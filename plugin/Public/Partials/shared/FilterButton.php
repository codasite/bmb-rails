<?php
namespace WStrategies\BMB\Public\Partials\shared;

use WStrategies\BMB\Includes\Service\TournamentFilter\TournamentFilterInterface;

class FilterButton {
	private TournamentFilterInterface $filter;
	private string $label;
	private string $color;
  private string $url;
	private bool $show_circle;
	private bool $fill_circle;

	public function __construct($args = []) {
		$this->filter = $args['tournament_filter'] ?? null;
		$this->label = $args['label'] ?? '';
		$this->color = $args['color'] ?? '';
    $this->url = $args['url'] ?? '';
		$this->show_circle = $args['show_circle'] ?? false;
		$this->fill_circle = $args['fill_circle'] ?? false;
	}

  public function get_filter(): TournamentFilterInterface {
    return $this->filter;
  }

  public function render(): false|string {
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
      ...match ($this->color) {
        'green' => ['tw-text-green', 'tw-bg-green/15', 'hover:tw-bg-green', 'hover:tw-border-green'],
        'yellow' => ['tw-text-yellow', 'tw-bg-yellow/15', 'hover:tw-bg-yellow', 'hover:tw-border-yellow'],
        'blue' => ['tw-text-blue', 'tw-bg-blue/15', 'hover:tw-bg-blue', 'hover:tw-text-white', 'hover:tw-border-blue'],
        default => ['tw-text-white', 'tw-border-white', 'tw-bg-white/15', 'hover:tw-bg-white'],
      },
    ];

    $active_cls   = [
      ...match ($this->color) {
        'green' => ['tw-text-black', 'tw-bg-green', 'hover:tw-bg-green'],
        'yellow' => ['tw-text-black', 'tw-bg-yellow', 'hover:tw-bg-yellow'],
        'blue' => ['tw-text-white', 'tw-bg-blue', 'hover:tw-bg-blue'],
        default => ['tw-text-black', 'tw-bg-white', 'hover:tw-bg-white'],
      }
    ];

    $cls_list = array_merge( $base_cls, $this->filter->is_active() ? $active_cls : $inactive_cls );
    ob_start();
    ?>
    <a class="<?php echo implode( ' ', $cls_list ) ?>" href="<?php echo esc_url( $this->url ) ?>">
      <?php if ( $this->show_circle ) : ?>
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="6" cy="6" r="6" fill="currentcolor"/>
  </svg>
      <?php endif; ?>
      <span>
        <?php echo esc_html( $this->label ) ?>
      </span>
      <?php if ( $this->filter) : ?>
        <span class="tw-text-12 tw-font-500 tw-ml-4">
          (<?php echo $this->filter->get_count(); ?>)
        </span>
      <?php endif; ?>
    </a>
    <?php
    return ob_get_clean();
  }
}