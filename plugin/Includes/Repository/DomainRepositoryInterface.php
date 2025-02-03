<?php

namespace WStrategies\BMB\Includes\Repository;

/**
 * Interface defining the contract for repository operations.
 */
interface DomainRepositoryInterface {
  /**
   * Retrieves objects based on search criteria.
   *
   * @param array $args {
   *     Optional. Arguments for filtering objects.
   *     @type string  $id               Object ID.
   *     @type int     $user_id          User ID.
   *     @type bool    $is_read          Read status.
   *     @type string  $notification_type Object type.
   *     @type bool    $single           Whether to return a single result.
   * }
   * @return object|object[]|null The found object(s) or null if not found.
   */
  public function get(array $args = []): object|array|null;

  /**
   * Adds a new object.
   *
   * @param object $object Object to add
   * @return object|null The created object or null on failure
   */
  public function add(object $object): ?object;

  /**
   * Updates an object.
   *
   * @param int $id Object ID
   * @param array $fields Fields to update
   * @return object|null Updated object or null on failure
   */
  public function update(int $id, array $fields = []): ?object;

  /**
   * Deletes an object.
   *
   * @param int $id Object ID to delete
   * @return bool Whether deletion was successful
   */
  public function delete(int $id): bool;
}
