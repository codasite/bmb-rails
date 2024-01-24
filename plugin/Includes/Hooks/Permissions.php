<?php
namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Service\Permissions\BracketPermissions;
use WStrategies\BMB\Includes\Service\Permissions\NotificationPermissions;
use WStrategies\BMB\Includes\Service\Permissions\PlayPermissions;

class Permissions implements HooksInterface {
  public function load(Loader $loader): void {
    $loader->add_filter('user_has_cap', [$this, 'user_cap_filter'], 10, 3);
  }

  /**
   * Authorization checks. Be sure to add any new caps to the admin role
   */
  public function user_cap_filter($allcaps, $cap, $args) {
    // check if user is admin. if so, bail
    $requested = $args[0];
    if (!str_starts_with($requested, 'wpbb_')) {
      return $allcaps;
    }
    if (
      isset($allcaps['administrator']) &&
      $allcaps['administrator'] === true
    ) {
      return $allcaps;
    }

    $permission_services = [
      new BracketPermissions(),
      new PlayPermissions(),
      new NotificationPermissions(),
    ];

    $user_id = $args[1];
    $post_id = $args[2] ?? null;

    // Find the first service that grants the requested cap
    foreach ($permission_services as $service) {
      $caps = $service->get_caps();
      if (
        in_array($requested, $caps) &&
        $service->has_cap($requested, $user_id, $post_id)
      ) {
        $allcaps[$cap[0]] = true;
        break;
      }
    }

    return $allcaps;
  }
}
