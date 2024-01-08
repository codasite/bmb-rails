<?php
namespace WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato;

class GelatoAdminHooks {
  //  add a custom text field for the bmb-logo-theme in the admin product variation settings
  // Attach to `woocommerce_product_after_variable_attributes` hook
  public function variation_settings_fields(
    $loop,
    $variation_data,
    $variation
  ): void {
    // Get the parent product
    $parent_product_id = wp_get_post_parent_id($variation->ID);

    // Check if the parent product has the 'bracket-ready' category
    if (has_term(BRACKET_PRODUCT_CATEGORY, 'product_cat', $parent_product_id)) {
      $front_design_value = get_post_meta(
        $variation->ID,
        'wpbb_front_design',
        true
      );
      $bracket_theme_value = get_post_meta(
        $variation->ID,
        'wpbb_bracket_theme',
        true
      );

      // Text input for front design url
      woocommerce_wp_text_input([
        'id' => 'wpbb_front_design[' . $variation->ID . ']',
        'label' => __('Front design URL', 'woocommerce'),
        'description' => __(
          'The design to print on the front of this product, in PDF format. Typically an S3 object URL.',
          'woocommerce'
        ),
        'desc_tip' => 'true',
        'value' => $front_design_value,
      ]);
      // Select input for bracket theme
      woocommerce_wp_select([
        'id' => 'wpbb_bracket_theme[' . $variation->ID . ']',
        'label' => __('Bracket theme', 'woocommerce'),
        'description' => __(
          'The bracket theme to be used on this variation',
          'woocommerce'
        ),
        'desc_tip' => 'true',
        'value' => $bracket_theme_value,
        'options' => [
          '' => __('Choose Theme', 'woocommerce'),
          'dark' => __('Dark', 'woocommerce'),
          'light' => __('Light', 'woocommerce'),
        ],
      ]);
    }
  }

  // save the value of this field when the product variation is saved
  // Attach to `woocommerce_save_product_variation` hook
  public function save_variation_settings_fields($variation_id, $i): void {
    if (isset($_POST['wpbb_front_design'][$variation_id])) {
      $front_design = $_POST['wpbb_front_design'][$variation_id];
      update_post_meta(
        $variation_id,
        'wpbb_front_design',
        esc_attr($front_design)
      );
    }
    if (isset($_POST['wpbb_bracket_theme'][$variation_id])) {
      $bracket_theme = $_POST['wpbb_bracket_theme'][$variation_id];
      update_post_meta(
        $variation_id,
        'wpbb_bracket_theme',
        esc_attr($bracket_theme)
      );
    }
  }

  public function validate_variation_fields($variation_id, $i): void {
    // Check for Front Design URL
    if (empty(get_post_meta($variation_id, 'wpbb_front_design', true))) {
      update_option(
        'custom_admin_error',
        'WARNING: Front Design URL is blank for variation ID ' .
          $variation_id .
          '. Customer will be unable to add this product to their cart.'
      );
    }

    // Check for Bracket Theme
    if (empty(get_post_meta($variation_id, 'wpbb_bracket_theme', true))) {
      update_option(
        'custom_admin_error',
        'WARNING: Bracket theme is blank for variation ID ' .
          $variation_id .
          '. Customer will be unable to customize this product.'
      );
    }
  }

  // Display the custom error message
  // hooked to `admin_notices` action hook
  public function display_custom_admin_error(): void {
    $message = get_option('custom_admin_error');
    if ($message) {
      echo '<div class="error notice">
	          <p>' .
        $message .
        '</p>
	      </div>';
      delete_option('custom_admin_error');
    }
  }
}
