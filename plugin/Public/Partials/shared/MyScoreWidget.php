<?php

namespace WStrategies\BMB\Public\Partials\shared;

use WStrategies\BMB\Includes\Domain\Play;

class MyScoreWidget {
  public static function my_score(Play $play, $args = []) {
    $sm_items_end = $args['sm_items_end'] ?? false;
    if (!$play->is_scored()) {
      return '';
    }
    ob_start();
    ?>
			<div class="tw-flex tw-flex-col <?php echo $sm_items_end
     ? 'sm:tw-items-end'
     : ''; ?>">
					<h2 class="tw-font-700 tw-text-32 sm:tw-text-48 tw-text-white"><?php echo esc_html(
       strval($play->get_accuracy_score())
     ); ?>%</h2>
					<span class="tw-font-500 tw-text-12 tw-text-white">My Score</span>
			</div>
		<?php return ob_get_clean();
  }
}
