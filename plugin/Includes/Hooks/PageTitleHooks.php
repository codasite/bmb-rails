<?php
namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Public\Partials\dashboard\DashboardPage;

class PageTitleHooks implements HooksInterface {
  public function load(Loader $loader): void {
    $loader->add_filter(
      'pre_get_document_title',
      [$this, 'set_page_title'],
      999
    );
  }

  public function set_page_title($title): string {
    global $post;
    $post_slug = $post->post_name;
    $current_tab = get_query_var('tab', '');

    return match ($post_slug) {
      'dashboard' => $this->get_page_title(
        DashboardPage::get_tab_title($current_tab)
      ),
      default => $title,
    };
  }

  private function get_page_title($tab_title) {
    return $tab_title . ' - ' . get_bloginfo('name');
  }
}
