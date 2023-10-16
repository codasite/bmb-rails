<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wpbb-bracket-repo.php';

$bracket_repo = new Wpbb_BracketRepo();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_bracket_id'])) {
	if (wp_verify_nonce($_POST['archive_bracket_nonce'], 'archive_bracket_action')) {
		$bracket_repo->update($_POST['archive_bracket_id'], [
			'status' => 'private',
		]);
	}
}