<?php
namespace WStrategies\BMB\Includes\Repository;

// // a constant for the session key
// define('WPBB_BRACKET_CONFIG_SESSION_KEY', 'wpbb_bracket_config');

// class BracketConfigRepo {

// 	public function add(BracketConfig $config, string $theme_mode = '', string $bracket_placement = ''): BracketConfig {
// 		$utils = new Utils();
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
// 		$utils = new Utils();
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

// 	public function get(string $theme_mode = '', $bracket_placement = ''): BracketConfig | null {
// 		if (empty($theme_mode) || empty($bracket_placement)) {
// 			return null;
// 		}
// 		$utils = new Utils();
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

use WStrategies\BMB\Includes\Domain\BracketConfig;
use WStrategies\BMB\Includes\Utils;

define('WPBB_BRACKET_CONFIG_SESSION_KEY', 'wpbb_bracket_config');
/**
 * @deprecated
 */
class BracketConfigRepo {
  private $utils;

  public function __construct() {
    $this->utils = new Utils();
  }

  public function add(BracketConfig $config): BracketConfig {
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
  ): ?BracketConfig {
    $configs = $this->get_all();
    return $configs[$theme_mode][$bracket_placement] ?? null;
  }
}
