<?php

namespace WStrategies\BMB\Includes\Hooks;

class MobileAppHooks implements HooksInterface {
  public function load(Loader $loader): void {
    $loader->add_action(
      'wp_is_application_passwords_available',
      [$this, 'is_application_passwords_available'],
      10,
      1
    );
    $loader->add_action(
      'set_logged_in_cookie',
      [$this, 'set_logged_in_cookie'],
      10,
      6
    );
    // Add filter to hide subscription products in mobile app
    $loader->add_filter(
      'woocommerce_product_is_visible',
      [$this, 'filter_subscription_products'],
      10,
      2
    );
  }

  public function is_application_passwords_available(): bool {
    return true;
  }

  public function filter_subscription_products($visible, $product_id): bool {
    // Check if request is from mobile app
    if (
      !empty($_SERVER['HTTP_USER_AGENT']) &&
      $_SERVER['HTTP_USER_AGENT'] === 'BackMyBracket-MobileApp'
    ) {
      // Check if product is a subscription
      $product = wc_get_product($product_id);
      if ($product && $product->is_type('subscription')) {
        return false;
      }
    }
    return $visible;
  }

  public function set_logged_in_cookie(
    $logged_in_cookie,
    $expire,
    $expiration,
    $user_id,
    $scheme,
    $token
  ): void {
    // Manually create nonce using the new session token to ensure it's valid for subsequent requests
    // We can't use wp_create_nonce() here because it still uses the old session token
    $uid = (int) $user_id;
    $action = 'wp_rest';
    $i = wp_nonce_tick($action);
    $nonce = substr(
      wp_hash($i . '|' . $action . '|' . $uid . '|' . $token, 'nonce'),
      -12,
      10
    );
    // Set cookie to expire in 24 hours
    $expiration = time() + 24 * HOUR_IN_SECONDS;

    // Set secure cookie if site is using HTTPS
    $secure = is_ssl();

    // Set the nonce in a cookie
    setcookie(
      'wordpress_rest_nonce', // Cookie name
      $nonce, // Cookie value
      $expiration, // Expiration time
      COOKIEPATH, // Cookie path
      COOKIE_DOMAIN, // Cookie domain
      $secure, // Only send over HTTPS if site uses it
      true // HTTPOnly flag
    );
  }
}
