<?php
namespace WStrategies\BMB\tests\integration\Traits;

trait SetupAdminUser {
  /**
   * @before
   */
  public function setUpAdminUser(): void {
    $admin_user = self::factory()->user->create([
      'role' => 'administrator',
    ]);
    wp_set_current_user($admin_user);
  }
}
