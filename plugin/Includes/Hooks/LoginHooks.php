<?php
namespace WStrategies\BMB\Includes\Hooks;

class LoginHooks implements HooksInterface {
  private string $plugin_name;
  private string $version;

  public function __construct($args = []) {
    $this->plugin_name = $args['plugin_name'];
    $this->version = $args['version'];
  }
  public function load(Loader $loader): void {
    $loader->add_action('login_enqueue_scripts', [
      $this,
      'enqueue_login_scripts',
    ]);
    $loader->add_filter('login_message', [$this, 'filter_login_message']);
    $loader->add_filter('the_privacy_policy_link', '__return_false');
    $loader->add_filter('login_headerurl', [$this, 'login_header_url']);
  }
  public function enqueue_login_scripts(): void {
    wp_enqueue_style(
      $this->plugin_name . '-login',
      plugin_dir_url(dirname(__FILE__, 2)) . 'Public/css/wpbb-login.css',
      [],
      $this->version,
      'all'
    );
    wp_enqueue_script(
      $this->plugin_name . '-login',
      plugin_dir_url(dirname(__FILE__, 2)) . 'Public/js/wpbb-login.js',
      ['jquery'],
      $this->version,
      false
    );
  }
  public function filter_login_message(string $message): string {
    if (strpos(strtolower($message), 'register for this site') !== false) {
      // don't show the register banner. This might remove needed messages? who knows
      return '';
    }
    return $message;
  }

  public function login_header_url(): string {
    return home_url();
  }
}
