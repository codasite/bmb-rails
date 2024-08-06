<?php
namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Public\Partials\BracketPage\BracketPage;
use WStrategies\BMB\Public\Partials\dashboard\DashboardPage;
use WStrategies\BMB\Public\Partials\PlayPage\PlayPage;
use WStrategies\BMB\Public\Partials\StripeOnboardingRedirect;
use WStrategies\BMB\Public\Partials\UserProfile\UserProfilePage;

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

  /**
   * Add shortcode to render events
   *
   * @return void
   */
  public function add_shortcodes(): void {
    add_shortcode('wpbb-bracket-builder', [$this, 'render_bracket_builder']); // This is a page with slug `bracket-template-builder
    add_shortcode('wpbb-official-brackets', [
      $this,
      'render_official_brackets',
    ]);
    add_shortcode('wpbb-celebrity-picks', [$this, 'render_celebrity_picks']); // This is a page with slug `celebrity-picks`
    add_shortcode('wpbb-bracket-preview', [
      $this,
      'render_bracket_product_preview',
    ]);
    add_shortcode('wpbb-dashboard', [new DashboardPage(), 'render']);
    add_shortcode('wpbb-user-profile', [new UserProfilePage(), 'render']);
    add_shortcode('wpbb-stripe-onboarding-redirect', [
      new StripeOnboardingRedirect(),
      'render',
    ]);
    add_shortcode('wpbb-bracket-page', [new BracketPage(), 'render']);
    add_shortcode('wpbb-bracket-play', [new PlayPage(), 'render']);
  }
}
