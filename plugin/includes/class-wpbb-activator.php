<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://github.com/barrymolina
 * @since      1.0.0
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 * @author     Barry Molina <barry@wstrategies.co>
 */
class Wpbb_Activator {
  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    1.0.0
   */
  public static function activate() {
    global $wpdb;
    $wp_prefix = $wpdb->prefix;
    $plugin_prefix = 'bracket_builder_';
    $prefix = $wp_prefix . $plugin_prefix;

    self::delete_tables($prefix);

    self::create_brackets_table($prefix);
    self::create_plays_table($prefix); // one-to-one table for bracket plays
    self::create_teams_table($prefix); // associated with matches
    self::create_matches_table($prefix); // associated with bracket templates
    self::create_match_picks_table($prefix); // associated with bracket plays
    self::create_bracket_results_table($prefix); // associated with bracket tournaments
  }

  private static function delete_tables(string $prefix) {
    global $wpdb;
    $tables = [
      $prefix . 'bracket_results',
      $prefix . 'tournament_results',
      $prefix . 'match_picks',
      $prefix . 'matches',
      $prefix . 'teams',
      $prefix . 'plays',
      $prefix . 'tournaments',
      $prefix . 'templates',
      $prefix . 'brackets',
    ];

    foreach ($tables as $table) {
      $wpdb->query("DROP TABLE IF EXISTS $table");
    }
  }
  private static function create_brackets_table(string $prefix) {
    /**
     * Create the bracket brackets table
     */

    global $wpdb;
    $table_name = $prefix . 'brackets';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY (post_id),
			FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  private static function create_plays_table(string $prefix) {
    /**
     * Create the play meta table
     */

    global $wpdb;
    $table_name = $prefix . 'plays';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id bigint(20) UNSIGNED NOT NULL,
			total_score int(11),
			accuracy_score float,
			bracket_post_id bigint(20) UNSIGNED,
      bracket_id bigint(20) UNSIGNED,
			busted_play_post_id bigint(20) UNSIGNED,
			busted_play_id bigint(20) UNSIGNED,
      is_printed tinyint(1) NOT NULL DEFAULT 0,
      -- printed = tinyint(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			UNIQUE KEY (post_id),
			FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
			FOREIGN KEY (bracket_post_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id) ON DELETE CASCADE,
			FOREIGN KEY (busted_play_post_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE SET NULL,
			FOREIGN KEY (busted_play_id) REFERENCES {$prefix}plays(id) ON DELETE SET NULL

		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  private static function create_matches_table(string $prefix) {
    /**
     * Create the matches table
     */

    global $wpdb;
    $table_name = $prefix . 'matches';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	 		bracket_id bigint(20) UNSIGNED NOT NULL,
			round_index tinyint(4) NOT NULL,
			match_index tinyint(4) NOT NULL,
			team1_id bigint(20) UNSIGNED,
			team2_id bigint(20) UNSIGNED,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id) ON DELETE CASCADE,
			FOREIGN KEY (team1_id) REFERENCES {$prefix}teams(id) ON DELETE SET NULL,
			FOREIGN KEY (team2_id) REFERENCES {$prefix}teams(id) ON DELETE SET NULL
		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  private static function create_teams_table(string $prefix) {
    /**
     * Create the teams table
     */

    global $wpdb;
    $table_name = $prefix . 'teams';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			bracket_id bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id) ON DELETE CASCADE
		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  private static function create_match_picks_table(string $prefix) {
    /**
     * Create the match picks table. Rows in this table represent a user's pick for a match.
     * Holds a pointer to the bracket play this pick belongs to.
     */

    global $wpdb;
    $table_name = $prefix . 'match_picks';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			bracket_play_id bigint(20) UNSIGNED NOT NULL,
			round_index tinyint(4) NOT NULL,
			match_index tinyint(4) NOT NULL,
			winning_team_id bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_play_id) REFERENCES {$prefix}plays(id) ON DELETE CASCADE,
			FOREIGN KEY (winning_team_id) REFERENCES {$prefix}teams(id) ON DELETE CASCADE
		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  private static function create_bracket_results_table(string $prefix) {
    /**
     * Create the match picks table. Rows in this table represent a user's pick for a match.
     * Holds a pointer to the bracket play this pick belongs to.
     */

    global $wpdb;
    $table_name = $prefix . 'bracket_results';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			bracket_id bigint(20) UNSIGNED NOT NULL,
			round_index tinyint(4) NOT NULL,
			match_index tinyint(4) NOT NULL,
			winning_team_id bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id) ON DELETE CASCADE,
			FOREIGN KEY (winning_team_id) REFERENCES {$prefix}teams(id) ON DELETE CASCADE
		) $charset_collate;";

    // import dbDelta
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }
}
