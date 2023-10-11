<?php
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-play.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-template.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wp-bracket-builder-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-template-repo.php';

/**
 * Class WPBB_UnitTest_Factory_For_Template
 *
 * This class is used to create template objects for unit testing
 */
class WPBB_UnitTest_Factory_For_Tournament extends WP_UnitTest_Factory_For_Thing
{

	private $tournament_repo;

	function __construct($factory = null) {
		parent::__construct($factory);
		$this->tournament_repo = new Wpbb_BracketTournamentRepo();

		$this->default_generation_definitions = [
			'title' => new WP_UnitTest_Generator_Sequence('Tournament %s'),
			'author' => 1,
		];
	}

	function create_object($args) {
		$tournament = new Wp_Bracket_Builder_Bracket_Tournament($args);
		$tournament = $this->tournament_repo->add($tournament);
		return $tournament;
	}

	function update_object($tournament_id, $fields) {
		$tournament = $this->tournament_repo->update($tournament_id, $fields);
		return $tournament;
	}

	function get_object_by_id($tournament_id) {
		return $this->tournament_repo->get($tournament_id);
	}
}
