<?php
// require_once plugin_dir_path(dirname(__FILE__,3)) . 'includes/service/class-wp-bracket-builder-score-service.php';
require_once plugin_dir_path(dirname(__FILE__,3)) . 'includes/controllers/class-wp-bracket-builder-bracket-template-api.php';
require_once plugin_dir_path(dirname(__FILE__,3)) . 'includes/controllers/class-wp-bracket-builder-bracket-tournament-api.php';
require_once plugin_dir_path(dirname(__FILE__,3)) . 'includes/controllers/class-wp-bracket-builder-bracket-play-api.php';

class Test_Wp_Bracket_Builder_Score_Service extends WP_UnitTestCase {

    public static function set_up_before_class() {
        // Set up DB tables
        require_once plugin_dir_path(dirname(__FILE__,3)) . 'includes/class-wp-bracket-builder-activator.php';

        $activator = new Wp_Bracket_Builder_Activator();
        $activator->activate();

        // Create bracket template
        require_once plugin_dir_path(dirname(__FILE__,3)) . 'tests/phpunit/data/templates.php';

        $template_api = new Wp_Bracket_Builder_Bracket_Template_Api();
        $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/templates');
        $request->set_query_params($template_14);
        $response = $template_api->create_item($request);
        $template = $response->get_data();

        // Create bracket tournament
        require_once plugin_dir_path(dirname(__FILE__,3)) . 'tests/phpunit/data/tournaments.php';

        $tournament_api = new Wp_Bracket_Builder_Bracket_Tournament_Api();
        $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/tournaments');
        $request->set_query_params($tournament_14);
        $response = $tournament_api->create_item($request);
        $tournament = $response->get_data();
        
        // // Create bracket play
        require_once plugin_dir_path(dirname(__FILE__,3)) . 'tests/phpunit/data/plays.php';

        $play_api = new Wp_Bracket_Builder_Bracket_Play_Api();
        $request = new WP_REST_Request('POST', '/wp-bracket-builder/v1/plays');
        $request->set_query_params($play_14);
        $response = $play_api->create_item($request);
    }


    function test_tournament_in_db() {
        // while (true) {};
        require_once plugin_dir_path(dirname(__FILE__,3)) . 'tests/phpunit/data/tournaments.php';

        $api = new Wp_Bracket_Builder_Bracket_Tournament_Api();
        $response = $api->get_items([]);
        $this->assertEquals(200, $response->get_status());
        $tournaments = $response->get_data();
        $this->assertNotEmpty($tournaments);
        $this->assertCount(1, $tournaments);
    }

    function test_play_for_tournament_in_db() {
        require_once plugin_dir_path(dirname(__FILE__,3)) . 'includes/controllers/class-wp-bracket-builder-bracket-play-api.php';

        $api = new Wp_Bracket_Builder_Bracket_Play_Api();
        $response = $api->get_items([]);
        $this->assertEquals(200, $response->get_status());

        $plays = $response->get_data();
        $this->assertNotEmpty($plays);
        $this->assertCount(1, $plays);

        $play = $plays[0];
        $this->assertEquals(5, $play->tournament_id);
    }
}