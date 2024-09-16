<?php
namespace WStrategies\BMB\Includes\Service\Permissions;

use WStrategies\BMB\Features\Notifications\NotificationRepo;

class NotificationPermissions implements PermissionsServiceInterface {
  private $notification_repo;
  public function __construct($opts = []) {
    $this->notification_repo =
      $opts['notification_repo'] ?? new NotificationRepo();
  }
  public function has_cap($cap, $user_id, $id): bool {
    $notification = $this->notification_repo->get([
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
