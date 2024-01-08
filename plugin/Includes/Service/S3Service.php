<?php
namespace WStrategies\BMB\Includes\Service;

use Aws\S3\S3Client;
use Aws\S3\S3UriParser;

# Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
# SPDX-License-Identifier: Apache-2.0

#snippet-start:[php.example_code.lambda.service]

class S3Service {
  protected S3Client $s3Client;

  public function __construct($region = 'us-east-1', $version = 'latest') {
    if (!defined('AWS_ACCESS_KEY_ID') || !defined('AWS_SECRET_ACCESS_KEY')) {
      return;
    }
    $this->s3Client = new S3Client([
      'region' => $region,
      'version' => $version,
      'credentials' => [
        'key' => AWS_ACCESS_KEY_ID,
        'secret' => AWS_SECRET_ACCESS_KEY,
      ],
    ]);
  }

  /**
   * Put an object in an Amazon S3 bucket.
   *
   * @param string $bucket bucket name
   * @param string $key object key
   * @param string $body object body
   *
   * @return string URI of the object
   */
  public function put(string $bucket, string $key, string $body): string {
    $result = $this->s3Client->putObject([
      'Bucket' => $bucket,
      'Key' => $key,
      'Body' => $body,
    ]);
    return $result->get('@metadata')['effectiveUri'];
  }

  /**
   * Get an object from an Amazon S3 bucket.
   *
   * @param string $bucket bucket name
   * @param string $key object key
   *
   * @return string object body
   */
  public function get(string $bucket, string $key): string {
    $result = $this->s3Client->getObject([
      'Bucket' => $bucket,
      'Key' => $key,
    ]);
    return $result['Body'];
  }

  /**
   * Get an object from an Amazon S3 bucket given the object's URL.
   *
   * @param string $url object url
   *
   * @return string object body
   */
  public function get_from_url(string $url): string {
    $parser = new S3UriParser();
    $parsed = $parser->parse($url);
    $result = $this->get($parsed['bucket'], $parsed['key']);
    return $result;
  }

  /**
   * Copy an object from a source S3 URL to a destination bucket and key.
   *
   * @param string $sourceUrl The S3 URL of the source object
   * @param string $destinationBucket The destination bucket
   * @param string $destinationKey The destination key
   *
   * @return string The URI of the copied object
   */
  public function copy_from_url(
    string $sourceUrl,
    string $destinationBucket,
    string $destinationKey
  ): string {
    // Parse the source URL to get the bucket and key
    $parser = new S3UriParser();
    $parsed = $parser->parse($sourceUrl);
    $sourceBucket = $parsed['bucket'];
    $sourceKey = $parsed['key'];

    // Copy the object
    $result = $this->s3Client->copyObject([
      'Bucket' => $destinationBucket,
      'Key' => $destinationKey,
      'CopySource' => "{$sourceBucket}/{$sourceKey}",
    ]);

    // Return the URI of the copied object
    return $result->get('@metadata')['effectiveUri'];
  }

  /**
   * Rename an object in an Amazon S3 bucket.
   *
   * @param string $url existing object url
   * @param string $newKey new object key
   *
   * @return string new object URL
   */
  public function rename_from_url(string $url, string $newKey): string {
    // Parse the bucket and key from the url
    $parser = new S3UriParser();
    $parsed = $parser->parse($url);
    $sourceBucket = $parsed['bucket'];
    $sourceKey = $parsed['key'];

    // Copy the object to the new key
    $result = $this->s3Client->copyObject([
      'Bucket' => $sourceBucket,
      'Key' => $newKey,
      'CopySource' => "{$sourceBucket}/{$sourceKey}",
    ]);

    // Delete the old object
    $this->s3Client->deleteObject([
      'Bucket' => $sourceBucket,
      'Key' => $sourceKey,
    ]);

    // Return the new object URL
    return $result->get('@metadata')['effectiveUri'];
  }
}
