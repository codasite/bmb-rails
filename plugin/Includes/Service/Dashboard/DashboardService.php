<?php

namespace WStrategies\BMB\Includes\Service\Dashboard;

use WP_Query;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;

class DashboardService {
  private BracketRepo $bracket_repo;
  private PlayRepo $play_repo;

  public function __construct($args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
  }
  public function get_managed_brackets(int $paged, string $status) {
    $all_status = ['publish', 'private', 'score', 'complete'];
    $active_status = ['publish', 'private'];
    $scored_status = ['score', 'complete'];

    if ($status === 'all') {
      $post_status = $all_status;
    } elseif ($status === 'active') {
      $post_status = $active_status;
    } elseif ($status === 'scored') {
      $post_status = $scored_status;
    } else {
      $post_status = $all_status;
    }

    $the_query = new WP_Query([
      'post_type' => Bracket::get_post_type(),
      'author' => get_current_user_id(),
      'posts_per_page' => 6,
      'paged' => $paged,
      'post_status' => $post_status,
    ]);

    $brackets = $this->bracket_repo->get_all($the_query);
    return [
      'brackets' => $brackets,
      'max_num_pages' => $the_query->max_num_pages,
    ];
  }

  public function get_tournaments(int $paged, string $status) {
    $all_status = ['publish', 'private', 'score', 'complete'];
    $active_status = ['publish', 'private'];
    $scored_status = ['score', 'complete'];

    if ($status === 'all') {
      $post_status = $all_status;
    } elseif ($status === 'active') {
      $post_status = $active_status;
    } elseif ($status === 'scored') {
      $post_status = $scored_status;
    } else {
      $post_status = $all_status;
    }
    // you could get all the plays and not paginate and then,
    // or get the plays with a unique bracket id

    // get all plays for user
    $plays = $this->play_repo->get_all_by_user(get_current_user_id());

    $the_query = new WP_Query([
      'post_type' => Bracket::get_post_type(),
      'author' => get_current_user_id(),
      'posts_per_page' => 6,
      'paged' => $paged,
      'post_status' => $post_status,
    ]);

    $brackets = $this->bracket_repo->get_all($the_query);
    return [
      'brackets' => $brackets,
      'max_num_pages' => $the_query->max_num_pages,
    ];
  }
}
