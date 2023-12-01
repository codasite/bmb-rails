<?php
namespace WStrategies\BMB\Includes\Service\Permissions;

interface PermissionsServiceInterface {
  public function has_cap($cap, $user_id, $post_id): bool;
  public static function get_caps(): array;
}
