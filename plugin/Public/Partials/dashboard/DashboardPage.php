<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccountFactory;
use WStrategies\BMB\Public\Partials\shared\PartialsCommon;
use WStrategies\BMB\Public\Partials\TemplateInterface;

class DashboardPage implements TemplateInterface {
  private PlayHistoryPage $play_history_page;
  private TournamentsPage $tournaments_page;
  private StripeConnectedAccountFactory $account_factory;

  public function __construct($args = []) {
    $this->play_history_page =
      $args['play_history_page'] ?? new PlayHistoryPage();
    $this->tournaments_page =
      $args['tournaments_page'] ?? new TournamentsPage();
    $this->account_factory =
      $args['account_factory'] ?? new StripeConnectedAccountFactory();
  }

  public static function get_url(): string {
    return get_permalink(get_page_by_path('dashboard'));
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
    <a class="tw-text-white tw-flex tw-gap-10 tw-items-center tw-rounded-8 tw-p-16 tw-whitespace-nowrap hover:tw-bg-blue/90<?php echo $active
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
    <a class="tw-text-white tw-flex tw-gap-10 tw-items-center tw-rounded-8 tw-p-16 tw-whitespace-nowrap hover:tw-bg-blue tw-bg-white/10"
       href="<?php echo $account_url; ?>">
      <?php echo file_get_contents(
        WPBB_PLUGIN_DIR . '/Public/assets/icons/settings.svg'
      ); ?>
      <span>My Account</span>
    </a>
    <?php return ob_get_clean();
  }

  private function payments_button(): string|bool {
    $account = $this->account_factory->get_account_for_current_user();
    $classes = $account->account_id_exists()
      ? 'tw-bg-white/10 disabled:tw-bg-white/20'
      : 'tw-bg-red hover:tw-bg-red/90 disabled:tw-bg-red/80';
    ob_start();
    ?>
    <div></div>
    <button class="wpbb-payments-button tw-group tw-text-white tw-flex tw-gap-10 tw-items-center tw-rounded-8 tw-p-16 tw-whitespace-nowrap hover:tw-bg-blue tw-font-sans tw-uppercase tw-border-none tw-font-500 tw-w-full tw-cursor-pointer tw-leading-[1.6] disabled:tw-cursor-default <?php echo $classes; ?>">
      <span class="group-disabled:tw-hidden tw-flex tw-justify-center">
        <?php echo PartialsCommon::icon('card'); ?>
      </span>
      <span class="group-disabled:tw-flex tw-hidden tw-justify-center tw-animate-spin tw-w-[22px]">
        <?php echo PartialsCommon::icon('spinner'); ?>
      </span>
      <span class='tw-block'>
        <?php echo $account->account_id_exists()
          ? 'Payments'
          : 'Set up payments'; ?>
      </span>
    </button>
    <?php return ob_get_clean();
  }

  public function render($current_tab = null): false|string {
    $current_tab = $current_tab == null ? get_query_var('tab') : $current_tab;

    if (empty($current_tab)) {
      $current_tab = 'tournaments';
    }

    $template = match ($current_tab) {
      'profile' => [ProfilePage::class, 'render'],
      'play-history' => [$this->play_history_page, 'render'],
      default => [$this->tournaments_page, 'render'],
    };
    ob_start();
    ?>
    <div class="tw-bg-dd-blue tw-py-60">
      <div
        class="wpbb-dashboard tw-text-white tw-font-sans tw-flex tw-flex-col md:tw-flex-row tw-gap-60 md:tw-gap-30 lg:tw-gap-60 leading-none tw-uppercase tw-max-w-screen-xl tw-mx-auto tw-px-20">
        <nav id="wpbb-dashboard-nav">
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
                'play-history',
                $current_tab,
                'My Play History',
                '../../assets/icons/clock.svg'
              ); ?></li>
            <li
              class="tw-font-500 tw-text-20 tw-list-none"><?php echo self::get_account_settings_link(); ?></li>
              <?php if (current_user_can('wpbb_create_paid_bracket')): ?>
                <li
                  class="tw-font-500 tw-text-20 tw-list-none"><?php echo self::payments_button(); ?></li>
              <?php endif; ?>
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
