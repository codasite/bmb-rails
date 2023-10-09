<?php


interface Wp_Bracket_Builder_Notification_Service_Interface {

    public function send_tournament_result_email_update($tournament_id): void;
}
