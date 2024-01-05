<?php
namespace WStrategies\BMB\Includes\Helpers;

class ConstructorArgs {
  /**
   * @template T
   * @param T $obj
   * @param array<string, mixed> $opts
   */
  public static function load($obj, $opts = []): void {
    $reflector = new \ReflectionClass($obj);
    $constructor = $reflector->getConstructor();
    if (!$constructor) {
      return;
    }
    $params = $constructor->getParameters();
    foreach ($params as $param) {
      $param_name = $param->getName();
      if (isset($opts[$param_name])) {
        $obj->$param_name = $opts[$param_name];
      }
    }
  }
}
