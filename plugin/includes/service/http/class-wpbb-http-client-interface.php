<?php

interface Wpbb_HttpClientInterface {
  public function send_many($requests = []): array;
}
