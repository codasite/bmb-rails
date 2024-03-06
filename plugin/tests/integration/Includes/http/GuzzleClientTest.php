<?php
namespace WStrategies\BMB\tests\integration\Includes\http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use WStrategies\BMB\Includes\Service\Http\GuzzleClient;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class GuzzleClientTest extends WPBB_UnitTestCase {
  public function test_send_many() {
    $requests = [
      'top_light' => [
        'url' => 'http://localhost:8080/',
        'method' => 'POST',
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => '{"test": "test"}',
      ],
      'center_light' => [
        'url' => 'http://localhost:8080/',
        'method' => 'POST',
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => '{"test": "test"}',
      ],
    ];

    $mock = new MockHandler([
      new Response(
        200,
        ['X-Foo' => 'Bar'],
        json_encode([
          'image_url' =>
            'https://wpbb-bracket-images.s3.amazonaws.com/top_light',
        ])
      ),
      new Response(
        200,
        ['X-Foo' => 'Bar'],
        json_encode([
          'image_url' =>
            'https://wpbb-bracket-images.s3.amazonaws.com/center_light',
        ])
      ),
    ]);

    $handler = HandlerStack::create($mock);
    $container = [];
    $history = Middleware::history($container);

    $handler->push($history);

    $client = new Client(['handler' => $handler]);

    $http_service = new GuzzleClient(['client' => $client]);

    $responses = $http_service->send_many($requests);

    $this->assertEquals(
      [
        'image_url' => 'https://wpbb-bracket-images.s3.amazonaws.com/top_light',
      ],
      $responses['top_light']
    );

    $this->assertEquals(
      [
        'image_url' =>
          'https://wpbb-bracket-images.s3.amazonaws.com/center_light',
      ],
      $responses['center_light']
    );
  }
}
