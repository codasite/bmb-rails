<?php
require_once plugin_dir_path(dirname(__FILE__)) . '../vendor/autoload.php';

use Aws\Lambda\LambdaClient;

# Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
# SPDX-License-Identifier: Apache-2.0

#snippet-start:[php.example_code.lambda.service]


class LambdaService {
	protected LambdaClient $lambdaClient;

	public function __construct(
		$client = null,
		$region = 'us-east-1',
		$version = 'latest',
		$profile = 'default'
	) {
		if (gettype($client) == LambdaClient::class) {
			$this->lambdaClient = $client;
			return;
		}

		$this->lambdaClient = new LambdaClient([
			'region' => $region,
			'version' => $version,
			'profile' => $profile,
		]);
	}

	public function html_to_image($html) {
		// $functionName = 'html-to-image';
		// $params = [
		// 	'html' => $html,
		// ];
		// $result = $this->invoke($functionName, $params);
		// return $result['Payload']->getContents();
		$res = $this->image_from_api($html);
		return $res;
	}

	public function image_from_api($html) {
		$convert_url = 'http://localhost:8080/convert';
		// Make a request to the convert url using POST, content type application/json, and the html as the body, and accept *
		$body = array(
			'html' => $html,
		);
		// convert body to json


		$res = wp_remote_post($convert_url, array(
			'method' => 'POST', // 'GET' or 'POST
			'timeout' => 45,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept' => '*',
			),
			'body' => json_encode($body)
		));

		if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
			return new WP_Error('error', __('There was an error converting the html to an image', 'text-domain'), array('status' => 500));
		}

		// get the response body as json
		$res_body = json_decode(wp_remote_retrieve_body($res));
		return $res_body;
	}

	public function invoke($functionName, $params, $logType = 'None') {
		return $this->lambdaClient->invoke([
			'FunctionName' => $functionName,
			'Payload' => json_encode($params),
			'LogType' => $logType,
		]);
	}
}
#snippet-end:[php.example_code.lambda.service]