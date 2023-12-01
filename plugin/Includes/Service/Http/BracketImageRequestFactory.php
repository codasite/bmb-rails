<?php
namespace WStrategies\BMB\Includes\Service\Http;

use WStrategies\BMB\Includes\Domain\PostBracketInterface;
use WStrategies\BMB\Includes\Service\ObjectStorage\ObjectStorageInterface;
use WStrategies\BMB\Includes\Service\ObjectStorage\S3Storage;

class BracketImageRequestFactory {
  /**
   * @var ObjectStorageInterface
   */
  private $object_storage;

  public function __construct($args = []) {
    $this->object_storage = $args['object_storage'] ?? new S3Storage();
  }

  public function get_request_data(
    PostBracketInterface $bracket,
    array $args = []
  ): array {
    list(
      $path,
      $method,
      $headers,
      $inch_height,
      $inch_width,
      $themes,
      $positions,
      $pdf,
    ) = $this->default_parameters($args);
    $bracket_id = $bracket->get_post_id();

    $base_data = $this->create_base_data($inch_height, $inch_width, $pdf);
    $base_query = $this->create_base_query($bracket, $inch_height, $inch_width);

    $request_data = [];
    foreach ($positions as $position) {
      foreach ($themes as $theme) {
        $key = "{$position}_{$theme}";
        $request_data[$key] = $this->create_request(
          $path,
          $method,
          $headers,
          $this->create_body(
            $bracket_id,
            $base_data,
            $base_query,
            $theme,
            $position
          )
        );
      }
    }

    return $request_data;
  }

  private function default_parameters(array $args): array {
    $path =
      $args['path'] ??
      (defined('IMAGE_GENERATOR_PATH')
        ? IMAGE_GENERATOR_PATH
        : 'http://localhost:3000/generate');
    $method = $args['method'] ?? 'POST';
    $headers = $args['headers'] ?? [
      'Content-Type' => 'application/json',
      'Accept' => '*',
    ];
    $pdf = $args['pdf'] ?? false;
    $inch_height = $args['inch_height'] ?? 16;
    $inch_width = $args['inch_width'] ?? 12;
    $positions = $args['positions'] ?? ['top', 'center'];
    $themes = $args['themes'] ?? ['light', 'dark'];
    return [
      $path,
      $method,
      $headers,
      $inch_height,
      $inch_width,
      $themes,
      $positions,
      $pdf,
    ];
  }

  private function create_base_data(
    int $inch_height,
    int $inch_width,
    bool $pdf
  ): array {
    $data = [
      'inchHeight' => $inch_height,
      'inchWidth' => $inch_width,
      'storageService' => $this->object_storage->get_service_name(),
    ];
    if ($pdf) {
      $data['pdf'] = true;
    }
    return $data;
  }

  private function create_base_query(
    PostBracketInterface $bracket,
    int $inch_height,
    int $inch_width
  ): array {
    return [
      'title' => $bracket->get_title(),
      'date' => $bracket->get_date(),
      'inch_height' => $inch_height,
      'inch_width' => $inch_width,
      'num_teams' => $bracket->get_num_teams(),
      'picks' => $bracket->get_picks(),
      'matches' => $bracket->get_matches(),
    ];
  }

  private function create_request($url, $method, $headers, $body): array {
    return [
      'url' => $url,
      'method' => $method,
      'headers' => $headers,
      'body' => $body,
    ];
  }

  public function create_body(
    $bracket_id,
    $base_data,
    $base_query,
    $theme,
    $position
  ) {
    $body = array_merge($base_data, [
      'storageOptions' => $this->object_storage->get_upload_options(
        $position . '-' . $theme . '-' . $bracket_id
      ),
      'queryParams' => array_merge($base_query, [
        'theme' => $theme,
        'position' => $position,
      ]),
    ]);
    return json_encode($body);
  }

  /**
   * Given a placement ('top' or 'center') returns an overlay map that can get passed direcly to the bracket preview page
   *
   * @var PostBracketInterface $bracket
   * @var string $placement - 'top' or 'center'
   *
   * @return array - an array of overlay maps
   *
   * @example
   * [
   * 'light' => 'someS3url',
   * 'dark' => 'someS3url'
   * ]
   */
  public function get_overlay_map(
    PostBracketInterface $bracket,
    string $placement
  ): array {
    return [
      'light' => 'someS3url',
      'dark' => 'someS3url',
    ];
  }
}
