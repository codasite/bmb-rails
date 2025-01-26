<?php
namespace WStrategies\BMB\Includes\Service\Permissions;

use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationSubscriptionRepo;

class NotificationPermissions implements PermissionsServiceInterface {
  private $notification_sub_repo;
  public function __construct($opts = []) {
    $this->notification_sub_repo =
      $opts['notification_sub_repo'] ?? new NotificationSubscriptionRepo();
  }
  public function has_cap($cap, $user_id, $id): bool {
    $notification = $this->notification_sub_repo->get([
      'id' => $id,
      'single' => true,
    ]);
    if (!$notification) {
      return false;
    }
    if ((int) $notification->user_id === (int) $user_id) {
      return true;
    }
    return false;
  }

  public static function get_caps(): array {
    return ['wpbb_delete_notification'];
  }
}
