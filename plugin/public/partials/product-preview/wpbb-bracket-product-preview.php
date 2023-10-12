<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/service/product-preview/class-wpbb-product-preview-service.php';

$preview_service = new Wpbb_ProductPreviewService();
$preview_service->localize_script();

?>
<div id="wpbb-product-preview"></div>