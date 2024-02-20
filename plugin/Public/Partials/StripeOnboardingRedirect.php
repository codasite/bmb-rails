<?php
namespace WStrategies\BMB\Public\Partials;

use WStrategies\BMB\Public\Partials\TemplateInterface;

class StripeOnboardingRedirect implements TemplateInterface {
	public function render(): string {
		ob_start();
		?>
		<div id="wpbb-stripe-onboarding-redirect"></div>
		<?php
		return ob_get_clean();
	}
}