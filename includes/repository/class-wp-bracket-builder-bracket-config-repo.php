<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';

// a constant for the session key
define('WPBB_BRACKET_CONFIG_SESSION_KEY', 'wpbb_bracket_config');

class Wp_Bracket_Builder_Bracket_Config_Repository {

	public function add(Wp_Bracket_Builder_Bracket_Config $config, string $theme_mode = ''): Wp_Bracket_Builder_Bracket_Config {
		$utils = new Wp_Bracket_Builder_Utils();
		// get the current configs
		$configs = $this->get_all();
		// add the new config
		$configs[$theme_mode] = $config;
		// set the session value
		$session_key = WPBB_BRACKET_CONFIG_SESSION_KEY;
		$utils->set_session_value($session_key, $configs);
		return $configs[$theme_mode];
	}

	public function get_all(): array {
		$utils = new Wp_Bracket_Builder_Utils();
		$session_key = WPBB_BRACKET_CONFIG_SESSION_KEY;
		$configs = $utils->get_session_value($session_key);
		if ($configs) {
			return $configs;
		}
		return [];
	}

	public function is_empty(): bool {
		$configs = $this->get_all();
		return empty($configs);
	}

	public function get(string $theme_mode = ''): Wp_Bracket_Builder_Bracket_Config | null {
		if (empty($theme_mode)) {
			return null;
		}
		$utils = new Wp_Bracket_Builder_Utils();
		$configs = $this->get_all();
		if (isset($configs[$theme_mode])) {
			return $configs[$theme_mode];
		}
		return null;
	}
}
