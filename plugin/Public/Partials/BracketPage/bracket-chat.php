<?php
namespace WStrategies\BMB\Public\Partials\BracketPage;

$post = get_post();
if (!current_user_can('wpbb_view_bracket_chat', $post->ID)) {
  header('HTTP/1.0 401 Unauthorized');
  wp_die('You do not have permission to view this page.');
}
$title = $post->post_title;
$thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
?>
<div class="wpbb-reset tw-flex tw-flex-col">
	<div class="tw-bg-dark-blue tw-flex tw-flex-col tw-items-center tw-gap-30 tw-px-20 tw-pt-60 tw-pb-60">
		<div class="tw-h-100 tw-w-100 tw-rounded-full tw-bg-white tw-bg-cover tw-bg-center tw-bg-no-repeat" style="background-image: url(<?php echo $thumbnail; ?>)"></div>
		<div class="tw-flex tw-flex-col tw-items-center tw-gap-10">
			<h1 class="tw-text-24 tw-text-center"><?php echo $title; ?></h1>
			<h3 class="tw-leading-none tw-text-14 tw-font-600 tw-px-10 tw-py-[6px] tw-rounded-16 tw-bg-blue">Chatter</h3>
		</div>
	</div>
	<div class="tw-bg-[#02041d] tw-px-20 tw-py-60">
		<h2 class="tw-text-48 tw-font-700 lg:tw-font-800 lg:tw-text-64 tw-text-center">Who You Got?</h2>
		<?php comments_template(); ?>
	</div>
</div>
