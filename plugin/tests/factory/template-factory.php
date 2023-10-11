<?php
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-template.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-template-repo.php';

/**
 * Class WPBB_UnitTest_Factory_For_Template
 *
 * This class is used to create template objects for unit testing
 */
class WPBB_UnitTest_Factory_For_Template extends WP_UnitTest_Factory_For_Thing
{

	private $template_repo;

	function __construct($factory = null) {
		parent::__construct($factory);
		$this->template_repo = new Wpbb_BracketTemplateRepo();

		$this->default_generation_definitions = [
			'title' => new WP_UnitTest_Generator_Sequence('Template %s'),
			'author' => 1,
		];
	}

	function create_object($args) {
		$template = new Wp_Bracket_Builder_Bracket_Template($args);
		$template = $this->template_repo->add($template);
		return $template;
	}

	function update_object($template_id, $fields) {
		$template = $this->template_repo->update($template_id, $fields);
		return $template;
	}

	function get_object_by_id($template_id) {
		return $this->template_repo->get($template_id);
	}
}
