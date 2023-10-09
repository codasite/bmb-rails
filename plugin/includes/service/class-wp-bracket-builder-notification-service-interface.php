<?php


interface Wp_Bracket_Builder_Notification_Service_Interface {

    public function notify_participants($tournament_id): void;
}
