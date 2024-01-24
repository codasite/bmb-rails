<?php
namespace WStrategies\BMB\Includes\Hooks;

class CustomPostHooks implements HooksInterface {
  public function load(Loader $loader): void {
    $loader->add_action('init', [$this, 'register_custom_post_types']);
    $loader->add_filter('init', [$this, 'register_custom_post_status']);
  }
  public function register_custom_post_types(): void {
    register_post_type('bracket', [
      'labels' => [
        'name' => __('Brackets'),
        'singular_name' => __('Bracket'),
      ],
      'description' => 'Brackets for the WP Bracket Builder plugin',
      'public' => true,
      'has_archive' => true,
      'supports' => [
        'title',
        'author',
        'thumbnail',
        'custom-fields',
        'comments',
      ],
      'show_ui' => true,
      'show_in_rest' => true, // Default endpoint for oxygen. React app uses Bracket_Api
      'taxonomies' => ['post_tag'],
      'rewrite' => ['slug' => 'brackets'],
    ]);

    register_post_type('bracket_play', [
      'labels' => [
        'name' => __('Plays'),
        'singular_name' => __('Play'),
      ],
      'description' => 'Bracket plays for the WP Bracket Builder plugin',
      'public' => true,
      'has_archive' => true,
      'supports' => ['title', 'author', 'thumbnail', 'custom-fields'],
      'show_ui' => true,
      'show_in_rest' => true,
      'taxonomies' => ['post_tag'],
      'rewrite' => ['slug' => 'plays'],
    ]);

    register_post_type('user_profile', [
      'labels' => [
        'name' => __('User profiles'),
        'singular_name' => __('User profile'),
      ],
      'description' => 'User profiles for the WP Bracket Builder plugin',
      'public' => true,
      'has_archive' => true,
      'supports' => ['title', 'author', 'thumbnail', 'custom-fields'],
      'show_ui' => true,
      'show_in_rest' => true,
      'taxonomies' => ['post_tag'],
      'rewrite' => ['slug' => 'users'],
    ]);
  }
  public function register_custom_post_status(): void {
    // Custom post status for completed tournaments
    register_post_status('complete', [
      'label' => 'Complete',
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop(
        'Completed <span class="count">(%s)</span>',
        'Complete <span class="count">(%s)</span>'
      ),
    ]);

    register_post_status('upcoming', [
      'label' => 'Upcoming',
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop(
        'Completed <span class="count">(%s)</span>',
        'Archive <span class="count">(%s)</span>'
      ),
    ]);

    register_post_status('score', [
      'label' => 'Scored',
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop(
        'Scored <span class="count">(%s)</span>',
        'Scored <span class="count">(%s)</span>'
      ),
    ]);

    register_post_status('archive', [
      'label' => 'Archive',
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop(
        'Completed <span class="count">(%s)</span>',
        'Archive <span class="count">(%s)</span>'
      ),
    ]);
  }
}
