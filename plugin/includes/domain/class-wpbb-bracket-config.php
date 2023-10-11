<?php

class Wpbb_BracketConfig
{
	/**
	 * @var string
	 */
	public $html;

	/**
	 * @var string
	 */
	public $theme_mode;

	/**
	 * @var string
	 */
	public $img_url;

	/**
	 * @var string
	 */
	public $bracket_placement;

	public function __construct(string $html, string $theme_mode, string $img_url, string $bracket_placement) {
		$this->html = $html;
		$this->theme_mode = $theme_mode;
		$this->img_url = $img_url;
		$this->bracket_placement = $bracket_placement;
	}
}
