<?php
add_filter('woocommerce_get_sections_advanced', 'idesigns_wc_add_section');
function idesigns_wc_add_section($sections) {
  $sections['idesigns-woo'] = __('iDesigns Custom', 'adm');
  return $sections;
}

add_filter('woocommerce_get_settings_advanced', 'idesigns_wc_all_settings', 10, 2);
function idesigns_wc_all_settings($settings, $current_section) {
  
  if ($current_section === 'idesigns-woo') {
    
    $settings_catalog_options = array();
    
    // Add Title to the Settings
    $settings_catalog_options[] = array(
      'name' => __('WooCommerce iDesigns Custom Settings', 'adm'),
      'type' => 'title',
      'desc' => __('Choose to enable the default `order received` or `thank you` redirect.<br>Visit the <a href="/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product">Product Category edit page</a> to change order-received redirects based on product category (requires <a target="_blank" href="https://www.advancedcustomfields.com/">ACF</a>)', 'adm'),
      'id'   => 'wc_idesigns_wc_title',
    );
    
    $settings_catalog_options[] = array(
      'name'        => __('JavaScript redirect only', 'adm'),
      'type'        => 'checkbox',
      'id'          => 'wc_idesigns_wc_js_only',
      'description' => 'This is useful to allow tracking scripts to function on the order received page. Then redirect after tracking has had a chance to collect data.',
    );
    
    
    $settings_catalog_options[] = array(
      'name' => __('Enable default page', 'adm'),
      'type' => 'checkbox',
      'id'   => 'wc_idesigns_wc_default_enabled',
    );
    
    $settings_catalog_options[] = array(
      'name'        => __('Pass order key to next page', 'adm'),
      'type'        => 'checkbox',
      'id'          => 'wc_idesigns_wc_pass_order_key',
      'description' => 'The order key may be needed if you are using any WooCommerce order shortcodes on the custom page.',
    );
    
    
    // Add second text field option
    $settings_catalog_options[] = array(
      'name'        => __('Thank you page default', 'adm'),
      'type'        => 'text',
      'id'          => 'wc_idesigns_wc_thank_default',
      'placeholder' => 'i.e: ' . site_url() . '/thank-you/',
    );
    
    $settings_catalog_options[] = array(
      'type' => 'sectionend',
      'id'   => 'wc_idesigns_wc_end',
    );
    
    return $settings_catalog_options;
  }
  return $settings;
}