<div class="tw-flex tw-flex-col tw-gap-15">
	<h1 class="tw-mb-16">My Profile</h1>
	<h3 class="tw-text-white/50">Overall Tournament Score</h3>
	<div class="tw-flex tw-gap-10 tw-flex-wrap">
		<div class="tw-flex tw-flex-col tw-w-[340px] tw-h-[308px] tw-p-30 tw-border-2 tw-border-solid tw-border-white/10 tw-rounded-16 tw-justify-between tw-bg-green/15 ">
			<!-- This pie should update based on the user's accuracy score -->
			<?php echo file_get_contents(plugins_url('../../assets/icons/pie.svg', __FILE__)); ?>
			<div class="tw-flex tw-flex-col tw-gap-4">
				<!-- This should be the user's accuracy score -->
				<h1>0%</h1>
				<h3 class="tw-text-20 tw-text-white/50">Accuracy Score</h3>
			</div>
		</div>
		<div class="tw-flex tw-flex-col tw-w-[340px] tw-h-[308px] tw-p-30 tw-border-2 tw-border-solid tw-border-white/10 tw-rounded-16 tw-justify-end">
			<div class="tw-flex tw-flex-col tw-gap-4">
				<!-- This is the number of tournaments the user has won -->
				<h1>2</h1>
				<h3 class="tw-text-20 tw-text-white/50">Tournament Wins</h3>
			</div>
		</div>
		<div class="tw-flex tw-flex-col tw-w-[340px] tw-h-[308px] tw-p-30 tw-border-2 tw-border-solid tw-border-white/10 tw-rounded-16 tw-justify-between">
			<a href="<?php echo get_permalink() . 'play-history'; ?>" class="tw-flex tw-gap-16 tw-items-center hover:tw-text-blue">
				<?php echo file_get_contents(plugins_url('../../assets/icons/arrow_up_right.svg', __FILE__)); ?>
				<span class="tw-font-500">View My Play History</span>
			</a>
			<div class="tw-flex tw-flex-col tw-gap-4">
				<!-- This is the number of tournaments the user has played -->
				<h1>524</h1>
				<h3 class="tw-text-20 tw-text-white/50">Total Tournaments Played</h3>
			</div>
		</div>
	</div>
</div>