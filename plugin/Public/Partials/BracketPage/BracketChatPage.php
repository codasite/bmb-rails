<?php
namespace WStrategies\BMB\Public\Partials\BracketPage;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Public\Error\ErrorPage;
use WStrategies\BMB\Public\Partials\TemplateInterface;

class BracketChatPage implements TemplateInterface {
  public function __construct(private Bracket $bracket) {
  }

  public function render(): string|false {
    $error_page = $this->get_error_page($this->bracket);
    if ($error_page) {
      return $error_page->render();
    }
    ob_start();
    ?>
		<div class="wpbb-reset tw-flex tw-flex-col">
			<div class="tw-bg-dark-blue tw-flex tw-flex-col tw-items-center tw-gap-30 tw-px-20 tw-pt-60 tw-pb-60">
				<div class="tw-h-100 tw-w-100 tw-rounded-full tw-bg-white tw-bg-cover tw-bg-center tw-bg-no-repeat" style="background-image: url(<?php echo $this
      ->bracket->thumbnail_url; ?>)"></div>
				<div class="tw-flex tw-flex-col tw-items-center tw-gap-10">
					<h1 class="tw-text-24 tw-text-center"><?php echo $this->bracket->title; ?></h1>
					<h3 class="tw-leading-none tw-text-14 tw-font-600 tw-px-10 tw-py-[6px] tw-rounded-16 tw-bg-blue">Chatter</h3>
				</div>
			</div>
			<div class="tw-bg-[#02041d] tw-px-20 tw-py-60">
				<h2 class="tw-text-48 tw-font-700 lg:tw-font-800 lg:tw-text-64 tw-text-center">Who You Got?</h2>
				<?php comments_template(); ?>
			</div>
		</div>
		<?php return ob_get_clean();
  }

  public function get_error_page(?Bracket $bracket): ?ErrorPage {
    if (!current_user_can('wpbb_view_bracket_chat', $bracket->id)) {
      return new ErrorPage(403);
    }
    return null;
  }
}

$post = get_post();
