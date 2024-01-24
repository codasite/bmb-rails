<?php
namespace WStrategies\BMB\Includes\Hooks;

use WP_Comment;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Helpers\Wordpress\Navigation;

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
      [$this, 'redirect_after_login'],
      10,
      0
    );
    $loader->add_action(
      'woocommerce_registration_redirect',
      [$this, 'redirect_after_register'],
      10,
      0
    );
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

  public function redirect_after_login(): string {
    return $this->get_login_redirect_url();
  }

  public function redirect_after_register(): string {
    return $this->get_login_redirect_url();
  }

  private function get_login_redirect_url(): string {
    $redirect_to = $_REQUEST['redirect_to'] ?? '';
    if (empty($redirect_to)) {
      $redirect_to = Navigation::get_page_permalink_by_path('dashboard');
    }
    return $redirect_to;
  }
}
