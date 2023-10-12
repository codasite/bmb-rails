<?php
require_once 'class-wpbb-bracket-image-generator-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'object-storage/class-wpbb-object-storage-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'object-storage/class-wpbb-s3-storage.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) .
  'domain/class-wpbb-bracket-interface.php';

class Wpbb_LocalNodeGenerator implements Wpbb_BracketImageGeneratorInterface {
  /**
   * @param int|Wp_Post|null $post The post to generate an image for
   *
   * @param array $args An array of optional arguments that will be used to generate the image {
   * 		Array of arguments for generating and image
   *
   * 		@type string $theme The theme mode to use for the image. Default is 'light'
   *
   * 		@type string $position The position of the play in the tournament. Default is 'top'
   *
   * 		@type int $inch_height The height of the image in inches. Default is 16
   *
   * 		@type int $inch_width The width of the image in inches. Default is 12
   * }
   *
   * @return string|WP_Error The URL of the generated image or a WP_Error if there was an error
   */
  public function generate_image(
    Wpbb_BracketInterface $bracket,
    array $args = []
  ): string|WP_Error {
    $theme = $args['theme'] ?? 'light';
    $position = $args['position'] ?? 'top';
    $inch_height = $args['inch_height'] ?? 16;
    $inch_width = $args['inch_width'] ?? 12;
    $matches = $bracket->get_matches();
    $picks = $bracket->get_picks();
    $title = $bracket->get_title();
    $date = $bracket->get_date();
    $num_teams = $bracket->get_num_teams();

    $data = [
      'upload_service' => 's3',
      's3_bucket' => 'wpbb-bracket-images',
      's3_key' => 'test-image',
      'matches' => $matches,
      'picks' => $picks,
      'title' => $title,
      'date' => $date,
      'num_teams' => $num_teams,
      'theme' => $theme,
      'position' => $position,
      'inch_height' => $inch_height,
      'inch_width' => $inch_width,
    ];

    $generator_host = 'react-server';
    $generator_port = '8080';

    $res = wp_remote_post("http://$generator_host:$generator_port/test", [
      'method' => 'POST',
      'timeout' => 45,
      'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => '*',
      ],
      'body' => json_encode($data),
    ]);

    if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
      // return new WP_Error('error', __('There was an error generating the image', 'text-domain'), array('status' => 500));
      return $res;
    }

    // get the response body as json
    $res_body = json_decode(wp_remote_retrieve_body($res), true);
    return $res_body;
  }
}
