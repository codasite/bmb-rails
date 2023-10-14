<?php
require_once 'class-wpbb-object-storage-interface.php';
class Wpbb_S3Storage implements Wpbb_Object_Storage_Interface {
  /**
   * Upload a file to the object storage.
   * @param  string $file The path to the file to upload.
   * @param  string $name The name to give the file in the object storage.
   * @param  array  $args Optional. Additional arguments to pass to the object storage.
   *
   * @return string The URL to the file in the object storage.
   */
  public function upload($file, $name, $args = []): string {
    return 'hello';
  }

  public function get_upload_options($file_name): array {
    return [
      'bucket' => 'wpbb-bracket-images',
      'key' => $file_name,
    ];
  }

  public function get_service_name(): string {
    return 's3';
  }
}
