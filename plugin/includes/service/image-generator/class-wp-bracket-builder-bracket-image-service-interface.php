<?php
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'domain/class-wp-bracket-builder-bracket-interface.php';

class Wp_Bracket_Builder_Bracket_Image_Service {

	/**
	 * Generate and save a set of image previews for a bracket
	 * @param Wp_Bracket_Builder_Bracket_Interface $bracket The bracket to generate images for
	 * 
	 * @return array An array of data to send to the image generator
	 */
	public function generate_bracket_previews(Wp_Bracket_Builder_Bracket_Interface $bracket): void {
		$matches = $bracket->get_matches();
		$picks = $bracket->get_picks();
		$title = $bracket->get_title();
		$date = $bracket->get_date();
		$num_teams = $bracket->get_num_teams();

		$data = [
			'matches' => $matches,
			'picks' => $picks,
			'title' => $title,
			'date' => $date,
			'num_teams' => $num_teams,
		];
		return $data;
	}

	public function generate_image(Wp_Bracket_Builder_Bracket_Interface $bracket, array $args = []): string | WP_Error;
}
