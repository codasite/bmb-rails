<?php


interface Wpbb_Notification_Service_Interface
{

	public function notify_tournament_results_updated($tournament_id): void;
}
