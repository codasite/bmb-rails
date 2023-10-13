<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket-config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wpbb-utils.php';

// // a constant for the session key
// define('WPBB_BRACKET_CONFIG_SESSION_KEY', 'wpbb_bracket_config');

// class Wpbb_BracketConfigRepo {

// 	public function add(Wpbb_BracketConfig $config, string $theme_mode = '', string $bracket_placement = ''): Wpbb_BracketConfig {
// 		$utils = new Wpbb_Utils();
// 		// get the current configs
// 		$configs = $this->get_all();
// 		// add the new config
// 		$configs[$theme_mode][$bracket_placement] = $config;
// 		// set the session value
// 		$session_key = WPBB_BRACKET_CONFIG_SESSION_KEY;
// 		$utils->set_session_value($session_key, $configs);
// 		return $configs[$theme_mode][$bracket_placement];
// 	}

// 	public function get_all(): array {
// 		$utils = new Wpbb_Utils();
// 		$session_key = WPBB_BRACKET_CONFIG_SESSION_KEY;
// 		$configs = $utils->get_session_value($session_key);
// 		if ($configs) {
// 			return $configs;
// 		}
// 		return [];
// 	}

// 	public function is_empty(): bool {
// 		$configs = $this->get_all();
// 		return empty($configs);
// 	}

// 	public function get(string $theme_mode = '', $bracket_placement = ''): Wpbb_BracketConfig | null {
// 		if (empty($theme_mode) || empty($bracket_placement)) {
// 			return null;
// 		}
// 		$utils = new Wpbb_Utils();
// 		$configs = $this->get_all();
// 		if (isset($configs[$theme_mode])) {
// 			if (isset($configs[$theme_mode][$bracket_placement])) {
// 				return $configs[$theme_mode][$bracket_placement];
// 			}
// 			return $configs[$theme_mode];
// 		}
// 		return null;
// 	}
// }

define('WPBB_BRACKET_CONFIG_SESSION_KEY', 'wpbb_bracket_config');
/**
 * @deprecated
 */
class Wpbb_BracketConfigRepo {
  private $utils;

  public function __construct() {
    $this->utils = new Wpbb_Utils();
  }

  public function add(Wpbb_BracketConfig $config): Wpbb_BracketConfig {
    $configs = $this->get_all();
    $theme_mode = $config->theme_mode;
    $bracket_placement = $config->bracket_placement;
    $configs[$theme_mode][$bracket_placement] = $config;
    $session_key = WPBB_BRACKET_CONFIG_SESSION_KEY;
    $this->utils->set_session_value($session_key, $configs);
    return $configs[$theme_mode][$bracket_placement];
  }

  public function get_all(): array {
    $session_key = WPBB_BRACKET_CONFIG_SESSION_KEY;
    $configs = $this->utils->get_session_value($session_key);
    return $configs ? $configs : [];
  }

  public function is_empty(): bool {
    return empty($this->get_all());
  }

  public function get(
    string $theme_mode,
    string $bracket_placement
  ): ?Wpbb_BracketConfig {
    $configs = $this->get_all();
    return $configs[$theme_mode][$bracket_placement] ?? null;
  }
}
