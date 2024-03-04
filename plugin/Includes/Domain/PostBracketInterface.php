<?php
namespace WStrategies\BMB\Includes\Domain;

/**
 * This interface can be implemented by any class that defines a type of bracket.
 */

interface PostBracketInterface {
  public function get_post_id(): int;
  /**
   * A series of matches representing the bracket's initial structure.
   *
   * @return BracketMatch[]
   */
  public function get_matches(): array;

  /**
   * A series of match picks representing a bracket outcome.
   *
   * @return Pick[]
   */
  public function get_picks(): array;

  /**
   * The title of the bracket
   *
   * @return string
   */
  public function get_title(): string;

  /**
   * The bracket date
   *
   * @return string
   */
  public function get_date(): string;

  /**
   * The number of teams in the bracket
   *
   * @return int
   */
  public function get_num_teams(): int;
}
