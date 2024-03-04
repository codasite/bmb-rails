<?php

namespace WStrategies\BMB\Includes\Service\Play;

use WStrategies\BMB\Includes\Controllers\ApiListeners\BracketPlayCreateListenerBase;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato\GelatoProductIntegration;
use WStrategies\BMB\Includes\Service\ProductIntegrations\ProductIntegrationInterface;

class PlayImageService extends BracketPlayCreateListenerBase {
  private ProductIntegrationInterface $product_integration;
  private bool $should_generate_play_image = false;

  /**
   * @param array<mixed> $args
   */
  public function __construct($args = []) {
    $this->product_integration =
      $args['product_integration'] ?? new GelatoProductIntegration();
  }

  public function filter_request_params(array $data): array {
    if (isset($data['generate_images']) && $data['generate_images'] === true) {
      $this->should_generate_play_image = true;
    }
    return $data;
  }

  public function filter_after_play_added(Play $play): Play {
    if ($this->should_generate_play_image) {
      $this->product_integration->generate_images($play);
    }
    return $play;
  }
}
