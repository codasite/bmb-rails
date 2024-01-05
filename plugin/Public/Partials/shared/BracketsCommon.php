<?php

namespace WStrategies\BMB\Public\Partials\shared;

use WP_Query;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\NotificationType;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\NotificationRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class BracketsCommon {

  public static function sort_button( $label, $endpoint, $active = false ) {
    $base_cls = [
      'tw-flex',
      'tw-items-center',
      'tw-justify-center',
      'tw-text-16',
      'tw-font-500',
      'tw-rounded-8',
      'tw-py-8',
      'tw-px-16',
    ];

    $inactive_cls = [
      'tw-border',
      'tw-border-solid',
      'tw-border-white/50',
      'hover:tw-bg-white',
      'hover:tw-text-dark-blue',
    ];
    $active_cls   = [
      'tw-bg-white',
      '!tw-text-dark-blue',
    ];

    $cls_list = array_merge( $base_cls, $active ? $active_cls : $inactive_cls );
    ob_start();
    ?>
    <a class="<?php echo implode( ' ', $cls_list ) ?>" href="<?php echo esc_url( $endpoint ) ?>">
      <?php echo esc_html( $label ) ?>
    </a>
    <?php
    return ob_get_clean();
  }

  public static function bracket_tag( $label, $color, $filled = true ) {
    $filled_path = WPBB_PLUGIN_DIR . 'Public/assets/icons/ellipse.svg';
    $empty_path  = WPBB_PLUGIN_DIR . 'Public/assets/icons/ellipse_empty.svg';
    ob_start();
    ?>
    <div class="tw-text-<?php echo $color ?> tw-bg-<?php echo $color; ?>/15 tw-border tw-border-solid tw-px-8 tw-py-4 tw-flex tw-gap-4 tw-items-center tw-rounded-8">
      <?php echo $filled ? file_get_contents( $filled_path ) : file_get_contents( ( $empty_path ) ); ?>
      <span class="tw-font-500 tw-text-12"><?php echo $label ?></span>
    </div>
    <?php
    return ob_get_clean();
  }

  public static function upcoming_bracket_tag() {
    return self::bracket_tag( 'Upcoming', 'yellow' );
  }

  public static function live_bracket_tag() {
    return self::bracket_tag( 'Live', 'green' );
  }

  public static function completed_bracket_tag() {
    return self::bracket_tag( 'Complete', 'yellow' );
  }

  public static function scored_bracket_tag() {
    return self::bracket_tag( 'Scored', 'yellow' );
  }

  public static function archived_bracket_tag() {
    return self::bracket_tag( 'Archive', 'white/50', false );
  }

  public static function private_bracket_tag() {
    return self::bracket_tag( 'Private', 'blue', false );
  }

  public static function paid_bracket_tag() {
    ob_start();
    ?>
    <div class="tw-text-white tw-bg-blue tw-px-8 tw-py-4 tw-flex tw-items-center tw-rounded-8">
      <?php echo PartialsCommon::icon('currency_dollar') ?>
    </div>
    <?php
    return ob_get_clean();
  }

  public static function get_bracket_tag( $status ) {
    switch ( $status ) {
      case 'publish':
        return self::live_bracket_tag();
      case 'private':
        return self::private_bracket_tag();
      case 'upcoming':
        return self::upcoming_bracket_tag();
      case 'score':
        return self::scored_bracket_tag();
      case 'complete':
        return self::completed_bracket_tag();
      case 'archive':
        return self::archived_bracket_tag();
      default:
        return '';
    }
  }

  public static function base_btn($content, $extra_cls = [], $attributes = []) {
    $base_cls = array( 'tw-cursor-pointer', 'tw-uppercase', 'tw-font-sans' );
    $styles = array_merge( $extra_cls, $base_cls );
    ob_start();
    ?>
    <button class="<?php echo implode( ' ', $styles) ?>" <?php echo HtmlUtils::mapArrayToAttributes($attributes);?>>
      <?php echo $content ?>
    </button>
    <?php
    return ob_get_clean();
  }

  public static function base_link_btn($content, $href, $extra_cls = []) {
    $base_cls = array( 'tw-cursor-pointer', 'tw-uppercase', 'tw-font-sans' );
    $styles = array_merge( $extra_cls, $base_cls );
    ob_start();
    ?>
    <a class="<?php echo implode( ' ', $styles) ?>" href="<?php echo esc_url( $href ) ?>">
      <?php echo $content ?>
    </a>
    <?php
    return ob_get_clean();
  }

  public static function red_gradient_btn($content, $extra_cls = [], $attributes = []) {
    $styles = array( 'wpbb-bg-gradient-red', 'tw-p-1', 'tw-rounded-8', 'tw-border-none' );
    $styles = array_merge( $extra_cls, $styles );
    $content = self::red_gradient_content($content);
    return self::base_btn($content, $styles, $attributes);
  }

  public static function red_gradient_link($content, $href, $extra_cls = []) {
    $styles = array( 'wpbb-bg-gradient-red', 'tw-p-1', 'tw-rounded-8', 'tw-border-none' );
    $styles = array_merge( $extra_cls, $styles );
    $content = self::red_gradient_content($content);
    return self::base_link_btn($content, $href, $styles);
  }
  
  public static function red_gradient_content($content, $extra_cls = []) {
    $styles = array( 'tw-flex', 'tw-justify-center', 'tw-items-center', 'tw-text-white', 'tw-rounded-8', 'tw-px-16', 'tw-py-12', 'tw-font-700', 'tw-text-16', 'tw-whitespace-nowrap', 'tw-bg-dd-blue/80', 'hover:tw-bg-transparent', 'hover:tw-text-dd-blue' );
    $styles = array_merge( $extra_cls, $styles );

    ob_start();
    ?>
      <div class="<?php echo implode( ' ', $styles) ?>">
        <?php echo $content ?>
      </div>
    <?php
    $content = ob_get_clean();
    return $content;
  }

  /**
   * This button goes to the Play Bracket page
   */
  public static function play_bracket_btn( $endpoint, $label = 'Play Bracket' ) {
    ob_start();
    ?>
    <a
      class="tw-border-green tw-border-solid tw-border tw-bg-green/15 hover:tw-bg-green hover:tw-text-dd-blue tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8 tw-text-white"
      href="<?php echo esc_url( $endpoint ) ?>">
      <?php echo file_get_contents( WPBB_PLUGIN_DIR . 'Public/assets/icons/play.svg' ); ?>
      <span class="tw-font-700"><?php echo $label ?></span>
    </a>
    <?php
    return ob_get_clean();
  }

  public static function view_bracket_button( $bracket ) {
    $bracket_play_link = get_permalink( $bracket->id ) . '/play';
    ob_start();
    ?>
    <a
      class="tw-border-green tw-border-solid tw-border tw-bg-green/15 hover:tw-bg-green hover:tw-text-dd-blue tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8 tw-text-white"
      href="<?php echo esc_url( $bracket_play_link ) ?>">
      <span class="tw-font-700">View bracket</span>
    </a>
    <?php
    return ob_get_clean();
  }

  public static function enable_upcoming_notification_btn(Bracket $bracket) {
    $label = 'Notify Me';
    ob_start();
    ?> 
    <div class="tw-flex tw-justify-center tw-items-center tw-gap-10">
      <?php echo PartialsCommon::icon( 'bell' ); ?>
      <span><?php echo esc_html( $label ) ?></span>
    </div>
    <?php
    $content = ob_get_clean();
    return self::red_gradient_btn($content, ['wpbb-enable-upcoming-notification-button'], ['data-bracket-id' => $bracket->id]);
  }

  public static function disable_upcoming_notification_btn(int $notification_id) {
    $btn_styles = array( 'wpbb-disable-upcoming-notification-button', 'tw-bg-white/30', 'tw-text-white', 'tw-border-solid', 'tw-border-1', 'tw-border-white', 'tw-rounded-8', 'tw-px-16', 'tw-py-12', 'tw-font-700', 'tw-text-16', 'hover:tw-bg-white', 'hover:tw-text-black');
    $label = 'Notifying';
    ob_start();
    ?> 
    <div class="tw-flex tw-justify-center tw-items-center tw-gap-10">
      <?php echo PartialsCommon::icon( 'bell_ringing' ); ?>
      <span><?php echo esc_html( $label ) ?></span>
    </div>
    <?php
    $content = ob_get_clean();
    return self::base_btn($content, $btn_styles, ['data-notification-id' => $notification_id]);
  }

  /**
   * This button goes to the Leaderboard page
   */
  public static function view_leaderboard_btn( $endpoint, $variant = 'primary', $label = 'View Leaderboard' ) {
    $final = false;

    ob_start();
    ?>
      <div class="tw-flex tw-justify-center tw-items-center tw-gap-10">
        <?php echo file_get_contents( WPBB_PLUGIN_DIR . 'Public/assets/icons/trend_up.svg' ); ?>
        <span class="tw-font-700 tw-text-16 tw-whitespace-nowrap"><?php echo esc_html( $label ) ?></span>
      </div>
    <?php
    $content = ob_get_clean();

    if ( $variant === 'final' ) {
      return self::red_gradient_link($content, $endpoint);
    }

    $base_cls = array( 'tw-flex', 'tw-justify-center', 'tw-items-center', 'tw-text-white', 'tw-rounded-8', 'tw-border', 'tw-border-solid', 'tw-px-16', 'tw-py-12' );

    $cls_list = array(
      'primary' => array_merge( $base_cls, array( 'tw-border-white/50', 'tw-bg-white/15', 'tw-gap-10', 'tw-px-16', 'tw-py-12', 'hover:tw-bg-white', 'hover:tw-text-black' ) ),
      'compact' => array_merge( $base_cls, array( 'tw-border-white/50', 'tw-bg-white/15', 'tw-gap-4', 'sm:tw-px-8', 'sm:tw-py-4', 'hover:tw-bg-white', 'hover:tw-text-black' ) ),
    );

    ob_start();
    ?>
    <a class="<?php echo implode( ' ', $cls_list[ $variant ] ) ?>" href="<?php echo esc_url( $endpoint ) ?>">
      <?php echo $content ?>
    </a>
    <?php
    $btn = ob_get_clean();

    return $btn;
  }

  public static function bracket_sort_buttons() {
    $all_endpoint      = get_permalink();
    $status            = get_query_var( 'status' );
    $live_endpoint     = add_query_arg( 'status', PartialsContants::LIVE_STATUS, $all_endpoint );
    $upcoming_endpoint = add_query_arg( 'status', PartialsContants::UPCOMING_STATUS, $all_endpoint );
    $scored_endpoint   = add_query_arg( 'status', PartialsContants::SCORED_STATUS, $all_endpoint );
    ob_start();
    ?>
    <?php echo self::sort_button( 'All', $all_endpoint, ! ( $status ) ); ?>
    <?php echo self::sort_button( 'Live', $live_endpoint, $status === PartialsContants::LIVE_STATUS ); ?>
    <?php echo self::sort_button( 'Upcoming', $upcoming_endpoint, $status === PartialsContants::UPCOMING_STATUS ); ?>
    <?php echo self::sort_button( 'Scored', $scored_endpoint, $status === PartialsContants::SCORED_STATUS ); ?>
    <?php
    return ob_get_clean();
  }

  public static function public_bracket_active_buttons( Bracket $bracket ) {
    $bracket_play_link = get_permalink( $bracket->id ) . '/play';
    $leaderboard_link  = get_permalink( $bracket->id ) . '/leaderboard';
    ob_start();
    ?>
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16">
      <?php echo self::play_bracket_btn( $bracket_play_link ); ?>
      <?php echo self::view_leaderboard_btn( $leaderboard_link ); ?>
      <?php echo self::bracket_chat_btn( $bracket->id ); ?>
    </div>
    <?php
    return ob_get_clean();
  }

  public static function public_bracket_upcoming_buttons( Bracket $bracket ) {
    ob_start();
    ?>
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16">
      <?php echo self::upcoming_notification_btn( $bracket ); ?>
    </div>
    <?php
    return ob_get_clean();
  }

  public static function upcoming_notification_btn(Bracket $bracket) {
    $notification_repo = new NotificationRepo();
    $notification_id = $notification_repo->current_user_notification_id( $bracket->id, NotificationType::BRACKET_UPCOMING);
    if ($notification_id) {
      return self::disable_upcoming_notification_btn( $notification_id );
    } else {
      return self::enable_upcoming_notification_btn( $bracket );
    }
  }

  public static function public_bracket_completed_buttons( Bracket $bracket ) {
    $leaderboard_link = get_permalink( $bracket->id ) . '/leaderboard';

    ob_start();
    ?>
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16">
      <!-- This goes to the Leaderboard page -->
      <?php echo self::view_leaderboard_btn( $leaderboard_link, 'final' ); ?>
      <?php echo self::bracket_chat_btn( $bracket->id ); ?>
    </div>
    <?php
    return ob_get_clean();
  }

  public static function public_bracket_list_item( Bracket $bracket) {
    $name = $bracket->title;
    $num_teams = $bracket->num_teams;
    $num_plays = $bracket->num_plays;
    $completed = $bracket->status === 'complete';
    $status = $bracket->status;
    $bracket_tag = self::get_bracket_tag( $status );
    $bracket_buttons = self::public_bracket_active_buttons( $bracket );
    if ( $status === 'upcoming' ) {
      $bracket_buttons = self::public_bracket_upcoming_buttons( $bracket );
    } else if ( $status === 'complete' ) {
      $bracket_buttons = self::public_bracket_completed_buttons( $bracket );
    }

    $bracket_product_utils = new BracketProductUtils();
    $is_paid = $bracket_product_utils->has_bracket_fee($bracket->id);
    ob_start();
    ?>
    <div class="tw-border-2 tw-border-solid tw-border-<?php echo $completed ? 'white/15' : 'blue' ?> tw-bg-dd-blue tw-flex tw-flex-col tw-gap-10 tw-p-30 tw-rounded-16">
      <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between sm:tw-items-center tw-gap-8">
        <div class="tw-flex tw-gap-8 tw-items-center">
          <?php echo $is_paid ? self::paid_bracket_tag() : ''?>
          <span class="tw-font-500 tw-text-12"><?php echo esc_html( $num_teams ) ?>-Team Bracket</span>
        </div>
        <div class="tw-flex tw-gap-4 tw-items-center">
          <?php echo $bracket_tag ?>
          <?php echo file_get_contents( WPBB_PLUGIN_DIR . 'Public/assets/icons/bar_chart.svg' ); ?>
          <span class="tw-font-500 tw-text-20 tw-text-white"><?php echo esc_html( $num_plays ) ?></span>
          <span class="tw-font-500 tw-text-20 tw-text-white/50">Plays</span>
        </div>
      </div>
      <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-gap-15 md:tw-justify-start sm:tw-items-center">
        <h2 class="tw-text-white tw-font-700 tw-text-20 sm:tw-text-30"><?php echo esc_html( $name ) ?></h2>
      </div>
      <div class="tw-mt-10">
        <?php echo $bracket_buttons; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  public static function public_bracket_list( $opts = [] ) {
    $tags      = $opts['tags'] ?? [];
    $author_id = $opts['author'] ?? null;

    $bracket_repo = new BracketRepo();

    $paged         = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
    $status_filter = get_query_var( 'status' );

    if ( empty( $status_filter ) ) {
      $status_filter = 'all';
    }

    $all_statuses  = [ 'publish', 'score', 'complete', PartialsContants::UPCOMING_STATUS ];
    $active_status = [ 'publish' ];
    $scored_status = [ 'score', 'complete' ];

    if ( $status_filter === 'all' ) {
      $status_query = $all_statuses;
    } else if ( $status_filter === PartialsContants::LIVE_STATUS ) {
      $status_query = $active_status;
    } else if ( $status_filter === PartialsContants::UPCOMING_STATUS ) {
      $status_query = [ PartialsContants::UPCOMING_STATUS ];
    } else if ( $status_filter === 'scored' ) {
      $status_query = $scored_status;
    } else {
      $status_query = $all_statuses;
    }


    $the_query = new WP_Query( [
      'post_type'      => Bracket::get_post_type(),
      'tag_slug__and'  => $tags,
      'posts_per_page' => 8,
      'paged'          => $paged,
      'post_status'    => $status_query,
      'order'          => 'DESC',
      'author'         => $author_id,
    ] );

    $num_pages = $the_query->max_num_pages;

    $brackets = $bracket_repo->get_all( $the_query );

    ob_start();
    ?>
    <div class="tw-flex tw-flex-col tw-gap-15">
      <?php foreach ( $brackets as $bracket ) : ?>
        <?php echo self::public_bracket_list_item( $bracket); ?>
      <?php endforeach; ?>
    </div>
    <?php PaginationWidget::pagination( $paged, $num_pages ); ?>
    <?php
    return ob_get_clean();
  }

  public static function bracket_chat_btn( $bracket_id ) {
    if ( ! comments_open( $bracket_id ) ) {
      return '';
    }
    $endpoint = get_permalink( $bracket_id ) . 'chat';
    $label    = 'Chatter';
    $disabled = ! current_user_can( 'wpbb_view_bracket_chat', $bracket_id );
    $base_cls = array(
      'tw-flex',
      'tw-justify-center',
      'tw-items-center',
      'tw-rounded-8',
      'tw-border',
      'tw-border-solid',
      'tw-px-16',
      'tw-py-12',
      'tw-gap-10',
      'tw-px-16',
      'tw-py-12',
    );

    $active_styles = [
      'tw-text-white',
      'tw-border-white/50',
      'tw-bg-white/15',
      'hover:tw-bg-white',
      'hover:tw-text-black'
    ];

    $disabled_styles = [
      '!tw-text-white/20',
      'tw-border-white/20',
      'tw-bg-transparent',
      'tw-pointer-events-none',
    ];

    // merge base styles with active or disabled styles
    $styles   = $disabled ? $disabled_styles : $active_styles;
    $cls_list = array_merge( $base_cls, $styles );

    ob_start();
    ?>
    <a class="<?php echo implode( ' ', $cls_list ) ?>" href="<?php echo esc_url( $endpoint ) ?>">
      <?php echo PartialsCommon::icon( 'chat' ); ?>
      <span class="tw-font-700 tw-text-16"><?php echo esc_html( $label ) ?></span>
    </a>
    <?php
    $btn = ob_get_clean();

    return $btn;
  }
}
