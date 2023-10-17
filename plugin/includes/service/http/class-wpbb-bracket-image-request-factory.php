<?php
require_once WPBB_PLUGIN_DIR .
  'includes/service/object-storage/class-wpbb-object-storage-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/object-storage/class-wpbb-s3-storage.php';

class Wpbb_BracketImageRequestFactory {
  /**
   * @var Wpbb_ObjectStorageInterface
   */
  private $object_storage;

  public function __construct($args = []) {
    $this->object_storage = $args['object_storage'] ?? new Wpbb_S3Storage();
  }

  public function get_request_data(
    Wpbb_PostBracketInterface $bracket,
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
    ) = $this->default_parameters($args);
    $bracket_id = $bracket->get_post_id();

    $base_data = $this->create_base_data($inch_height, $inch_width);
    $base_query = $this->create_base_query($bracket, $inch_height, $inch_width);

    $request_data = [];
    foreach ($themes as $theme) {
      foreach ($positions as $position) {
        $key = "{$theme}_{$position}";
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
    $path = $args['path'] ?? 'http://react-server:8080/test';
    $method = $args['method'] ?? 'POST';
    $headers = $args['headers'] ?? [
      'Content-Type' => 'application/json',
      'Accept' => '*',
    ];
    $inch_height = $args['inch_height'] ?? 16;
    $inch_width = $args['inch_width'] ?? 11;
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
    ];
  }

  private function create_base_data(int $inch_height, int $inch_width): array {
    return [
      'inchHeight' => $inch_height,
      'inchWidth' => $inch_width,
      'storageService' => $this->object_storage->get_service_name(),
    ];
  }

  private function create_base_query(
    Wpbb_PostBracketInterface $bracket,
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

  private function create_body(
    $bracket_id,
    $base_data,
    $base_query,
    $theme,
    $position
  ) {
    $body = array_merge($base_data, [
      'storageOptions' => $this->object_storage->get_upload_options(
        $theme . '-' . $position . '-' . $bracket_id
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
   * @var Wpbb_PostBracketInterface $bracket
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
    Wpbb_PostBracketInterface $bracket,
    string $placement
  ): array {
    return [
      'light' => 'someS3url',
      'dark' => 'someS3url',
    ];
  }
}
