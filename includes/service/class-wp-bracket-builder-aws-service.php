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

		if (!defined('AWS_ACCESS_KEY') || !defined('AWS_SECRET_KEY')) {
			return;
		}

		$this->lambdaClient = new LambdaClient([
			'region' => $region,
			'version' => $version,
			'profile' => $profile,
			'credentials' => [
				'key' => AWS_ACCESS_KEY,
				'secret' => AWS_SECRET_KEY,
			],
		]);
	}

	public function html_to_image($body) {
		// $res = $this->image_from_api($body);
		$res = $this->image_from_invocation($body);
		return $res;
	}

	public function image_from_api($body) {
		$convert_url = 'http://localhost:8080/convert';
		// Make a request to the convert url using POST, content type application/json, and the html as the body, and accept *

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

	private function image_from_invocation($params) {
		if (!defined('HTML_TO_IMAGE_FUNCTION_NAME')) {
			return new WP_Error('error', __('Lambda function name not defined.', 'text-domain'), array('status' => 500));
		}
		$functionName = HTML_TO_IMAGE_FUNCTION_NAME;

		$result = $this->invoke($functionName, $params);

		if (is_wp_error($result)) {
			return $result;
		}

		return json_decode($result['Payload']->getContents());
	}

	private function invoke($functionName, $params, $logType = 'None') {
		// check if lambda client is initialized
		if (!isset($this->lambdaClient)) {
			return new WP_Error('error', __('Lambda client is not initialized. Did you forget to add AWS credentials?', 'text-domain'), array('status' => 500));
		}

		return $this->lambdaClient->invoke([
			'FunctionName' => $functionName,
			'Payload' => json_encode($params),
			'LogType' => $logType,
		]);
	}
}
#snippet-end:[php.example_code.lambda.service]