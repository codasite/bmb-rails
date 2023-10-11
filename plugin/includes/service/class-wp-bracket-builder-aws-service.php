<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wpbb-utils.php';
require_once plugin_dir_path(dirname(__FILE__)) . '../vendor/autoload.php';

use Aws\Lambda\LambdaClient;
use Aws\S3\S3Client;
use Aws\S3\S3UriParser;

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

	/**
	 * Put an object in an Amazon S3 bucket.
	 * @param string $bucket bucket name
	 * @param string $key object key
	 * @param string $body object body
	 * 
	 * @return string URI of the object
	 */
	public function put($bucket, $key, $body): string {
		$result = $this->s3Client->putObject([
			'Bucket' => $bucket,
			'Key' => $key,
			'Body' => $body,
		]);
		return $result->get('@metadata')['effectiveUri'];
	}

	/**
	 * Get an object from an Amazon S3 bucket.
	 * @param string $bucket bucket name
	 * @param string $key object key
	 * 
	 * @return string object body
	 */
	public function get($bucket, $key): string {
		$result = $this->s3Client->getObject([
			'Bucket' => $bucket,
			'Key' => $key,
		]);
		return $result['Body'];
	}

	/**
	 * Get an object from an Amazon S3 bucket given the object's URL.
	 * @param string $url object url
	 * 
	 * @return string object body
	 */
	public function get_from_url($url) {
		$parser = new S3UriParser();
		$parsed = $parser->parse($url);
		$result = $this->get($parsed['bucket'], $parsed['key']);
		return $result;
	}

	/**
	 * Copy an object from a source S3 URL to a destination bucket and key.
	 * @param string $sourceUrl The S3 URL of the source object
	 * @param string $destinationBucket The destination bucket
	 * @param string $destinationKey The destination key
	 * 
	 * @return string The URI of the copied object
	 */
	public function copy_from_url($sourceUrl, $destinationBucket, $destinationKey): string {
		// Parse the source URL to get the bucket and key
		$parser = new S3UriParser();
		$parsed = $parser->parse($sourceUrl);
		$sourceBucket = $parsed['bucket'];
		$sourceKey = $parsed['key'];

		// Copy the object
		$result = $this->s3Client->copyObject([
			'Bucket'     => $destinationBucket,
			'Key'        => $destinationKey,
			'CopySource' => "{$sourceBucket}/{$sourceKey}",
		]);

		// Return the URI of the copied object
		return $result->get('@metadata')['effectiveUri'];
	}

	/**
	 * Rename an object in an Amazon S3 bucket.
	 * @param string $url existing object url
	 * @param string $newBucket new bucket name
	 * @param string $newKey new object key
	 * 
	 * @return string new object URL
	 */
	public function rename_from_url($url, $newKey): string {
		// Parse the bucket and key from the url
		$parser = new S3UriParser();
		$parsed = $parser->parse($url);
		$sourceBucket = $parsed['bucket'];
		$sourceKey = $parsed['key'];

		// Copy the object to the new key
		$result = $this->s3Client->copyObject([
			'Bucket'     => $sourceBucket,
			'Key'        => $newKey,
			'CopySource' => "{$sourceBucket}/{$sourceKey}",
		]);

		// Delete the old object
		$this->s3Client->deleteObject([
			'Bucket' => $sourceBucket,
			'Key'    => $sourceKey,
		]);

		// Return the new object URL
		return $result->get('@metadata')['effectiveUri'];
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

	/**
	 * Invoke the BMB Lambda function.
	 * @param array $body parameters to pass to the function
	 * 
	 * @return array response from the function
	 */
	public function html_to_image($body): array | WP_Error {
		// $res = $this->image_from_api($body);
		$res = $this->image_from_invocation($body);
		return $res;
	}

	/**
	 * Invoke the BMB Lambda function using the API Gateway. Only used for local testing.
	 * @param array $body parameters to pass to the function
	 * 
	 * @return array response from the function
	 */
	private function image_from_api($body): array | WP_Error {
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
		$res_body = json_decode(wp_remote_retrieve_body($res), true);
		return $res_body;
	}

	/**
	 * Invoke the BMB Lambda function using the Lambda client. Used in production.
	 * @param array $params parameters to pass to the function
	 * 
	 * @return array response from the function
	 */
	private function image_from_invocation($params): array | WP_Error {
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
			$extension = (isset($params['pdf']) && $params['pdf'] === true) ? '.pdf' : '.png';
			$params['s3Key'] = 'bracket-' . uniqid() . $extension;
		}

		$functionName = HTML_TO_IMAGE_FUNCTION_NAME;

		$result = $this->invoke($functionName, $params);

		if (is_wp_error($result)) {
			return $result;
		}

		return json_decode($result['Payload']->getContents(), true);
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
