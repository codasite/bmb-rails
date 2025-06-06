<?php

namespace WStrategies\BMB\Public\Partials\BracketBoard;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Public\Partials\shared\BracketsCommon;

class BracketBoardPage {
  private BracketRepo $bracket_repo;

  public function __construct() {
    $this->bracket_repo = new BracketRepo();
  }

  public function render(): string {
    ob_start(); ?>
        <div class="wpbb-bracket-board">
            <div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
                <h1 class="tw-text-4xl tw-font-bold tw-mb-8 tw-text-white">Bracket Board</h1>
                
                <!-- Filter buttons -->
                <div class="tw-mb-8">
                    <?php echo BracketsCommon::bracket_filter_buttons(); ?>
                </div>

                <!-- Bracket list -->
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
                    <?php echo BracketsCommon::public_bracket_list(); ?>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
  }
}
