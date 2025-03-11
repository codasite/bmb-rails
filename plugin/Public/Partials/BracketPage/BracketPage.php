<?php
namespace WStrategies\BMB\Public\Partials\BracketPage;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Public\Error\ErrorPage;
use WStrategies\BMB\Public\Partials\TemplateInterface;

class BracketPage implements TemplateInterface {
  public function __construct(
    private BracketRepo $bracket_repo = new BracketRepo()
  ) {
  }
  public function render(): false|string {
    $post = get_post();
    $bracket = $this->bracket_repo->get($post->ID);
    $view = get_query_var('view');
    $action = get_query_var('action');
    $error_page = $this->get_error_page($bracket, $view, $action);
    if ($error_page) {
      return $error_page->render();
    }
    $el_id = 'wpbb-play-bracket';
    $subpage = null;

    switch ($view) {
      case 'leaderboard':
        $subpage = new LeaderboardPage($bracket, [
          'bracket_repo' => $this->bracket_repo,
        ]);
        break;
      case 'copy':
        $el_id = 'wpbb-bracket-builder';
        break;
      case 'copy-new':
        $el_id = 'wpbb-bracket-builder';
        break;
      case 'results':
        switch ($action) {
          case 'update':
            $el_id = 'wpbb-update-bracket-results';
            break;
          default:
            $el_id = 'wpbb-view-bracket-results';
            break;
        }
        break;
      case 'chat':
        $subpage = new BracketChatPage($bracket);
        break;
      case 'go-live':
        $el_id = 'wpbb-go-live';
        break;
      case 'most-popular-picks':
        $el_id = 'wpbb-most-popular-picks';
        break;
    }
    if ($subpage !== null) {
      return $subpage->render();
    }
    return "<div id='$el_id'></div>";
  }
  public function get_error_page(
    ?Bracket $bracket,
    string $view,
    string $action
  ): ?ErrorPage {
    $status_code = 200;
    if (!$bracket) {
      $status_code = 404;
    } elseif (
      $view === 'results' &&
      $action === 'update' &&
      !current_user_can('wpbb_edit_bracket', $bracket->id)
    ) {
      $status_code = 403;
    } elseif (
      $view === 'go-live' &&
      !current_user_can('wpbb_share_bracket', $bracket->id)
    ) {
      $status_code = 403;
    }
    if ($status_code !== 200) {
      return new ErrorPage($status_code);
    }
    return null;
  }
}
