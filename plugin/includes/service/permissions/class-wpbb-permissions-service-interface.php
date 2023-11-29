<?php
interface Wpbb_PermissionsServiceInterface {
  public function has_cap($cap, $user_id, $post_id): bool;
  public static function get_caps(): array;
}
