<?php

interface Wpbb_NotificationService_Interface {
  public function notify_bracket_results_updated(
    Wpbb_Bracket|int|null $bracket
  ): void;
}
