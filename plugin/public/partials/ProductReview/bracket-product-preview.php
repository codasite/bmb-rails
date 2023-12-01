<?php
namespace WStrategies\BMB\Public\Partials\ProductReview;


use WStrategies\BMB\Includes\Service\ProductPreview\ProductPreviewService;

$preview_service = new ProductPreviewService();
$ajax_obj        = $preview_service->get_ajax_obj();
wp_localize_script(
	'wpbb-bracket-builder-react',
	'wpbb_bracket_product_preview_obj',
	$ajax_obj
);

?>
<div id="wpbb-product-preview"></div>
