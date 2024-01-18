<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

class DashboardPage {
  private ManageBracketsPage $manage_brackets_page;
  private PlayHistoryPage $play_history_page;
  private TournamentsPage $tournaments_page;

  public function __construct($args = []) {
    $this->manage_brackets_page =
      $args['manage_brackets_page'] ?? new ManageBracketsPage();
    $this->play_history_page =
      $args['play_history_page'] ?? new PlayHistoryPage();
    $this->tournaments_page =
      $args['tournaments_page'] ?? new TournamentsPage();
  }

  public static function get_nav_link(
    $tab,
    $current_tab,
    $label,
    $icon
  ): false|string {
    $active = $tab === $current_tab;
    ob_start();
    ?>
    <a class="tw-flex tw-gap-10 tw-items-center tw-rounded-8 tw-p-16 tw-whitespace-nowrap hover:tw-bg-blue<?php echo $active
      ? ' tw-bg-blue'
      : ' tw-bg-white/10'; ?>"
       href="<?php echo get_permalink() .
         $tab; ?>" data-tab="<?php echo $tab; ?>">
      <?php echo file_get_contents(
        WPBB_PLUGIN_DIR . '/Public/assets/icons/' . $icon
      ); ?>
      <span><?php echo $label; ?></span>
    </a>
    <?php return ob_get_clean();
  }

  public static function get_account_settings_link(): string|bool {
    $account_page = get_page_by_path('my-account');
    $account_url = '';
    if ($account_page instanceof \WP_Post) {
      $page_id = $account_page->ID;
      $account_url = get_permalink($page_id);
    }
    ob_start();
    ?>
    <a class="tw-flex tw-gap-10 tw-items-center tw-rounded-8 tw-p-16 tw-whitespace-nowrap hover:tw-bg-blue tw-bg-white/10"
       href="<?php echo $account_url; ?>">
      <?php echo file_get_contents(
        WPBB_PLUGIN_DIR . '/Public/assets/icons/settings.svg'
      ); ?>
      <span>My Account</span>
    </a>
    <?php return ob_get_clean();
  }

  public function render($current_tab = null): false|string {
    $current_tab = $current_tab == null ? get_query_var('tab') : $current_tab;

    if (empty($current_tab)) {
      $current_tab = 'brackets';
    }

    $template = match ($current_tab) {
      'profile' => [ProfilePage::class, 'render'],
      'play-history' => [$this->play_history_page, 'render'],
      'tournaments' => [$this->tournaments_page, 'render'],
      default => [$this->manage_brackets_page, 'render'],
    };
    ob_start();
    ?>
    <div class="tw-bg-dd-blue tw-py-60">
      <div
        class="wpbb-dashboard tw-text-white tw-font-sans tw-flex tw-flex-col md:tw-flex-row tw-gap-60 md:tw-gap-30 lg:tw-gap-60 leading-none tw-uppercase tw-max-w-screen-xl tw-mx-auto tw-px-20">
        <nav>
          <h4 class="tw-text-white/50 tw-text-16 tw-font-500 tw-mb-15">Dashboard</h4>
          <ul class="tw-flex tw-flex-col tw-gap-15 tw-p-0 tw-m-0">
            <li class="tw-font-500 tw-text-20 tw-list-none"><?php echo self::get_nav_link(
              'profile',
              $current_tab,
              'Profile',
              '../../assets/icons/user.svg'
            ); ?></li>
            <li
              class="tw-font-500 tw-text-20 tw-list-none"><?php echo self::get_nav_link(
                'tournaments',
                $current_tab,
                'Tournaments',
                '../../assets/icons/signal.svg'
              ); ?></li>
            <li
              class="tw-font-500 tw-text-20 tw-list-none"><?php echo self::get_nav_link(
                'brackets',
                $current_tab,
                'Manage Brackets',
                '../../assets/icons/cursor-box.svg'
              ); ?></li>
            <li
              class="tw-font-500 tw-text-20 tw-list-none"><?php echo self::get_nav_link(
                'play-history',
                $current_tab,
                'My Play History',
                '../../assets/icons/clock.svg'
              ); ?></li>
            <li
              class="tw-font-500 tw-text-20 tw-list-none"><?php echo self::get_account_settings_link(); ?></li>
          </ul>
        </nav>
        <div class="tw-flex-grow">
          <?php echo $template(); ?>
        </div>
      </div>
    </div>
    <?php return ob_get_clean();
  }
}
