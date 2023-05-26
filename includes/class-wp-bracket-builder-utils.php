<?php
class Wp_Bracket_Builder_Utils {
	public function set_session_value($key, $value) {
		if (!session_id()) {
			session_start();
		}
		$_SESSION[$key] = $value;
	}

	// Get value from user session
	public function get_session_value($key) {
		if (!session_id()) {
			session_start();
		}
		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
		return null;
	}
}
