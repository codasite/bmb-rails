<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/service/product-preview/class-wp-bracket-builder-product-preview-service.php';

$preview_service = new Wp_Bracket_Builder_Product_Preview_Service();
$preview_service->localize_script();

?>
<div id="wpbb-product-preview"></div>