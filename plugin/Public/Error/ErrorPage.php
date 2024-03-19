<?php
namespace WStrategies\BMB\Public\Error;

use WStrategies\BMB\Public\Partials\TemplateInterface;

class ErrorPage implements TemplateInterface {
  public function __construct(private int $status_code = 500) {
  }

  public function render(): false|string {
    switch ($this->status_code) {
      case 404:
        $header = 'HTTP/1.0 404 Not Found';
        $display_text = 'Page not found.';
        break;
      case 403:
        $header = 'HTTP/1.0 403 Forbidden';
        $display_text = 'Looks like you don\'t have access to this page.';
        break;
      default:
        $header = 'HTTP/1.0 500 Internal Server Error';
        $display_text = 'An error occurred.';
        break;
    }
    header($header);
    ob_start();
    ?>
    <div class="wpbb-reset tw-bg-dd-blue tw-min-h-screen">
			<div class="tw-flex tw-flex-col">
				<div class="tw-flex tw-flex-col md:tw-flex-row-reverse tw-py-60 tw-gap-15 tw-items-center md:tw-justify-between tw-max-w-screen-lg tw-m-auto tw-px-20 lg:tw-px-0">
					<h1 class="tw-text-24 sm:tw-text-36"><?= $display_text ?></h1>
				</div>
			</div>
    </div>
    <?php return ob_get_clean();
  }
}

/**
 * The template for displaying 404 pages (Not Found)
 */

?>
