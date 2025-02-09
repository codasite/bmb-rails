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
        'type' => 'integer',
        'required' => false,
      ],
      'user_id' => [
        'type' => 'integer',
        'required' => true,
      ],
      'title' => [
        'type' => 'string',
        'required' => true,
      ],
      'message' => [
        'type' => 'string',
        'required' => false,
      ],
      'timestamp' => [
        'type' => 'string',
        'required' => false,
        'serializer' => new DateTimeSerializer(),
      ],
      'is_read' => [
        'type' => 'boolean',
        'required' => false,
        'default' => false,
      ],
      'link' => [
        'type' => 'string',
        'required' => false,
        'default' => null,
      ],
      'notification_type' => [
        'type' => 'string',
        'required' => true,
        'serializer' => new EnumSerializer(NotificationType::class),
      ],
    ];
  }
}
