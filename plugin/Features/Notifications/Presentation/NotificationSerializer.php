<?php

namespace WStrategies\BMB\Features\Notifications\Presentation;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerBase;

class NotificationSerializer extends ApiSerializerBase {
  /**
   * Deserialize array data into a Notification object
   *
   * @param array $data The data to deserialize
   * @return Notification
   */
  public function deserialize(array $data): Notification {
    $obj_data = $this->get_object_data($data);
    return new Notification($obj_data);
  }

  /**
   * Define the fields to be serialized/deserialized
   *
   * @return array The field definitions
   */
  public function get_serialized_fields(): array {
    return [
      'id' => [
        'required' => false,
      ],
      'user_id' => [
        'required' => true,
      ],
      'title' => [
        'required' => true,
      ],
      'message' => [
        'required' => true,
      ],
      'timestamp' => [
        'required' => true,
      ],
      'is_read' => [
        'required' => false,
        'default' => false,
      ],
      'link' => [
        'required' => false,
        'default' => null,
      ],
      'notification_type' => [
        'required' => true,
      ],
    ];
  }
}
