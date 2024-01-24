<?php
namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Public\Partials\dashboard\DashboardPage;

class PublicShortcodes implements HooksInterface {
  public function load(Loader $loader): void {
    $loader->add_action('init', [$this, 'add_shortcodes']);
  }
  public function render_bracket_product_preview(): false|string {
    ob_start();
    include WPBB_PLUGIN_DIR .
      'Public/Partials/ProductPreview/bracket-product-preview.php';
    return ob_get_clean();
  }

  public function render_bracket_builder(): string {
    return '<div id="wpbb-bracket-builder"></div>';
  }

  public function render_official_brackets(): false|string {
    ob_start();
    include WPBB_PLUGIN_DIR . 'Public/Partials/official-brackets.php';
    return ob_get_clean();
  }

  public function render_celebrity_picks(): false|string {
    ob_start();
    include WPBB_PLUGIN_DIR . 'Public/Partials/celebrity-brackets.php';
    return ob_get_clean();
  }

  public function render_bracket_page(): false|string {
    ob_start();
    include WPBB_PLUGIN_DIR . 'Public/Partials/bracket-page.php';
    return ob_get_clean();
  }

  public function render_bracket_play_page(): false|string {
    ob_start();
    include WPBB_PLUGIN_DIR . 'Public/Partials/PlayPage/play-page.php';
    return ob_get_clean();
  }

  public function render_user_profile_page(): false|string {
    ob_start();
    include WPBB_PLUGIN_DIR . 'Public/Partials/UserProfile/user-profile.php';
    return ob_get_clean();
  }

  /**
   * Add shortcode to render events
   *
   * @return void
   */
  public function add_shortcodes(): void {
    add_shortcode('wpbb-bracket-builder', [$this, 'render_bracket_builder']); // This is a page with slug `bracket-template-builder
    add_shortcode('wpbb-dashboard', [new DashboardPage(), 'render']); // This is a page with slug `dashboard`
    add_shortcode('wpbb-official-brackets', [
      $this,
      'render_official_brackets',
    ]); // This is a page with slug `official-brackets`
    add_shortcode('wpbb-celebrity-picks', [$this, 'render_celebrity_picks']); // This is a page with slug `celebrity-picks`
    add_shortcode('wpbb-bracket-page', [$this, 'render_bracket_page']); // This is a single post type template for bracket_template posts
    add_shortcode('wpbb-bracket-play', [$this, 'render_bracket_play_page']); // This is a single post type template for bracket_play posts
    add_shortcode('wpbb-bracket-preview', [
      $this,
      'render_bracket_product_preview',
    ]); // This is a single post type template for woocommerce product posts with the `bracket-ready` tag
    add_shortcode('wpbb-user-profile', [$this, 'render_user_profile_page']); // This is a single post type template for user_profile posts
  }
}
