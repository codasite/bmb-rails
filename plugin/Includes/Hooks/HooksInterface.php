<?php
namespace WStrategies\BMB\Includes\Hooks;

interface HooksInterface {
  public function load(Loader $loader): void;
}
