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

  // Filter types
  public const FILTER_LIVE = 'live';
  public const FILTER_UPCOMING = 'upcoming';
  public const FILTER_SCORED = 'scored';
  public const FILTER_IN_PROGRESS = 'in_progress';
  public const FILTER_COMPLETED = 'completed';
  public const FILTER_ALL = 'all';

  /**
   * Get status query array based on filter type
   */
  public static function getStatusQuery(string $filter): array {
    return match ($filter) {
      self::FILTER_LIVE => [self::STATUS_PUBLISH],
      self::FILTER_UPCOMING => [self::STATUS_UPCOMING],
      self::FILTER_SCORED => [self::STATUS_SCORE, self::STATUS_COMPLETE],
      self::FILTER_IN_PROGRESS => [self::STATUS_SCORE],
      self::FILTER_COMPLETED => [self::STATUS_COMPLETE],
      self::FILTER_ALL => [
        self::STATUS_PUBLISH,
        self::STATUS_SCORE,
        self::STATUS_COMPLETE,
        self::STATUS_UPCOMING,
      ],
      default => [self::STATUS_PUBLISH],
    };
  }
}
