<?php

use WP_Mock\Tools\TestCase as ToolsTestCase;
use WStrategies\BMB\Includes\Domain\UserProfile;

class UserProfileTest extends ToolsTestCase {
  public function test_get_bio_should_return_content() {
    $profile = new UserProfile([
      'content' => 'This is my bio',
    ]);

    $this->assertEquals('This is my bio', $profile->get_bio());
  }
}
