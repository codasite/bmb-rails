<?php

namespace WStrategies\BMB\Features\Notifications\Push\Fakes;

use Kreait\Firebase\Messaging\Message;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\SendReport;

class SendReportFake extends SendReport {
  private bool $is_invalid_target = false;
  private bool $is_unknown_token = false;
  private bool $is_invalid_message = false;
  private bool $is_success;
  private MessageTarget $target;
  private ?Message $message;

  private function __construct(
    MessageTarget $target,
    bool $is_success = true,
    ?Message $message = null
  ) {
    $this->target = $target;
    $this->is_success = $is_success;
    $this->message = $message;
  }

  public static function success(
    MessageTarget $target,
    array $response = ['message_id' => 'fake_id'],
    ?Message $message = null
  ): self {
    return new self($target, true, $message);
  }

  public static function failure(
    MessageTarget $target,
    \Throwable $error = null,
    ?Message $message = null
  ): self {
    return new self($target, false, $message);
  }

  public function withInvalidTarget(): self {
    $this->is_invalid_target = true;
    $this->is_success = false;
    return $this;
  }

  public function withUnknownToken(): self {
    $this->is_unknown_token = true;
    $this->is_success = false;
    return $this;
  }

  public function withInvalidMessage(): self {
    $this->is_invalid_message = true;
    $this->is_success = false;
    return $this;
  }

  public function target(): MessageTarget {
    return $this->target;
  }

  public function message(): ?Message {
    return $this->message;
  }

  public function isSuccess(): bool {
    return $this->is_success;
  }

  public function isFailure(): bool {
    return !$this->is_success;
  }

  public function messageTargetWasInvalid(): bool {
    return $this->is_invalid_target;
  }

  public function messageWasSentToUnknownToken(): bool {
    return $this->is_unknown_token;
  }

  public function messageWasInvalid(): bool {
    return $this->is_invalid_message;
  }

  public function error(): ?\Throwable {
    return $this->isFailure() ? new \Exception('Fake error') : null;
  }

  public function result(): ?array {
    return $this->isSuccess() ? ['message_id' => 'fake_id'] : null;
  }
}
