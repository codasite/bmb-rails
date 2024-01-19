<?php

namespace WStrategies\BMB\Public\Partials\shared;

class PagedStatusFilterButtons {

	public static function private_filter_button(string $url, bool $active): string {
		return self::filter_button(
			'Private',
			$url,
			$active,
			'blue',
			true
		);
	}

	public static function upcoming_filter_button(string $url, bool $active): string {
		return self::filter_button(
			'Upcoming',
			$url,
			$active,
			'yellow',
			true
		);

	}

	public static function live_filter_button(string $url, bool $active): string {
		return self::filter_button(
			'Live',
			$url,
			$active,
			'green',
			true
		);
	}

	public static function closed_filter_button(string $url, bool $active): string {
		return self::filter_button(
			'Closed',
			$url,
			$active,
			'white',
			true
		);
	}


  public static function filter_button( $label, $endpoint, $active = false, $color = 'white', $showCircle = false): false|string {
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
      'hover:tw-text-dd-blue',
      ...match ($color) {
        'green' => ['tw-text-green', 'tw-bg-green/15', 'hover:tw-bg-green', 'hover:tw-border-green'],
        'yellow' => ['tw-text-yellow', 'tw-bg-yellow/15', 'hover:tw-bg-yellow', 'hover:tw-border-yellow'],
        'blue' => ['tw-text-blue', 'tw-bg-blue/15', 'hover:tw-bg-blue', 'hover:tw-text-white', 'hover:tw-border-blue'],
        default => ['tw-text-white', 'tw-border-white', 'tw-bg-white/15', 'hover:tw-bg-white'],
      },
    ];

    $active_cls   = [
      ...match ($color) {
        'green' => ['tw-text-black', 'tw-bg-green', 'hover:tw-bg-green'],
        'yellow' => ['tw-text-black', 'tw-bg-yellow', 'hover:tw-bg-yellow'],
        'blue' => ['tw-text-white', 'tw-bg-blue', 'hover:tw-bg-blue'],
        default => ['tw-text-black', 'tw-bg-white', 'hover:tw-bg-white'],
      }
    ];

    $cls_list = array_merge( $base_cls, $active ? $active_cls : $inactive_cls );
    ob_start();
    ?>
    <a class="<?php echo implode( ' ', $cls_list ) ?>" href="<?php echo esc_url( $endpoint ) ?>">
      <?php if ( $showCircle ) : ?>
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="6" cy="6" r="6" fill="currentcolor"/>
  </svg>
      <?php endif; ?>
      <?php echo esc_html( $label ) ?>
    </a>
    <?php
    return ob_get_clean();
  }

}