<?php
require_once plugin_dir_path(dirname(__FILE__)) . '../vendor/autoload.php';

use Aws\Lambda\LambdaClient;

# Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
# SPDX-License-Identifier: Apache-2.0

#snippet-start:[php.example_code.lambda.service]


class LambdaServicex {
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

	public function invoke($functionName, $params, $logType = 'None') {
		return $this->lambdaClient->invoke([
			'FunctionName' => $functionName,
			'Payload' => json_encode($params),
			'LogType' => $logType,
		]);
	}
}
#snippet-end:[php.example_code.lambda.service]