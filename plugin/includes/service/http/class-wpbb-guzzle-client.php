<?php

require_once WPBB_PLUGIN_DIR .
  'includes/service/http/class-wpbb-http-client-interface.php';
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-utils.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class Wpbb_GuzzleClient implements Wpbb_HttpClientInterface {
  /**
   * @var Client
   */
  private $client;

  /**
   * @var Wpbb_Utils
   */
  private $utils;

  public function __construct($args = []) {
    $this->client = $args['client'] ?? new Client();
    $this->utils = new Wpbb_Utils();
  }

  /**
   *
   * Given an array of request data mapped to a key, make a request for each
   * and return an array of responses mapped to the same key
   *
   * @param string $url
   * @param array $requests
   * @example $requests = [
   * 	'light_top' => [
   * 		'url' => 'http://localhost:8080/',
   *  	'method' => 'POST',
   * 		'headers' => [
   * 			'Content-Type' => 'application/json',
   * 		],
   * 		'body' => json_encode($data),
   * 	],
   * 	'light_center' => [
   * 		'url' => 'http://localhost:8080/',
   * 		'method' => 'POST',
   * 		'headers' => [
   * 			'Content-Type' => 'application/json',
   * 		],
   * 		'body' => json_encode($data),
   * 	],
   * ]
   * @return array
   */
  public function send_many($requests = []): array {
    $keys = array_keys($requests);
    $get_requests = function () use ($keys, $requests) {
      //try
      for ($i = 0; $i < count($keys); $i++) {
        yield new Request(
          $requests[$keys[$i]]['method'],
          $requests[$keys[$i]]['url'],
          $requests[$keys[$i]]['headers'],
          $requests[$keys[$i]]['body']
        );
      }
    };

    $responses = [];

    $pool = new Pool($this->client, $get_requests(), [
      'concurrency' => 5,
      'fulfilled' => function (Response $response, $index) use (
        $keys,
        &$responses
      ) {
        // this is delivered each successful response
        $body = $response->getBody()->getContents();
        $responses[$keys[$index]] = json_decode($body, true);
      },
      'rejected' => function (RequestException $reason, $index) {
        // this is delivered each failed request
        $this->utils->log_error($reason->getMessage());
      },
    ]);

    // Initiate the transfers and create a promise
    $promise = $pool->promise();

    // Force the pool of requests to complete.
    $promise->wait();

    return $responses;
  }
}
