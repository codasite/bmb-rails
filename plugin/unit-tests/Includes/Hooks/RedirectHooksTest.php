<?php

use PHPUnit\Framework\TestCase;

class RedirectHooksTest extends TestCase {
  public function test_template_redirect() {
    WP_Mock::userFunction('is_page', [
      'times' => 1,
      'args' => ['dashboard'],
      'return' => true,
    ]);

    $is_page = is_page('dashboard');
    $this->assertTrue($is_page);
  }
}
