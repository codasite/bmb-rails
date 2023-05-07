<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-base.php';

class Wp_Bracket_Builder_Bracket_Pick extends Wp_Bracket_Builder_Bracket_Base {

	/**
	 * @var int
	 */
	public $customer_id;

	/**
	 * @var int
	 */
	public $bracket_id;



	public function __construct(int $customer_id, int $bracket_id, string $name = null, int $id = null, array $rounds = []) {
		// call parent constructor
		parent::__construct($name, $id, $rounds);
		$this->customer_id = $customer_id;
		$this->bracket_id = $bracket_id;
		// $this->rounds = $rounds;
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Pick {
		print_r($data);
		$bracket_pick = new Wp_Bracket_Builder_Bracket_Pick($data['customer_id'], $data['bracket_id'], $data['name']);
		echo 'bracket_pick:';
		print_r($bracket_pick);

		if (isset($data['id'])) {
			$bracket_pick->id = (int) $data['id'];
		}

		if (isset($data['rounds'])) {
			$bracket_pick->rounds = array_map(function ($index, $round) {
				$round['depth'] = $index;
				return Wp_Bracket_Builder_Round::from_array($round);
			}, array_keys($data['rounds']), $data['rounds']);
		}

		return $bracket_pick;
	}
}
