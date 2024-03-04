<?php

namespace WStrategies\BMB\Includes\Hooks;

class CronScheduleHooks implements HooksInterface {
  public function load(Loader $loader): void {
    $loader->add_filter('cron_schedules', [$this, 'custom_cron_schedules']);
  }

  public function custom_cron_schedules($schedules) {
    $schedules['every_minute'] = [
      'interval' => 60,
      'display' => __('Every Minute'),
    ];
    return $schedules;
  }
}
