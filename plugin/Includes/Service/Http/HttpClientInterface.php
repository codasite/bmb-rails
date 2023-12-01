<?php
namespace WStrategies\BMB\Includes\Service\Http;

interface HttpClientInterface {
  public function send_many($requests = []): array;
}
