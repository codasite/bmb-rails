<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-domain.php';

class Wp_Bracket_Builder_Sport_Factory {
	public function create(array $data): Wp_Bracket_Builder_Sport {
		$sport = new Wp_Bracket_Builder_Sport($data['name']);

		if (isset($data['id'])) {
			$sport->id = $data['id'];
		}

		if (isset($data['teams'])) {
			$sport->teams = $data['teams'];
		}

		return $sport;
	}
}
