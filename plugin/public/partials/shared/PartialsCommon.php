<?php

namespace WStrategies\BMB\Public\Partials\shared;

class PartialsCommon {

  /**
   * This button goes to the View Play page
   */
  public static function view_play_btn( $endpoint, $label = 'View Play' ) {
    ob_start();
    ?>
    <a
      class="tw-flex tw-items-center tw-justify-center tw-text-white tw-px-16 tw-py-12 tw-rounded-8 tw-border tw-border-solid tw-border-transparent tw-bg-dd-blue/80 hover:tw-bg-transparent hover:tw-text-dd-blue tw-bg-clip-padding tw-h-full tw-w-full"
      href="<?php echo esc_url( $endpoint ) ?>">
      <span class="tw-font-700 tw-whitespace-nowrap"><?php echo $label ?></span>
    </a>
    <?php
    // return ob_get_clean();
    return self::gradient_border_wrap( ob_get_clean(), array( 'wpbb-add-apparel-gradient-border', 'tw-rounded-8' ) );
  }

  /**
   * This is a utility wrapper for buttons that have a gradient border
   */
  public static function gradient_border_wrap( $content, $class_arr = array() ) {
    $classes = implode( ' ', $class_arr );
    ob_start();
    ?>
    <div class="<?php echo esc_attr( $classes ) ?>">
      <?php echo $content; ?>
    </div>
    <?php
    return ob_get_clean();
  }

  public static function icon( $icon_name ) {
    return file_get_contents( WPBB_PLUGIN_DIR . 'Public/assets/icons/' . $icon_name . '.svg' );
  }
}
