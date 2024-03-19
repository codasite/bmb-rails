<?php
namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Hooks\CustomQuery\CustomPlayQuery;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\Includes\Service\TournamentEntryService;
use WStrategies\BMB\Includes\Utils;

class PublicHooks implements HooksInterface {
  private $play_repo;
  private $play_query;
  private Utils $utils;

  public function __construct($opts = []) {
    $this->play_query = $opts['play_query'] ?? new CustomPlayQuery();
    $this->play_repo = $opts['play_repo'] ?? new PlayRepo();
    $this->utils = $opts['utils'] ?? new Utils();
  }

  public function load(Loader $loader): void {
    $loader->add_action('init', [$this, 'add_rewrite_tags'], 10, 0);
    $loader->add_action('init', [$this, 'add_rewrite_rules'], 10, 0);
    $loader->add_action('init', [$this, 'add_roles']);
    $loader->add_filter('query_vars', [$this, 'add_query_vars']);
    $loader->add_filter('posts_clauses', [$this, 'custom_query_fields'], 10, 2);

    $loader->add_action(
      'woocommerce_subscription_status_active',
      [$this, 'add_bmb_plus_role'],
      10,
      1
    );

    $loader->add_action(
      'woocommerce_subscription_status_cancelled',
      [$this, 'remove_bmb_plus_role'],
      10,
      1
    );
    $loader->add_action(
      'wpbb_after_play_printed',
      [$this, 'after_play_printed'],
      10,
      1
    );
  }

  public function add_rewrite_tags(): void {
    add_rewrite_tag('%tab%', '([^&]+)');
    add_rewrite_tag('%view%', '([^&]+)');
  }

  public function add_rewrite_rules(): void {
    // Be sure to flush the rewrite rules after adding new rules
    add_rewrite_rule(
      '^dashboard/profile/?',
      'index.php?pagename=dashboard&tab=profile',
      'top'
    );
    add_rewrite_rule(
      '^dashboard/tournaments/page/([0-9]+)/?',
      'index.php?pagename=dashboard&tab=tournaments&paged=$matches[1]',
      'top'
    );
    add_rewrite_rule(
      '^dashboard/tournaments/?',
      'index.php?pagename=dashboard&tab=tournaments',
      'top'
    );
    add_rewrite_rule(
      '^dashboard/brackets/page/([0-9]+)/?',
      'index.php?pagename=dashboard&tab=brackets&paged=$matches[1]',
      'top'
    );
    add_rewrite_rule(
      '^dashboard/brackets/?',
      'index.php?pagename=dashboard&tab=brackets',
      'top'
    );
    add_rewrite_rule(
      '^dashboard/play-history/page/([0-9]+)/?',
      'index.php?pagename=dashboard&tab=play-history&paged=$matches[1]',
      'top'
    );
    add_rewrite_rule(
      '^dashboard/play-history/?',
      'index.php?pagename=dashboard&tab=play-history',
      'top'
    );
    add_rewrite_rule(
      '^plays/([^/]+)/([^/]+)/?',
      'index.php?bracket_play=$matches[1]&view=$matches[2]',
      'top'
    );
    add_rewrite_rule(
      '^brackets/([^/]+)/([^/]+)/?([^/]+/?)?',
      'index.php?bracket=$matches[1]&view=$matches[2]&action=$matches[3]',
      'top'
    );
  }

  public function add_query_vars($vars) {
    $vars[] = 'tab';
    $vars[] = 'status';
    $vars[] = 'view';
    $vars[] = 'role';
    $vars[] = 'action';
    return $vars;
  }

  public function add_roles(): void {
    add_role('bmb_plus', 'BMB Plus', [
      'wpbb_share_bracket' => true,
      'wpbb_bust_play' => true,
      'wpbb_enable_chat' => true,
      'wpbb_create_paid_bracket' => true,
      'read' => true,
    ]);
    add_role('bmb_vip', 'BMB VIP', [
      'wpbb_share_bracket' => true,
      'wpbb_bust_play' => true,
      'wpbb_enable_chat' => true,
      'wpbb_play_bracket_for_free' => true,
      'wpbb_create_paid_bracket' => true,
      'read' => true,
    ]);
  }

  public function custom_query_fields($clauses, $query_object) {
    if ($query_object->get('post_type') === 'bracket_play') {
      return $this->play_query->handle_custom_query($clauses, $query_object);
    }
    return $clauses;
  }

  public function add_bmb_plus_role($subscription): void {
    $user_id = $subscription->get_user_id();
    $user = get_user_by('id', $user_id);
    $user->add_role('bmb_plus');
  }

  public function remove_bmb_plus_role($subscription): void {
    $user_id = $subscription->get_user_id();
    $user = get_user_by('id', $user_id);
    $user->remove_role('bmb_plus');
  }

  public function after_play_printed($play_id): void {
    $play = $this->play_repo->get($play_id);
    if (!$play) {
      $this->utils->log_error(
        'ERROR: trying to mark play as printed but play not found for play: ' .
          $play_id
      );
      return;
    }
    $data = [
      'is_printed' => true,
    ];

    $this->play_repo->update($play_id, $data);
  }
}
