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

	/**
	 * @var string
	 */
	public $img_url;

	/**
	 * @var string
	 */
	public $html;

	public function __construct(int $customer_id, int $bracket_id, string $name = null, string $html = null, string $img_url = null, int $id = null, array $rounds = []) {
		parent::__construct($name, $id, $rounds);
		$this->customer_id = $customer_id;
		$this->bracket_id = $bracket_id;
		$this->html = $html;
		$this->img_url = $img_url;
	}

	public static function from_array(array $data): Wp_Bracket_Builder_Bracket_Pick {
		$bracket_pick = new Wp_Bracket_Builder_Bracket_Pick($data['customer_id'], $data['bracket_id'], $data['name']);

		if (isset($data['id'])) {
			$bracket_pick->id = (int) $data['id'];
		}

		if (isset($data['html'])) {
			$bracket_pick->html = $data['html'];
		}

		if (isset($data['img_url'])) {
			$bracket_pick->img_url = $data['img_url'];
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

class Wp_Bracket_Builder_Match_Pick {
	public $match_id;
	public $pick_id;
	public $winner_id;

	public function __construct(int $match_id, int $pick_id, int $winner_id) {
		$this->match_id = $match_id;
		$this->pick_id = $pick_id;
		$this->winner_id = $winner_id;
	}
}
