<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-custom-post-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';

class Wp_Bracket_Builder_Bracket_Tournament extends Wp_Bracket_Builder_Post_Base {
	/**
	 * @var ?int
	 */
	public $bracket_template_id;

	/**
	 * @var ?Wp_Bracket_Builder_Bracket_Template
	 */
	public $bracket_template;

	public function __construct(
		int $bracket_template_id,
		int $id = null,
		string $title = '',
		int $author = null,
		string $status = 'publish',
		DateTimeImmutable|false $date = false,
		DateTimeImmutable|false $date_gmt = false,
		Wp_Bracket_Builder_Bracket_Template $bracket_template = null
	) {
		parent::__construct(
			$id,
			$title,
			$author,
			$status,
			$date,
			$date_gmt,
		);
		$this->bracket_template_id = $bracket_template_id;
		$this->bracket_template = $bracket_template;
	}

	static public function get_post_type(): string {
		return 'bracket_tournament';
	}

	public function get_post_meta(): array {
		return [
			'bracket_template_id' => $this->bracket_template_id,
		];
	}

	static public function from_array($data) {

		if (!isset($data['bracket_template_id'])) {
			throw new Exception('bracket_template_id is required');
		}

		$tournament = new Wp_Bracket_Builder_Bracket_Tournament($data['bracket_template_id']);

		foreach ($data as $key => $value) {
			if (property_exists($tournament, $key)) {
				$tournament->$key = $value;
			}
		}

		return $tournament;
	}
}
