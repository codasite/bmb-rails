<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/controllers/class-wp-bracket-builder-bracket-play-api.php';

class Test_Wp_Bracket_Builder_Bracket_Play_Api extends WP_UnitTestCase {

    function test_get_plays() {
        $api = new Wp_Bracket_Builder_Bracket_Play_Api();
        $plays = $api->get_items([]);
        $this->assertNotEmpty($plays);
        $this->assertEquals(200, $plays->get_status());
    }
}

?>