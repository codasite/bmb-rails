<?php
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'domain/class-wp-bracket-builder-bracket-interface.php';

class Wp_Bracket_Builder_Bracket_Image_Convert_Request {

	/**
	 * @param Wp_Bracket_Builder_Bracket_Interface $bracket The bracket to generate an image for
	 * 
	 * @return array An array of data to send to the image generator
	 */
	public function get_data(Wp_Bracket_Builder_Bracket_Interface $bracket): array {
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
}
