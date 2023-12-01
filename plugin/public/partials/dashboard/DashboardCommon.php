<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

class DashboardCommon {

  /**
   * Icon Buttons DO something (make post request, execute JS, etc.)
   */
  public static function icon_btn( $icon_path, $type = '', $id = '', $classes = '', $attributes = '' ) {
    ob_start();
    ?>
    <button <?php echo ! empty( $type ) ? "type=$type" : '' ?> <?php echo ! empty( $id ) ? "id=$id" : '' ?>
      class="<?php echo $classes ?> tw-h-40 tw-w-40 tw-p-8 tw-bg-white/15 tw-border-none tw-text-white tw-flex tw-flex-col tw-items-center tw-justify-center tw-rounded-8 hover:tw-cursor-pointer hover:tw-bg-white hover:tw-text-black"
      <?php echo $attributes ?>>
      <?php echo file_get_contents( WPBB_PLUGIN_DIR . '/Public/assets/icons/' . $icon_path ); ?>
    </button>
    <?php
    return ob_get_clean();
  }

  /**
   * Icon Links GO somewhere. (To another page, etc.)
   */
  public static function icon_link( $icon_path, $endpoint ) {
    ob_start();
    ?>
    <a
      class="tw-h-40 tw-w-40 tw-p-8 tw-bg-white/15 tw-border-none tw-text-white tw-flex tw-flex-col tw-rounded-8 tw-items-center tw-justify-center hover:tw-cursor-pointer hover:tw-bg-white hover:tw-text-black"
      href="<?php echo esc_url( $endpoint ) ?>">
      <?php echo file_get_contents( WPBB_PLUGIN_DIR . '/Public/assets/icons/' . $icon_path ); ?>
    </a>
    <?php
    return ob_get_clean();
  }

  /**
   * This button sends a POST request to delete the template
   */
  public static function delete_post_btn( $endpoint, $post_id, $post_id_field, $nonce_action, $nonce_name ) {
    ob_start();
    ?>
    <form method="post" action="<?php echo esc_url( $endpoint ) ?>">
      <input type="hidden" name="<?php echo $post_id_field ?>" value="<?php echo esc_attr( $post_id ) ?>">
      <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
      <?php echo self::icon_btn( 'trash.svg', 'submit' ); ?>
    </form>
    <?php
    return ob_get_clean();
  }

  /**
   * This button goes to the Play Bracket page
   */
  public static function add_to_apparel_btn( $endpoint ) {
    ob_start();
    ?>
    <a
      class="wpbb-add-apparel-btn tw-border tw-border-solid tw-border-transparent tw-bg-clip-padding tw-px-16 tw-py-12 tw-flex tw-items-center tw-justify-center tw-gap-10 tw-items-center tw-rounded-8 hover:tw-cursor-pointer tw-leading-[1.15] tw-h-full tw-bg-dd-blue/80 hover:tw-bg-transparent hover:tw-text-dd-blue"
      href="<?php echo esc_url( $endpoint ) ?>">
      <?php echo file_get_contents( WPBB_PLUGIN_DIR . 'Public/assets/icons/plus.svg' ); ?>
      <span class="tw-font-700">Add to Apparel</span>
    </a>
    <?php
    return gradient_border_wrap( ob_get_clean(), array( 'wpbb-add-apparel-gradient-border', 'tw-rounded-8' ) );
  }

  /**
   * This button sends a POST request to delete the tournament
   */
  public static function delete_tournament_btn( $endpoint, $post_id ) {
    ob_start();
    ?>
    <form method="post" action="<?php echo esc_url( $endpoint ) ?>">
      <input type="hidden" name="delete_tournament_id" value="<?php echo esc_attr( $post_id ) ?>">
      <?php wp_nonce_field( 'delete_tournament_action', 'delete_tournament_nonce' ); ?>
      <?php echo self::icon_btn( 'trash.svg', 'submit' ); ?>
      <!-- <button type="submit" class="tw-h-40 tw-w-40 tw-p-8 tw-bg-white/15 tw-border-none tw-text-white tw-flex tw-flex-col tw-rounded-8 tw-items-center tw-justify-center hover:tw-cursor-pointer hover:tw-bg-white hover:tw-text-black">
			<?php echo file_get_contents( WPBB_PLUGIN_DIR . 'Public/assets/icons/trash.svg' ); ?>
		</button> -->
    </form>
    <?php
    return ob_get_clean();
  }

  public static function restore_tournament_btn( $endpoint, $post_id ) {
    ob_start();
    ?>
    <form method="post" action="<?php echo esc_url( $endpoint ) ?>">
      <input type="hidden" name="restore_tournament_id" value="<?php echo esc_attr( $post_id ) ?>">
      <?php wp_nonce_field( 'restore_tournament_action', 'restore_tournament_nonce' ); ?>
      <?php echo self::icon_btn( 'trash.svg', 'submit' ); ?>
      <!-- <button type="submit" class="tw-h-40 tw-w-40 tw-p-8 tw-bg-white/15 tw-border-none tw-text-white tw-flex tw-flex-col tw-rounded-8 tw-items-center tw-justify-center hover:tw-cursor-pointer hover:tw-bg-white hover:tw-text-black">
			<?php echo file_get_contents( WPBB_PLUGIN_DIR . 'Public/assets/icons/trash.svg' ); ?>
		</button> -->
    </form>
    <?php
    return ob_get_clean();
  }
}
