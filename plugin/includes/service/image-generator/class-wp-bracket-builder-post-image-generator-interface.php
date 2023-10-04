<?php
interface Wp_Bracket_Builder_Post_Image_Generator_Interface {

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
	public function generate_image(Wp_Bracket_Builder_Bracket_Interface $bracket, array $args = []): string | WP_Error;
}
