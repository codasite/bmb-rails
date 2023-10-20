<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/service/product-preview/class-wpbb-product-preview-service.php';

$preview_service = new Wpbb_ProductPreviewService();
$ajax_obj = $preview_service->get_ajax_obj();
wp_localize_script(
	'wpbb-bracket-builder-react',
	'wpbb_bracket_product_preview_obj',
	$ajax_obj
);

?>
<div id="wpbb-product-preview"></div>