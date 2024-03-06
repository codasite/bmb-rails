<?php

namespace WStrategies\BMB\Public\Partials\shared;

class HtmlUtils {
  /**
   * Map an associative array to HTML attributes.
   *
   * @param array $array The associative array to be mapped.
   * @return string The HTML attributes string.
   */
  public static function mapArrayToAttributes(array $array): string {
    $attributesString = '';

    foreach ($array as $key => $value) {
      // Escape values for security
      $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

      // Concatenate key-value pairs as HTML attributes
      $attributesString .= "$key=\"$escapedValue\" ";
    }

    return trim($attributesString);
  }
}
