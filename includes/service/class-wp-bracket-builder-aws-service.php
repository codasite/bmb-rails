<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';
require_once plugin_dir_path(dirname(__FILE__)) . '../vendor/autoload.php';

use Aws\Lambda\LambdaClient;
use Aws\S3\S3Client;

# Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
# SPDX-License-Identifier: Apache-2.0

#snippet-start:[php.example_code.lambda.service]

class Wp_Bracket_Builder_S3_Service {
	protected S3Client $s3Client;

	public function __construct(
		$region = 'us-east-1',
		$version = 'latest',
	) {
		if (!defined('AWS_ACCESS_KEY') || !defined('AWS_SECRET_KEY')) {
			return;
		}
		$this->s3Client = new S3Client([
			'region' => $region,
			'version' => $version,
			'credentials' => [
				'key' => AWS_ACCESS_KEY,
				'secret' => AWS_SECRET_KEY,
			],
		]);
	}
	public function put($bucket, $key, $body) {
		$result = $this->s3Client->putObject([
			'Bucket' => $bucket,
			'Key' => $key,
			'Body' => $body,
		]);
		return $result;
	}

	public function copy($destination_bucket, $destination_key, $source_bucket, $source_key) {
		try {
			$result = $this->s3Client->copyObject([
				'Bucket' => $destination_bucket,
				'Key' => $destination_key,
				'CopySource' => $source_bucket . '/' . $source_key,
			]);
			return $result;
		} catch (Exception $e) {
			$utils = new Wp_Bracket_Builder_Utils();
			$utils->log_sentry_error($e);
		}
	}

	public function get($bucket, $key) {
		$result = $this->s3Client->getObject([
			'Bucket' => $bucket,
			'Key' => $key,
		]);
		return $result;
	}

	public function extract_key_from_url($url) {
		$parsed = parse_url($url);
		$path = $parsed['path'];
		$parts = explode('/', $path);
		$key = $parts[count($parts) - 1];
		return $key;
	}
}

class Wp_Bracket_Builder_Lambda_Service {
	protected LambdaClient $lambdaClient;

	public function __construct(
		$client = null,
		$region = 'us-east-1',
		$version = 'latest',
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

		// Attempt to get the bucket name from params or wp config
		if (!isset($params['s3Bucket'])) {
			if (!defined('BRACKET_BUILDER_S3_IMAGE_BUCKET')) {
				return new WP_Error('error', __('S3 image bucket name not found.', 'text-domain'), array('status' => 500));
			}
			$params['s3Bucket'] = BRACKET_BUILDER_S3_IMAGE_BUCKET;
		}

		// Attempt to get the key name from params or wp config
		if (!isset($params['s3Key'])) {
			$params['s3Key'] = 'bracket-' . uniqid() . '.png';
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




	/**
	 * Invoke multiple AWS Lambda functions asynchronously.
	 *
	 * @param array $functions An array of associative arrays, each containing 'name' and 'args' keys. 
	 * 'name' is the name of the function to invoke, and 'args' are the arguments to pass to the function.
	 * Example:
	 * [
	 *     ['name' => 'MyFunction1', 'args' => '...'],
	 *     ['name' => 'MyFunction2', 'args' => '...'],
	 *     // ...
	 * ]
	 *
	 * @return array An array of Aws\Result objects, each representing the result of invoking one of the functions. 
	 * The order of the results corresponds to the order of the functions in the input array.
	 *
	 * @throws \GuzzleHttp\Promise\RejectionException If any of the promises are rejected.
	 */
	private function invoke_all(array $functions) {
		if (!isset($this->lambdaClient)) {
			return new WP_Error('error', __('Lambda client is not initialized. Did you forget to add AWS credentials?', 'text-domain'), array('status' => 500));
		}

		$promises = [];

		foreach ($functions as $function) {
			$promises[] = $this->lambdaClient->invokeAsync([
				'FunctionName' => $function['name'],
				'InvokeArgs'   => $function['args']
			]);
		}

		$results = \GuzzleHttp\Promise\Utils::unwrap($promises);

		return $results;
	}
}
