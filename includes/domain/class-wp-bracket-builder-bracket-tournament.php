<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';

class Wp_Bracket_Builder_Bracket_Tournament extends Wp_Bracket_Builder_Post_Base {
	/**
	 * @var Wp_Bracket_Builder_Bracket_Template
	 */
	public $template;

	public function __construct(
		Wp_Bracket_Builder_Bracket_Template $template,
		int $id = null,
		string $title = '',
		int $author = null,
		string $status = 'draft',
		DateTimeImmutable|false $date = false,
		DateTimeImmutable|false $date_gmt = false,
	) {
		parent::__construct(
			$id,
			$title,
			$author,
			$status,
			$date,
			$date_gmt,
		);
		$this->template = $template;
	}
}
