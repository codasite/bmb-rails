<?php

namespace WStrategies\BMB\Public\Partials\shared;

use WP_Query;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationSubscriptionRepo;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Features\MobileApp\RequestService;
use WStrategies\BMB\Features\MobileApp\MobileAppMetaQuery;
use WStrategies\BMB\Features\Bracket\Infrastructure\BracketQueryBuilder;
use WStrategies\BMB\Features\Bracket\Presentation\BracketListRenderer;
use WStrategies\BMB\Features\Bracket\Domain\BracketQueryTypes;

class BracketsCommon {
  public static function filter_button(
    $label,
    $endpoint,
    $active = false,
    $color = 'white',
    $showCircle = false
  ): false|string {
    $base_cls = [
      'tw-flex',
      'tw-items-center',
      'tw-gap-4',
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
      'hover:tw-text-black',
      ...match ($color) {
        'green' => ['tw-text-green', 'tw-bg-green/15', 'hover:tw-bg-green'],
        'yellow' => ['tw-text-yellow', 'tw-bg-yellow/15', 'hover:tw-bg-yellow'],
        'blue' => ['tw-text-blue', 'tw-bg-blue/15', 'hover:tw-bg-blue'],
        default => [
          'tw-text-white',
          'tw-border-white',
          'tw-bg-white/15',
          'hover:tw-bg-white',
        ],
      },
    ];

    $active_cls = [
      ...match ($color) {
        'green' => ['tw-text-black', 'tw-bg-green', 'hover:tw-bg-green'],
        'yellow' => ['tw-text-black', 'tw-bg-yellow', 'hover:tw-bg-yellow'],
        'blue' => ['tw-text-white', 'tw-bg-blue', 'hover:tw-bg-blue'],
        default => ['tw-text-black', 'tw-bg-white', 'hover:tw-bg-white'],
      },
    ];

    $cls_list = array_merge($base_cls, $active ? $active_cls : $inactive_cls);
    ob_start();
    ?>
    <a class="<?php echo implode(' ', $cls_list); ?>" href="<?php echo esc_url(
  $endpoint
); ?>">
      <?php if ($showCircle): ?>
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="6" cy="6" r="6" fill="currentcolor"/>
  </svg>
      <?php endif; ?>
      <?php echo esc_html($label); ?>
    </a>
    <?php return ob_get_clean();
  }

  /**
   * @param $label
   * @param 'yellow'|'green'|'white'|'blue'|'red' $color
   * @param $filled
   *
   * @return false|string
   */
  public static function bracket_tag(
    $label,
    string $color,
    $filled = true
  ): false|string {
    $filled_path = WPBB_PLUGIN_DIR . 'Public/assets/icons/ellipse.svg';
    $empty_path = WPBB_PLUGIN_DIR . 'Public/assets/icons/ellipse_empty.svg';
    $base_styles = [
      'tw-border',
      'tw-border-solid',
      'tw-px-8',
      'tw-py-4',
      'tw-flex',
      'tw-gap-4',
      'tw-items-center',
      'tw-rounded-8',
    ];
    $color_styles = match ($color) {
      'yellow' => ['tw-text-yellow', 'tw-bg-yellow/15'],
      'green' => ['tw-text-green', 'tw-bg-green/15'],
      'white' => ['tw-text-white', 'tw-bg-white/15'],
      'blue' => ['tw-text-blue', 'tw-bg-blue/15'],
      'red' => ['tw-text-red', 'tw-bg-red/15'],
    };
    ob_start();
    ?>
    <div class="<?php echo implode(
      ' ',
      array_merge($base_styles, $color_styles)
    ); ?>">
      <?php echo $filled
        ? file_get_contents($filled_path)
        : file_get_contents($empty_path); ?>
      <span class="tw-font-500 tw-text-12"><?php echo $label; ?></span>
    </div>
    <?php return ob_get_clean();
  }

  public static function voting_status_tag(Bracket $bracket): false|string {
    if (!$bracket->is_voting) {
      return '';
    }
    $round = $bracket->live_round_index + 1;
    if ($bracket->status === 'complete') {
      return self::bracket_tag('Complete', 'white');
    }
    return self::bracket_tag("Voting Round $round", 'green');
  }

  public static function upcoming_bracket_tag(): false|string {
    return self::bracket_tag('Upcoming', 'yellow');
  }

  public static function live_bracket_tag(): false|string {
    return self::bracket_tag('Live', 'green');
  }

  public static function completed_bracket_tag(): false|string {
    return self::bracket_tag('Complete', 'white');
  }

  public static function scored_bracket_tag(): false|string {
    return self::bracket_tag('In progress', 'white');
  }

  public static function private_bracket_tag(): false|string {
    return self::bracket_tag('Private', 'blue', false);
  }

  public static function paid_bracket_tag(): false|string {
    ob_start(); ?>
    <div class="tw-text-white tw-bg-blue tw-px-8 tw-py-4 tw-flex tw-items-center tw-rounded-8">
      <?php echo PartialsCommon::icon('currency_dollar'); ?>
    </div>
    <?php return ob_get_clean();
  }

  public static function get_bracket_status_tag($bracket): false|string {
    if ($bracket->is_voting) {
      return self::voting_status_tag($bracket);
    }
    switch ($bracket->status) {
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
      default:
        return '';
    }
  }

  public static function base_btn(
    $content,
    $extra_cls = [],
    $attributes = []
  ): false|string {
    $base_cls = ['tw-cursor-pointer', 'tw-uppercase', 'tw-font-sans'];
    $styles = array_merge($extra_cls, $base_cls);
    ob_start();
    ?>
    <button class="<?php echo implode(
      ' ',
      $styles
    ); ?>" <?php echo HtmlUtils::mapArrayToAttributes($attributes); ?>>
      <?php echo $content; ?>
    </button>
    <?php return ob_get_clean();
  }

  public static function base_link_btn(
    $content,
    $href,
    $extra_cls = []
  ): false|string {
    $base_cls = ['tw-cursor-pointer', 'tw-uppercase', 'tw-font-sans'];
    $styles = array_merge($extra_cls, $base_cls);
    ob_start();
    ?>
    <a class="<?php echo implode(' ', $styles); ?>" href="<?php echo esc_url(
  $href
); ?>">
      <?php echo $content; ?>
    </a>
    <?php return ob_get_clean();
  }

  public static function red_gradient_btn(
    $content,
    $extra_cls = [],
    $attributes = []
  ): false|string {
    $styles = [
      'wpbb-bg-gradient-red',
      'tw-p-1',
      'tw-rounded-8',
      'tw-border-none',
    ];
    $styles = array_merge($extra_cls, $styles);
    $content = self::red_gradient_content($content);
    return self::base_btn($content, $styles, $attributes);
  }

  public static function red_gradient_link(
    $content,
    $href,
    $extra_cls = []
  ): false|string {
    $styles = [
      'wpbb-bg-gradient-red',
      'tw-p-1',
      'tw-rounded-8',
      'tw-border-none',
    ];
    $styles = array_merge($extra_cls, $styles);
    $content = self::red_gradient_content($content);
    return self::base_link_btn($content, $href, $styles);
  }

  public static function red_gradient_content(
    $content,
    $extra_cls = []
  ): false|string {
    $styles = [
      'tw-flex',
      'tw-justify-center',
      'tw-items-center',
      'tw-text-white',
      'tw-rounded-8',
      'tw-px-16',
      'tw-py-12',
      'tw-font-700',
      'tw-text-16',
      'tw-whitespace-nowrap',
      'tw-bg-dd-blue/80',
      'hover:tw-bg-transparent',
      'hover:tw-text-dd-blue',
    ];
    $styles = array_merge($extra_cls, $styles);

    ob_start();
    ?>
      <div class="<?php echo implode(' ', $styles); ?>">
        <?php echo $content; ?>
      </div>
    <?php
    $content = ob_get_clean();
    return $content;
  }

  /**
   * This button goes to the Play Bracket page
   */
  public static function play_bracket_btn(
    Bracket $bracket,
    array $args = []
  ): false|string {
    $label = $args['label'] ?? 'Play Tournament';
    $endpoint = $bracket->url . 'play';
    /**
     * @var 'green'|'white'|'yellow' $color
     */
    $color = $args['color'] ?? 'green';
    $base_styles = [
      'tw-flex',
      'tw-justify-center',
      'tw-items-center',
      'tw-rounded-8',
      'tw-gap-10',
      'tw-px-16',
      'tw-py-12',
    ];
    $color_styles = match ($color) {
      'green' => [
        'tw-border',
        'tw-border-solid',
        'tw-border-green',
        'tw-bg-green/15',
        'hover:tw-bg-green',
        'hover:tw-text-dd-blue',
        'tw-text-white',
        'tw-font-700',
      ],
      'white' => ['tw-text-black', 'tw-bg-white', 'tw-font-500'],
      'yellow' => ['tw-text-black', 'tw-bg-yellow', 'tw-font-500'],
    };
    ob_start();
    ?>
    <a
      class="<?php echo implode(
        ' ',
        array_merge($base_styles, $color_styles)
      ); ?>"
      href="<?php echo esc_url($endpoint); ?>">
      <?php echo file_get_contents(
        WPBB_PLUGIN_DIR . 'Public/assets/icons/play.svg'
      ); ?>
      <span><?php echo $label; ?></span>
    </a>
    <?php return ob_get_clean();
  }

  public static function view_results_btn(
    Bracket $bracket,
    array $args = []
  ): false|string {
    $label = $args['label'] ?? 'View Results';
    /**
     * @var 'green'|'white'|'yellow' $color
     */
    $color = $args['color'] ?? 'green';
    $url = $bracket->url . '/results/view';
    $base_styles = [
      'tw-flex',
      'tw-justify-center',
      'tw-items-center',
      'tw-rounded-8',
      'tw-gap-10',
      'tw-px-16',
      'tw-py-12',
    ];
    $color_styles = match ($color) {
      'green' => [
        'tw-border',
        'tw-border-solid',
        'tw-border-green',
        'tw-bg-green/15',
        'hover:tw-bg-green',
        'hover:tw-text-dd-blue',
        'tw-text-white',
        'tw-font-700',
      ],
      'white' => ['tw-text-black', 'tw-bg-white', 'tw-font-500'],
      'yellow' => ['tw-text-black', 'tw-bg-yellow', 'tw-font-500'],
    };
    ob_start();
    ?>
    <a
      class="<?php echo implode(
        ' ',
        array_merge($base_styles, $color_styles)
      ); ?>"
      href="<?php echo esc_url($url); ?>">
      <?php echo PartialsCommon::icon('eye'); ?>
      <span><?= $label ?></span>
    </a>
    <?php return ob_get_clean();
  }

  public static function enable_upcoming_notification_btn(
    Bracket $bracket
  ): false|string {
    $label = 'Notify Me';
    ob_start();
    ?> 
    <div class="tw-flex tw-justify-center tw-items-center tw-gap-10">
      <?php echo PartialsCommon::icon('bell'); ?>
      <span><?php echo esc_html($label); ?></span>
    </div>
    <?php
    $content = ob_get_clean();
    return self::red_gradient_btn(
      $content,
      ['wpbb-enable-upcoming-notification-button'],
      ['data-bracket-id' => $bracket->id]
    );
  }

  public static function disable_upcoming_notification_btn(
    int $notification_id
  ): false|string {
    $btn_styles = [
      'wpbb-disable-upcoming-notification-button',
      'tw-bg-white/30',
      'tw-text-white',
      'tw-border-solid',
      'tw-border-1',
      'tw-border-white',
      'tw-rounded-8',
      'tw-px-16',
      'tw-py-12',
      'tw-font-700',
      'tw-text-16',
      'hover:tw-bg-white',
      'hover:tw-text-black',
    ];
    $label = 'Notifying';
    ob_start();
    ?> 
    <div class="tw-flex tw-justify-center tw-items-center tw-gap-10">
      <?php echo PartialsCommon::icon('bell_ringing'); ?>
      <span><?php echo esc_html($label); ?></span>
    </div>
    <?php
    $content = ob_get_clean();
    return self::base_btn($content, $btn_styles, [
      'data-notification-id' => $notification_id,
    ]);
  }

  /**
   * This button goes to the Leaderboard page
   */
  public static function leaderboard_btn(
    $endpoint,
    $variant = 'primary',
    $label = 'Leaderboard'
  ): false|string {
    $final = false;

    ob_start();
    ?>
      <div class="tw-flex tw-justify-center tw-items-center tw-gap-10">
        <?php echo file_get_contents(
          WPBB_PLUGIN_DIR . 'Public/assets/icons/trend_up.svg'
        ); ?>
        <span class="tw-font-700 tw-text-16 tw-whitespace-nowrap"><?php echo esc_html(
          $label
        ); ?></span>
      </div>
    <?php
    $content = ob_get_clean();

    if ($variant === 'final') {
      return self::red_gradient_link($content, $endpoint);
    }

    $base_cls = [
      'tw-flex',
      'tw-justify-center',
      'tw-items-center',
      'tw-text-white',
      'tw-rounded-8',
      'tw-border',
      'tw-border-solid',
      'tw-px-16',
      'tw-py-12',
    ];

    $cls_list = [
      'primary' => array_merge($base_cls, [
        'tw-border-white/50',
        'tw-bg-white/15',
        'tw-gap-10',
        'tw-px-16',
        'tw-py-12',
        'hover:tw-bg-white',
        'hover:tw-text-black',
      ]),
      'compact' => array_merge($base_cls, [
        'tw-border-white/50',
        'tw-bg-white/15',
        'tw-gap-4',
        'sm:tw-px-8',
        'sm:tw-py-4',
        'hover:tw-bg-white',
        'hover:tw-text-black',
      ]),
    ];

    ob_start();
    ?>
    <a class="<?php echo implode(
      ' ',
      $cls_list[$variant]
    ); ?>" href="<?php echo esc_url($endpoint); ?>">
      <?php echo $content; ?>
    </a>
    <?php
    $btn = ob_get_clean();

    return $btn;
  }

  public static function bracket_filter_buttons(): false|string {
    $all_endpoint = get_permalink();
    $status = get_query_var('status', PartialsContants::LIVE_STATUS);
    $live_endpoint = add_query_arg(
      'status',
      PartialsContants::LIVE_STATUS,
      $all_endpoint
    );
    $upcoming_endpoint = add_query_arg(
      'status',
      PartialsContants::UPCOMING_STATUS,
      $all_endpoint
    );
    $scored_endpoint = add_query_arg(
      'status',
      PartialsContants::SCORED_STATUS,
      $all_endpoint
    );
    ob_start();
    ?>
    <?php echo self::filter_button(
      'Live',
      $live_endpoint,
      $status === PartialsContants::LIVE_STATUS
    ); ?>
    <?php echo self::filter_button(
      'Upcoming',
      $upcoming_endpoint,
      $status === PartialsContants::UPCOMING_STATUS
    ); ?>
    <?php echo self::filter_button(
      'Scored',
      $scored_endpoint,
      $status === PartialsContants::SCORED_STATUS
    ); ?>
    <?php return ob_get_clean();
  }

  public static function public_bracket_active_buttons(
    Bracket $bracket
  ): false|string {
    $leaderboard_link = get_permalink($bracket->id) . '/leaderboard';
    ob_start();
    ?>
    <?php echo self::play_bracket_btn($bracket); ?>
    <?php echo self::leaderboard_btn($leaderboard_link); ?>
    <?php echo self::bracket_chat_btn($bracket->id); ?>
    <?php return ob_get_clean();
  }

  public static function public_bracket_upcoming_buttons(
    Bracket $bracket
  ): false|string {
    ob_start(); ?>
    <?php echo self::upcoming_notification_btn($bracket); ?>
    <?php echo self::preview_bracket_btn($bracket); ?>
    <?php return ob_get_clean();
  }

  public static function upcoming_notification_btn(
    Bracket $bracket
  ): false|string {
    $notification_sub_repo = new NotificationSubscriptionRepo();
    $notification_id = $notification_sub_repo->current_user_notification_id(
      $bracket->id,
      NotificationType::BRACKET_UPCOMING
    );
    if ($notification_id) {
      return self::disable_upcoming_notification_btn($notification_id);
    } else {
      return self::enable_upcoming_notification_btn($bracket);
    }
  }

  public static function preview_bracket_btn($bracket): false|string {
    $bracket_play_link = get_permalink($bracket->id) . '/play';
    ob_start();
    ?>
    <a
      class="tw-border-white/50 tw-border-solid tw-border tw-bg-white/15 hover:tw-bg-white hover:tw-text-black tw-px-16 tw-py-12 tw-flex tw-justify-center tw-gap-10 tw-items-center tw-rounded-8 tw-text-white"
      href="<?php echo esc_url($bracket_play_link); ?>">
      <?php echo PartialsCommon::icon('eye'); ?>
      <span class="tw-font-700">Preview</span>
    </a>
    <?php return ob_get_clean();
  }

  public static function public_bracket_completed_buttons(
    Bracket $bracket
  ): false|string {
    $leaderboard_link = get_permalink($bracket->id) . '/leaderboard';

    ob_start();
    ?>
    <?php echo self::leaderboard_btn($leaderboard_link, 'final'); ?>
    <?php echo self::bracket_chat_btn($bracket->id); ?>
    <?php return ob_get_clean();
  }

  public static function get_public_brackets($opts = []): array {
    $bracket_repo = new BracketRepo();
    $query_builder = new BracketQueryBuilder();

    $query_args = $query_builder->buildPublicBracketsQuery([
      'tags' => $opts['tags'] ?? [],
      'author' => $opts['author'] ?? null,
      'posts_per_page' => $opts['posts_per_page'] ?? 8,
      'paged' =>
        $opts['paged'] ?? get_query_var('paged')
          ? absint(get_query_var('paged'))
          : 1,
      'paged_status' =>
        $opts['status'] ??
        get_query_var('status', BracketQueryTypes::FILTER_LIVE),
      'posts_per_page' => $opts['posts_per_page'] ?? 8,
    ]);

    $the_query = new WP_Query($query_args);
    $brackets = $bracket_repo->get_all($the_query);

    return [
      'brackets' => $brackets,
      'num_pages' => $the_query->max_num_pages,
      'current_page' => $query_args['paged'],
    ];
  }

  public static function public_bracket_list($opts = []): false|string {
    $result = self::get_public_brackets($opts);
    $list_renderer = new BracketListRenderer();

    return $list_renderer->renderBracketList($result['brackets'], [
      'current_page' => $result['current_page'],
      'num_pages' => $result['num_pages'],
    ]);
  }

  public static function bracket_chat_btn($bracket_id): false|string {
    if (!comments_open($bracket_id)) {
      return '';
    }
    $endpoint = get_permalink($bracket_id) . 'chat';
    $label = 'Chatter';
    $disabled = !current_user_can('wpbb_view_bracket_chat', $bracket_id);
    $base_cls = [
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
    ];

    $active_styles = [
      'tw-text-white',
      'tw-border-white/50',
      'tw-bg-white/15',
      'hover:tw-bg-white',
      'hover:tw-text-black',
    ];

    $disabled_styles = [
      '!tw-text-white/20',
      'tw-border-white/20',
      'tw-bg-transparent',
      'tw-pointer-events-none',
    ];

    // merge base styles with active or disabled styles
    $styles = $disabled ? $disabled_styles : $active_styles;
    $cls_list = array_merge($base_cls, $styles);

    ob_start();
    ?>
    <a class="<?php echo implode(
      ' ',
      $cls_list
    ); ?>" href="<?php echo esc_url($endpoint); ?>">
      <?php echo PartialsCommon::icon('chat'); ?>
      <span class="tw-font-700 tw-text-16"><?php echo esc_html(
        $label
      ); ?></span>
    </a>
    <?php
    $btn = ob_get_clean();

    return $btn;
  }
}
