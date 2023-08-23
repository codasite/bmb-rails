<div class="wpbb-my-profile">
	<h1 class="bb-mb-16">My Profile</h1>
	<h3 class="wpbb-color-grey-50">Overall Tournament Score</h3>
	<div class="wpbb-flex wpbb-gap-10 wpbb-flex-wrap">
		<div class="wpbb-profile-widget wpbb-accuracy-widget">
			<?php echo file_get_contents(plugins_url('../../assets/icons/pie.svg', __FILE__)); ?>
			<div class="wpbb-flex-col wpbb-gap-4">
				<h1 class="wpbb-widget-value">0%</h1>
				<h3 class="wpbb-font-size-20 wpbb-color-grey-50">Accuracy Score</h3>
			</div>
		</div>
		<div class="wpbb-profile-widget wpbb-wins-widget">
			<div class="wpbb-flex-col wpbb-gap-4">
				<h1 class="wpbb-widget-value">2</h1>
				<h3 class="wpbb-font-size-20 wpbb-color-grey-50">Tournament Wins</h3>
			</div>
		</div>
		<div class="wpbb-profile-widget wpbb-total-tournaments-widget">
			<a href="#" class="wpbb-flex wpbb-gap-16 wpbb-align-center">
				<?php echo file_get_contents(plugins_url('../../assets/icons/arrow_up_right.svg', __FILE__)); ?>
				<span class="wpbb-font-weight-500">View My Play History</span>
			</a>
			<div class="wpbb-flex-col wpbb-gap-4">
				<h1 class="wpbb-widget-value">524</h1>
				<h3 class="wpbb-font-size-20 wpbb-color-grey-50">Total Tournaments Played</h3>
			</div>
		</div>
	</div>
</div>