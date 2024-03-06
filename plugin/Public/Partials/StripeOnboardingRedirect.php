<?php
namespace WStrategies\BMB\Public\Partials;

class StripeOnboardingRedirect implements TemplateInterface {
  public static function get_url(): string {
    return get_permalink(get_page_by_path('stripe-onboarding-redirect'));
  }
  public function render(): string {
    ob_start(); ?>
		<div id="wpbb-stripe-onboarding-redirect"></div>
		<?php return ob_get_clean();
  }
}
