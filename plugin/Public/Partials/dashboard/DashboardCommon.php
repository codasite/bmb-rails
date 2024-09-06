<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

use WStrategies\BMB\Public\Partials\shared\PartialsCommon;

class DashboardCommon {
  private static $icon_btn_class = [
    'wpbb-icon-btn',
    'tw-p-0',
    'tw-bg-white/15',
    'tw-border-none',
    'tw-text-white',
    'tw-flex',
    'tw-items-center',
    'tw-justify-center',
    'tw-rounded-8',
    'hover:tw-cursor-pointer',
    'hover:tw-bg-white',
    'hover:tw-text-black',
  ];
  /**
   * Icon Buttons DO something (make post request, execute JS, etc.)
   */
  public static function icon_btn(
    $label,
    $icon_path,
    $id = '',
    $classes = [],
    $data = []
  ): false|string {
    $classes = implode(' ', array_merge(self::$icon_btn_class, $classes));
    $data_attributes = self::build_data_attributes($data);
    ob_start();
    ?>
    <button <?php echo !empty($id) ? "id=$id" : ''; ?>
      class="<?php echo $classes; ?>"
      <?php echo $data_attributes; ?>>
      <?php echo self::build_icon_btn_contents($icon_path, $label); ?>
    </button>
    <?php return ob_get_clean();
  }

  /**
   * Icon Links GO somewhere. (To another page, etc.)
   */
  public static function icon_link(
    $label,
    $icon_path,
    $endpoint,
    $id = '',
    $classes = [],
    $data = []
  ): false|string {
    $classes = implode(' ', array_merge(self::$icon_btn_class, $classes));
    $data_attributes = self::build_data_attributes($data);
    ob_start();
    ?>
    <a
      <?php echo !empty($id) ? "id=$id" : ''; ?>
      href="<?php echo esc_url($endpoint); ?>"
      class="<?php echo $classes; ?>"
      <?php echo $data_attributes; ?>>
      <?php echo self::build_icon_btn_contents($icon_path, $label); ?>
    </a>
    <?php return ob_get_clean();
  }

  private static function build_icon_btn_contents($icon_path, $label = '') {
    ob_start(); ?>
      <div class="tw-h-40 tw-w-40 tw-p-8 tw-flex tw-flex-col tw-items-center tw-justify-center">
      <?php echo PartialsCommon::icon($icon_path); ?>
      </div>
      <?php if (!empty($label)) :?>
      <div class="wpbb-icon-btn-label">
        <span class="tw-whitespace-nowrap tw-uppercase tw-font-500 tw-font-sans tw-pr-8"><?php echo $label; ?></span>
      </div>
      <?php endif; ?>
      <?php return ob_get_clean();
  }

  private static function build_data_attributes(array $data): string {
    $data_attributes = '';
    foreach ($data as $key => $value) {
      $data_attributes .= "data-$key='$value' ";
    }
    return $data_attributes;
  }

  /**
   * This button goes to the Play Bracket page
   */
  public static function add_to_apparel_btn($endpoint): false|string {
    ob_start(); ?>
    <a
      class="tw-text-white tw-border tw-border-solid tw-border-transparent tw-bg-clip-padding tw-px-16 tw-py-12 tw-flex tw-items-center tw-justify-center tw-gap-10 tw-rounded-8 hover:tw-cursor-pointer tw-leading-[1.15] tw-h-full tw-bg-dd-blue/80 hover:tw-bg-transparent hover:tw-text-dd-blue"
      href="<?php echo esc_url($endpoint); ?>">
      <?php echo PartialsCommon::icon('plus'); ?>
      <span class="tw-font-700">Add to Apparel</span>
    </a>
    <?php return PartialsCommon::gradient_border_wrap(ob_get_clean(), [
      'wpbb-add-apparel-gradient-border',
      'tw-rounded-8',
    ]);
  }
}
