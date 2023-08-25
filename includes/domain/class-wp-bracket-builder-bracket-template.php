<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-base.php';

class Wp_Bracket_Builder_Bracket_Template extends Wp_Bracket_Builder_Bracket_Base {
	/**
	 * @var int
	 * 
	 */
	public $cpt_id;

	/**
	 * @var bool
	 */
	public $active;

	/**
	 * @var int
	 */
	public $num_rounds;

	/**
	 * @var int
	 */
	public $num_wildcards;

	/**
	 * @var int
	 */
	public $wildcard_placement;

	/**
	 * @var DateTime
	 */
	public $created_at;

	/**
	 * @var int
	 */
	public $num_submissions;

	public function __construct(
		string $name,
		int $num_rounds,
		int $num_wildcards,
		int $wildcard_placement = null,
		bool $active = false,
		int $id = null,
		DateTime $created_at = null,
		array $rounds = []
	) {
		parent::__construct($name, $id, $rounds);
		$this->active = $active;
		$this->num_rounds = $num_rounds;
		$this->num_wildcards = $num_wildcards;
		$this->wildcard_placement = $wildcard_placement;
		$this->created_at = $created_at;
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Template {
		$bracket = new Wp_Bracket_Builder_Bracket_Template(
			$data['name'],
			$data['num_rounds'],
			$data['num_wildcards'],
			$data['wildcard_placement'],
			$data['active'],
		);

		if (isset($data['id'])) {
			$bracket->id = (int) $data['id'];
		}

		if (isset($data['created_at'])) {
			$bracket->created_at = new DateTime($data['created_at']);
		}

		if (isset($data['num_submissions'])) {
			$bracket->num_submissions = (int) $data['num_submissions'];
		}


		if (isset($data['rounds'])) {
			$bracket->rounds = array_map(function ($index, $round) {
				$round['depth'] = $index;
				return Wp_Bracket_Builder_Round::from_array($round);
			}, array_keys($data['rounds']), $data['rounds']);
		}

		if (isset($data['cpt_id'])) {
			$bracket->cpt_id = (int) $data['cpt_id'];
		}

		return $bracket;
	}
}
