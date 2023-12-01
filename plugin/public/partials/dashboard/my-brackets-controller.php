<?php
namespace WStrategies\BMB\Public\Partials\dashboard;


use WStrategies\BMB\Includes\Repository\BracketRepo;

$bracket_repo = new BracketRepo();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_bracket_id'])) {
	if (wp_verify_nonce($_POST['archive_bracket_nonce'], 'archive_bracket_action')) {
		$bracket_repo->update($_POST['archive_bracket_id'], [
			'status' => 'private',
		]);
	}
}
