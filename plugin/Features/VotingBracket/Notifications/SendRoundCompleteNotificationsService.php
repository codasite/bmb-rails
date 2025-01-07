<?php

namespace WStrategies\BMB\Features\VotingBracket\Notifications;

use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Features\Bracket\BracketMetaConstants;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\UserRepo;
use WStrategies\BMB\Includes\Service\WordpressFunctions\PermalinkService;

class SendRoundCompleteNotificationsService {
  private readonly EmailServiceInterface $email_service;
  private readonly BracketRepo $bracket_repo;
  private readonly PlayRepo $play_repo;
  private readonly UserRepo $user_repo;
  private readonly PermalinkService $permalink_service;

  /**
   * @var array<RoundCompleteNotificationListenerInterface>
   */
  private array $listeners = [];

  function __construct($args = []) {
    $this->email_service =
      $args['email_service'] ?? (new MailchimpEmailServiceFactory())->create();
    $args['email_service'] = $this->email_service;
    $this->listeners = $args['listeners'] ?? $this->init_listeners($args);
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->user_repo = $args['user_repo'] ?? new UserRepo();
    $this->permalink_service =
      $args['permalink_service'] ?? new PermalinkService();
  }
  /**
   * @return array<RoundCompleteNotificationListenerInterface>
   */
  private function init_listeners($args): array {
    return [
      new RoundCompleteEmailListener($args),
      new RoundCompletePushListener($args),
    ];
  }
  public function send_round_complete_notifications(): void {
    $brackets = $this->bracket_repo->get_all(
      [
        'meta_query' => [
          [
            'key' => BracketMetaConstants::SHOULD_NOTIFY_ROUND_COMPLETE,
            'value' => 1,
          ],
        ],
      ],
      ['fetch_matches' => false, 'fetch_results' => false]
    );
    // get all plays for bracket
    $plays = [];
    foreach ($brackets as $bracket) {
      $this->send_notifications_for_bracket($bracket);
    }
  }
  private function send_notifications_for_bracket(Bracket $bracket): void {
    $plays = $this->play_repo->get_all(
      ['bracket_id' => $bracket->id, 'author__not_in' => [0]],
      ['fetch_bracket' => false]
    );
    foreach ($plays as $play) {
      // get user for each play
      // send email
      $this->send_notifications_for_play($bracket, $play);
    }
    update_post_meta(
      $bracket->id,
      BracketMetaConstants::SHOULD_NOTIFY_ROUND_COMPLETE,
      0
    );
  }
  private function send_notifications_for_play(
    Bracket $bracket,
    Play $play
  ): void {
    $user = $this->user_repo->get_by_id($play->author);
    if (!$user) {
      return;
    }

    foreach ($this->listeners as $listener) {
      $listener->notify($user, $bracket, $play);
    }
  }
}
