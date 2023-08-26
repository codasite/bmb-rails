<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-base.php';

class Wp_Bracket_Builder_Bracket_Template extends Wp_Bracket_Builder_Bracket_Base {


	public function __construct(
		int $id = null,
		string $title,
		int $author,
		string $status = 'publish',
		int $num_teams,
		int $wildcard_placement = null,
		DateTime $date = null,
		DateTime $date_gmt = null,
		array $rounds = []
	) {
		parent::__construct($title, $id, $rounds);
		$this->status = $status;
		$this->num_teams = $num_teams;
		$this->wildcard_placement = $wildcard_placement;
		$this->date = $date;
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Template {
		$bracket = new Wp_Bracket_Builder_Bracket_Template(
			$data['title'],
			$data['num_rounds'],
			$data['num_wildcards'],
			$data['wildcard_placement'],
			$data['active'],
		);

		if (isset($data['id'])) {
			$bracket->id = (int) $data['id'];
		}

		if (isset($data['date'])) {
			$bracket->date = new DateTime($data['date']);
		}

		if (isset($data['rounds'])) {
			$bracket->rounds = array_map(function ($index, $round) {
				$round['depth'] = $index;
				return Wp_Bracket_Builder_Round::from_array($round);
			}, array_keys($data['rounds']), $data['rounds']);
		}

		if (isset($data['id'])) {
			$bracket->id = (int) $data['id'];
		}

		return $bracket;
	}
}
