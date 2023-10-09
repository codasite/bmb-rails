<?php
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wp-bracket-builder-notification-service-interface.php';

class Mock_Notification_Service implements Wp_Bracket_Builder_Notification_Service_Interface {

	public function send_tournament_result_email_update($tournament_id): void {
		// Do nothing
	}
}
