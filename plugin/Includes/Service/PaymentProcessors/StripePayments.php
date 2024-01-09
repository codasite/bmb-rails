<?php
namespace WStrategies\BMB\Includes\Service\PaymentProcessors;

class StripePayments {
  // public function create_payment_intent_for_paid_bracket($bracket_id) {
// 	$bracket = $this->bracket_repo->get($bracket_id);
// 	$fee = $this->bracket_product_utils->get_bracket_fee($bracket_id);
// 	$amount = $fee * 100;
// 	$intent = $this->stripe->paymentIntents->create([
// 		'amount' => $amount,
// 		'currency' => 'usd',
// 		'metadata' => [
// 			'bracket_id' => $bracket_id,
// 		],
// 	]);
// 	return $intent;
// }
}
