<?php
namespace WStrategies\BMB\Features\Notifications\Push\Fakes;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\RegistrationTokens;
use Kreait\Firebase\Messaging\SendReport;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\AppInstance;

class MessagingFake implements Messaging {
  private array $sent_messages = [];

  public function send(
    Message|array $message,
    bool $validateOnly = false
  ): array {
    $this->sent_messages[] = $message;
    return ['message_id' => 'fake_message_id_' . count($this->sent_messages)];
  }

  public function sendAll(
    $messages,
    bool $validateOnly = false
  ): MulticastSendReport {
    return new MulticastSendReport();
  }

  public function sendMulticast(
    $message,
    $registrationTokens,
    bool $validateOnly = false
  ): MulticastSendReport {
    $tokens = RegistrationTokens::fromValue($registrationTokens);
    $reports = [];

    foreach ($tokens as $token) {
      $reports[] = SendReport::success(
        MessageTarget::with(MessageTarget::TOKEN, $token->value()),
        ['message_id' => 'fake_message_id_' . count($this->sent_messages)],
        $message
      );
      $this->sent_messages[] = $message;
    }

    return MulticastSendReport::withItems($reports);
  }

  public function validate($message): array {
    return $this->send($message, true);
  }

  public function validateRegistrationTokens(
    $registrationTokenOrTokens
  ): array {
    return [
      'valid' => [],
      'unknown' => [],
      'invalid' => [],
    ];
  }

  public function subscribeToTopic($topic, $registrationTokenOrTokens): array {
    return [];
  }

  public function subscribeToTopics(
    $topics,
    $registrationTokenOrTokens
  ): array {
    return [];
  }

  public function unsubscribeFromTopic(
    $topic,
    $registrationTokenOrTokens
  ): array {
    return [];
  }

  public function unsubscribeFromTopics(
    $topics,
    $registrationTokenOrTokens
  ): array {
    return [];
  }

  public function unsubscribeFromAllTopics($registrationTokenOrTokens): array {
    return [];
  }

  public function getAppInstance(
    RegistrationToken|string $registrationToken
  ): AppInstance {
    $token =
      $registrationToken instanceof RegistrationToken
        ? $registrationToken
        : RegistrationToken::fromValue($registrationToken);

    return AppInstance::fromRawData($token, ['rel' => ['topics' => []]]);
  }

  /**
   * Get all messages that have been sent through this fake
   *
   * @return array Messages that have been sent
   */
  public function getSentMessages(): array {
    return $this->sent_messages;
  }
}
