<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wpbb-utils.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'vendor/autoload.php';

class Wp_Bracket_Builder_Bracket_Pick_Service {

	/**
	 * @var Wpbb_Utils
	 */
	private $utils;

	/**
	 * @var Wp_Bracket_Builder_Lambda_Service
	 */
	private $lamda_service;

	// /**
	//  * @var Wp_Bracket_Builder_Bracket_Pick
	//  */
	// private $bracket_pick;

	public function __construct($bracket_pick = null) {
		$this->utils = new Wpbb_Utils();
		$this->lamda_service = new Wp_Bracket_Builder_Lambda_Service();
		// $this->bracket_pick = $bracket_pick;
	}

	// public function set_bracket_pick($bracket_pick) {
	// 	$this->bracket_pick = $bracket_pick;
	// }

	public function generate_images(Wp_Bracket_Builder_Bracket_Play $bracket_pick) {
		// if ($this->bracket_pick == null) {
		// 	return new WP_Error('error', __('Bracket pick object is null.', 'text-domain'), array('status' => 500));
		// }

		$html = $bracket_pick->html;
		$convert_params = array(
			'html' => $html,
			'inchHeight' => 16,
			'inchWidth' => 12,
			's3Key' => 'bracket-pick-test',
			// 'themeMode' => 'dark',
			// 'bracketPlacement' => 'left',
		);

		// convert html to image
		$res = $this->lamda_service->html_to_image($convert_params);

		if (!is_wp_error($res) && isset($res['imageUrl'])) {
			$bracket_pick->img_url = $res['imageUrl'];
			// build a config object
			// $config = new Wp_Bracket_Builder_Bracket_Config($body['html'], $theme_mode, $res['imageUrl'], $bracket_placement);
			// // Add the image url to the user's session
			// $config_repo = new Wp_Bracket_Builder_Bracket_Config_Repository();
			// $config_repo->add($config);

			return $bracket_pick;
		} else {
			// $error = $res instanceof WP_Error ? $res : new WP_Error('error', __('Error converting HTML to image. Image url not found. Response: ' . json_encode($res), 'text-domain'), array('status' => 500));
			$this->utils->log('Error converting HTML to image. Image url not found. Response: ' . json_encode($res), 'error');
			return $bracket_pick;
		}
	}
}
