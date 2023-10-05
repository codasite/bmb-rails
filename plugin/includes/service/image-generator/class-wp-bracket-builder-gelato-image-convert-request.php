<?php
require_once 'class-wp-bracket-builder-bracket-image-convert-request.php';

class Wp_Bracket_Builder_Gelato_Image_Convert_Request extends Wp_Bracket_Builder_Bracket_Image_Convert_Request {

	public function get_data(Wp_Bracket_Builder_Bracket_Interface $bracket): array {
		$bracket_data = parent::get_data($bracket);

		// For Gelato, we need to generate 4 png images, one for each theme and position combination


	}
}
