<?php
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-hooks-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/permissions/class-wpbb-bracket-permissions.php';

class Wpbb_Permissions implements Wpbb_HooksInterface {
  public function load(Wpbb_Loader $loader): void {
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

    $permission_services = [new Wpbb_BracketPermissions()];

    $user_id = $args[1];
    $post_id = $args[2] ?? null;

    foreach ($permission_services as $service) {
      $caps = $service->get_caps();
      if (in_array($requested, $caps)) {
        $allcaps[$cap[0]] = $service->has_cap($requested, $user_id, $post_id);
      }
    }

    return $allcaps;
  }
}
