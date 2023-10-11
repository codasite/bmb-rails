<?php
require_once 'class-wpbb-post-image-generator-interface.php';
require_once plugin_dir_path(dirname(__FILE__), 2) . 'object-storage/class-wpbb-object-storage-interface.php';
require_once plugin_dir_path(dirname(__FILE__), 2) . 'object-storage/class-wpbb-s3-storage.php';

class Wpbb_Local_Node_Generator implements Wpbb_PostImageGeneratorInterface
{

	/**
	 * @param Wp_Bracket_Builder_Object_Storage_Interface
	 */
	private $storage_service;

	public function __construct() {
		$this->storage_service = new Wp_Bracket_Builder_S3_Storage();
	}

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
	public function generate_image(int|Wp_Post|null $play, array $args = []): string {
		$img_url = $this->storage_service->upload('hi', 'hi');
		return $img_url;
	}
}
