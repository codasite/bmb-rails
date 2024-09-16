<?php
namespace WStrategies\BMB\Includes\Hooks;

use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use WP_Comment;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Public\Partials\dashboard\DashboardPage;

class RedirectHooks implements HooksInterface {
  public function load(Loader $loader): void {
    $loader->add_action(
      'template_redirect',
      [$this, 'dashboard_redirect'],
      10,
      0
    );
    $loader->add_action(
      'comment_post_redirect',
      [$this, 'custom_comment_redirect'],
      10,
      2
    );
    $loader->add_action(
      'woocommerce_login_redirect',
      [$this, 'login_redirect'],
      10,
      1
    );
    $loader->add_action(
      'woocommerce_registration_redirect',
      [$this, 'login_redirect'],
      10,
      1
    );
    $loader->add_action('login_redirect', [$this, 'login_redirect'], 10, 1);
  }

  public function dashboard_redirect(): void {
    if (is_page('dashboard') && !is_user_logged_in()) {
      global $wp;
      $login_url = wp_login_url($wp->request);
      wp_redirect($login_url);
      exit();
    }
  }

  public function custom_comment_redirect(
    string $location,
    WP_Comment $comment
  ): string {
    $post_id = (int) $comment->comment_post_ID;
    if (get_post_type($post_id) === Bracket::get_post_type()) {
      $parts = explode('/', $location);
      $anchor = array_pop($parts);
      $location = get_permalink($post_id) . 'chat/' . $anchor;
    }
    return $location;
  }

  public function login_redirect($redirect) {
    // if redirect is empty or contains 'wp-admin' or 'my-account' return dashboard
    if (
      empty($redirect) ||
      strpos($redirect, 'wp-admin') !== false ||
      strpos($redirect, 'my-account') !== false
    ) {
      $redirect = DashboardPage::get_url();
    }
    return $redirect;
  }
}
