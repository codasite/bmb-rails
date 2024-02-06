<?php

namespace WStrategies\BMB\Public\Partials\shared;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Repository\UserProfileRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class BracketCards {

  public static function vip_switcher( $bracket_or_play ) {
    if ( $bracket_or_play instanceof Bracket ) {
      return self::vip_bracket_card( $bracket_or_play, [ 'show_profile_link' => true ] );
    } else if ( $bracket_or_play instanceof BracketPlay ) {
      return self::vip_play_card( $bracket_or_play, [ 'show_profile_link' => true ] );
    }
  }

  public static function vip_play_card( $play, $options = [] ): false|string {
    $show_profile_link = $options['show_profile_link'] ?? false;
    // Should probably handle this differently
    $profile_link = '';
    if ( $show_profile_link ) {
      $profile_repo = new UserProfileRepo();
      $profile      = $profile_repo->get_by_user( $play->author );
      if ( $profile->url ) {
        $profile_link = $profile->url;
      }
    }
    $product_utils = new BracketProductUtils();
    $title            = $play->title;
    $id               = $play->id;
    $thumbnail        = get_the_post_thumbnail_url( $id );
    $play_link        = get_permalink( $id ) . 'view';
    $leaderboard_link = get_permalink( $play->bracket_id ) . 'leaderboard';
    $show_paid_tag    = $product_utils->has_bracket_fee( $play->bracket_id );
    $buttons          = [
      PartialsCommon::view_play_btn( $play_link ),
      BracketsCommon::leaderboard_btn( $leaderboard_link, 'primary', 'Leaderboard' ),
      BracketsCommon::bracket_chat_btn( $play->bracket_id ),
    ];

    return self::vip_card( $title, $thumbnail, [ 
      'buttons' => $buttons, 
      'profile_link' => $profile_link, 
      'show_paid_tag' => $show_paid_tag 
    ] );
  }

  public static function vip_bracket_card( $bracket, $options = [] ): false|string {
    $show_profile_link = $options['show_profile_link'] ?? false;
    // Should probably handle this differently
    $profile_link = '';
    if ( $show_profile_link ) {
      $profile_repo = new UserProfileRepo();
      $profile      = $profile_repo->get_by_user( $bracket->author );
      if ( $profile->url ) {
        $profile_link = $profile->url;
      }
    }
    $product_utils = new BracketProductUtils();
    $title            = $bracket->title;
    $id               = $bracket->id;
    $thumbnail        = get_the_post_thumbnail_url( $id );
    $play_link        = get_permalink( $id ) . 'play';
    $leaderboard_link = get_permalink( $id ) . 'leaderboard';
    $buttons          = [];
    $bracket_tag      = '';
    $show_paid_tag    = $product_utils->has_bracket_fee( $bracket->id );
    switch ( $bracket->status ) {
      case 'upcoming':
        $buttons = [
          BracketsCommon::upcoming_notification_btn( $bracket ),
          BracketsCommon::preview_bracket_btn($bracket),
        ];
        $bracket_tag = BracketsCommon::upcoming_bracket_tag();
        break;
      case 'publish':
        $buttons = [
          BracketsCommon::play_bracket_btn( $bracket, ['label' => 'Play'] ),
          BracketsCommon::leaderboard_btn( $leaderboard_link, 'primary', 'Leaderboard' ),
          BracketsCommon::bracket_chat_btn( $id ),
        ];
        break;
      case 'score':
        $buttons = [
          BracketsCommon::view_results_btn( $bracket, ['label' => 'Results'] ),
          BracketsCommon::leaderboard_btn( $leaderboard_link, 'primary', 'Leaderboard' ),
          BracketsCommon::bracket_chat_btn( $id ),
        ];
        break;
      case 'complete':
        $buttons = [
          BracketsCommon::view_results_btn( $bracket, ['label' => 'Results'] ),
          BracketsCommon::leaderboard_btn( $leaderboard_link, 'final', 'Leaderboard' ),
          BracketsCommon::bracket_chat_btn( $id ),
        ];
        break;
    }

    return self::vip_card( $title, $thumbnail, [ 
      'buttons' => $buttons, 
      'profile_link' => $profile_link, 
      'tag' => $bracket_tag,
      'show_paid_tag' => $show_paid_tag
    ] );
  }

  public static function view_profile_btn( $link ): false|string {
    ob_start();
    ?>
    <a href="<?php echo esc_url( $link ) ?>"
       class="tw-flex tw-items-center tw-justify-center tw-gap-8 tw-text-white tw-px-16 tw-py-4 tw-rounded-8 tw-bg-dd-blue/60 hover:tw-bg-dd-blue">
      <span class="tw-text-14 tw-font-600">View Profile</span>
      <?php echo PartialsCommon::icon( 'arrow_up_right' ) ?>
    </a>
    <?php
    return ob_get_clean();
  }

  public static function vip_card( $title, $thumbnail, array $options = [] ): false|string {
    $buttons      = $options['buttons'] ?? [];
    $profile_link = $options['profile_link'] ?? '';
    $tag = $options['tag'] ?? '';
    $show_paid_tag = $options['show_paid_tag'] ?? false;
    ob_start();
    ?>
    <div class="tw-flex tw-flex-col">
      <div class="tw-relative tw-bg-center tw-bg-cover tw-bg-no-repeat tw-bg-white tw-rounded-t-16 tw-min-h-[324px] sm:tw-grow" style="background-image: url(<?php echo $thumbnail ?>)">
        <?php if ( $show_paid_tag ) : ?>
          <div class="tw-absolute tw-top-20 tw-left-20">
            <?php echo BracketsCommon::paid_bracket_tag() ?>
          </div>
        <?php endif; ?>
        <?php if ( $profile_link ) : ?>
          <div class="tw-absolute tw-top-20 tw-right-20">
            <?php echo self::view_profile_btn( $profile_link ) ?>
          </div>
        <?php endif; ?>
        <div
          class="tw-flex tw-flex-col tw-justify-end tw-flex-grow tw-gap-10 tw-px-30 tw-rounded-t-16 tw-bg-gradient-to-t tw-from-[#03073C] tw-to-[72%] tw-border-solid tw-border-white/20 tw-border-2 tw-border-y-none tw-h-full">
          <div class="tw-self-start">
            <?php echo $tag ?>
          </div>
          <h3 class="tw-text-30 tw-text-black"><?php echo esc_html( $title ) ?></h3>
        </div>
      </div>
      <div
        class="tw-flex tw-flex-col sm:tw-flex-row md:tw-flex-col lg:tw-flex-row tw-pt-20 tw-gap-10 tw-px-30 tw-pb-30 tw-bg-dd-blue tw-bg-gradient-to-r tw-from-[#03073C]/50 tw-to-50% tw-rounded-b-16 tw-border-solid tw-border-white/20 tw-border-2 tw-border-t-none">
        <?php
        foreach ( $buttons as $button ) {
          echo $button;
        }
        ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }
}
