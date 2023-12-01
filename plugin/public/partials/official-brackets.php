<?php
namespace WStrategies\BMB\Public\Partials;
use WStrategies\BMB\Public\Partials\shared\BracketsCommon;

?>
<div class="wpbb-faded-bracket-bg tw-py-20 sm:tw-py-60 tw-px-20">
	<div class="wpbb-reset wpbb-official-brackets tw-flex tw-flex-col tw-gap-30 tw-max-w-screen-lg tw-mx-auto ">
		<div class="tw-flex tw-flex-col tw-py-20 sm:tw-py-30 tw-gap-15 tw-items-center ">
			<div class="logo-svg"></div>
			<h1 class="tw-text-32 sm:tw-text-48 md:tw-text-64 lg:tw-text-80 tw-font-700 tw-text-center">Official BMB Brackets</h1>
		</div>
		<div class="tw-flex tw-flex-col tw-gap-15">
      <div class="tw-flex tw-justify-center tw-gap-10 tw-py-11 tw-flex-wrap">
        <?php echo BracketsCommon::bracket_sort_buttons(); ?>
      </div>
      <?php echo BracketsCommon::public_bracket_list( [ 'tags' => [ 'bmb_official' ] ] ); ?>
		</div>
	</div>
</div>
