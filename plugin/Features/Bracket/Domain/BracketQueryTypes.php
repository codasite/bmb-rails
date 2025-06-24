<?php

namespace WStrategies\BMB\Features\Bracket\Domain;

/**
 * Shared types and constants for bracket queries
 */
class BracketQueryTypes {
  // Status constants
  public const STATUS_PUBLISH = 'publish';
  public const STATUS_SCORE = 'score';
  public const STATUS_COMPLETE = 'complete';
  public const STATUS_UPCOMING = 'upcoming';
  public const STATUS_PRIVATE = 'private';

  // Filter types
  public const FILTER_LIVE = 'live';
  public const FILTER_UPCOMING = 'upcoming';
  public const FILTER_PRIVATE = 'private';
  public const FILTER_IN_PROGRESS = 'in_progress';
  public const FILTER_COMPLETED = 'completed';
  public const FILTER_SCORED = 'scored';
  public const FILTER_ALL = 'all';

  // Role constants
  public const ROLE_HOSTING = 'hosting';
  public const ROLE_PLAYING = 'playing';

  /**
   * Private mapping of filter types to status arrays
   */
  private static array $filter_status_mapping = [
    self::FILTER_LIVE => [self::STATUS_PUBLISH, self::STATUS_SCORE],
    self::FILTER_UPCOMING => [self::STATUS_UPCOMING],
    self::FILTER_PRIVATE => [self::STATUS_PRIVATE],
    self::FILTER_IN_PROGRESS => [self::STATUS_SCORE],
    self::FILTER_COMPLETED => [self::STATUS_COMPLETE],
    self::FILTER_SCORED => [self::STATUS_SCORE, self::STATUS_COMPLETE],
    self::FILTER_ALL => [
      self::STATUS_PUBLISH,
      self::STATUS_PRIVATE,
      self::STATUS_UPCOMING,
      self::STATUS_SCORE,
      self::STATUS_COMPLETE,
    ],
  ];

  /**
   * Get status query array based on filter type
   */
  public static function getStatusQuery(string $filter): array {
    return self::$filter_status_mapping[$filter] ?? [self::STATUS_PUBLISH];
  }

  /**
   * Get valid filter types
   */
  public static function getValidFilters(): array {
    return array_keys(self::$filter_status_mapping);
  }

  /**
   * Get valid roles
   */
  public static function getValidRoles(): array {
    return [self::ROLE_HOSTING, self::ROLE_PLAYING];
  }

  /**
   * Check if a filter is valid
   */
  public static function isValidFilter(string $filter): bool {
    return array_key_exists($filter, self::$filter_status_mapping);
  }

  /**
   * Check if a role is valid
   */
  public static function isValidRole(string $role): bool {
    return in_array($role, self::getValidRoles());
  }
}
