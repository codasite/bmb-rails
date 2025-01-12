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
  private array $token_responses = [];

  public function configure_response(
    string $token,
    callable $responseBuilder
  ): void {
    $this->token_responses[$token] = $responseBuilder;
  }

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
      $this->sent_messages[] = $message;

      if (isset($this->token_responses[$token->value()])) {
        $reports[] = $this->token_responses[$token->value()](
          MessageTarget::with(MessageTarget::TOKEN, $token->value()),
          $message
        );
      } else {
        $reports[] = SendReportFake::success(
          MessageTarget::with(MessageTarget::TOKEN, $token->value()),
          ['message_id' => 'fake_id'],
          $message
        );
      }
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
