<?php

namespace WStrategies\BMB\Features\Notifications\Presentation;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerBase;
use WStrategies\BMB\Includes\Service\Serializer\DateTimeSerializer;
use WStrategies\BMB\Includes\Service\Serializer\EnumSerializer;

class NotificationSerializer extends ApiSerializerBase {
  /**
   * Deserialize array data into a Notification object
   *
   * @param array|string $data The data to deserialize
   * @return Notification
   */
  public function deserialize(array|string $data): Notification {
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
        'serializer' => new DateTimeSerializer(),
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
        'serializer' => new EnumSerializer(NotificationType::class),
      ],
    ];
  }
}
