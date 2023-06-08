<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';


class Wp_Bracket_Builder_Bracket_Config_Repository {

	public function add(Wp_Bracket_Builder_Bracket_Config $config, string $theme_mode = ''): Wp_Bracket_Builder_Bracket_Config {
		$utils = new Wp_Bracket_Builder_Utils();
		$session_key = $this->get_session_key($theme_mode);
		$utils->set_session_value($session_key, $config);
		return $config;
	}

	public function get(string $theme_mode = ''): Wp_Bracket_Builder_Bracket_Config | null {
		if (empty($theme_mode)) {
			return null;
		}
		$utils = new Wp_Bracket_Builder_Utils();
		$session_key = $this->get_session_key($theme_mode);
		$config = $utils->get_session_value($session_key);
		if ($config) {
			return $config;
		}
		return null;
	}

	private function get_session_key(string $theme_mode = ''): string {
		return $theme_mode ? 'wpbb_bracket_config_' . $theme_mode : 'wpbb_bracket_config';
	}
}
