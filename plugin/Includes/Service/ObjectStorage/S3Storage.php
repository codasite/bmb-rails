<?php
namespace WStrategies\BMB\Includes\Service\ObjectStorage;

use WP;

class S3Storage implements ObjectStorageInterface {
  /**
   * Upload a file to the object storage.
   *
   * @param string $file The path to the file to upload.
   * @param string $name The name to give the file in the object storage.
   * @param array $args Optional. Additional arguments to pass to the object storage.
   *
   * @return string The URL to the file in the object storage.
   */
  public function upload(string $file, string $name, array $args = []): string {
    return 'hello';
  }

  public function get_upload_options($file_name): array {
    if (!defined('WPBB_S3_IMAGE_BUCKET')) {
      throw new \Exception('S3 bucket not defined');
    }
    return [
      'bucket' => WPBB_S3_IMAGE_BUCKET,
      'key' => $file_name,
    ];
  }

  public function get_service_name(): string {
    return 's3';
  }
}
