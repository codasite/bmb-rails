<?php
namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Loader;

interface HooksInterface {
  public function load(Loader $loader): void;
}
