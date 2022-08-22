<?php
/*
Plugin Name: iDesigns Payment Complete Redirect
Plugin URI: https://ibbotsondesigns.com.au/
Description: Redirect after successful payment complete. Global redirect or per category. Requires 'Advanced Custom Fields' plugin for product category functionality..
Author: iDesigns
Version: 3.0
Author URI: https://ibbotsondesigns.com.au/
*/

require('inc/adm-checkout-redirect-woo-settings.php');
require('inc/adm-checkout-acf.php');
require('inc/updater.php');
require('inc/adm-plugin-updater.php');

add_filter('plugin_action_links_adm-checkout-redirect/adm-checkout-redirect.php', 'nc_settings_link');
function nc_settings_link($links) {
  // Build and escape the URL.
  $url = '/wp-admin/admin.php?page=wc-settings&tab=advanced&section=idesigns-woo';
  // Create the link.
  $settings_link = "<a href='$url'>" . __('Settings') . '</a>';
  // Adds the link to the end of the array.
  $links[] = $settings_link;
  return $links;
}

add_action('template_redirect', 'adm_order_received_redirect');
function adm_order_received_redirect() {
  
  // do nothing if we are not on the order received page
  if (empty($_GET['key']) || !is_wc_endpoint_url('order-received')) {
    return;
  }
  
  if (is_wc_endpoint_url('order-received')) {
    /* should not need to load, but just in cases... */
    wp_register_script('adm-order-received-redirect', plugin_dir_url(__FILE__) . '/adm-order-received-redirect.js', ['jquery'], filemtime(plugin_dir_path(__FILE__) . '/adm-order-received-redirect.js'));
    wp_localize_script('adm-order-received-redirect', 'adm', [
      'ajax_url' => admin_url('admin-ajax.php'),
    ]);
    wp_enqueue_script('adm-order-received-redirect');
  }
  
  
  if ('yes' !== get_option('wc_idesigns_wc_js_only')) {
    $adm_red      = adm_get_redirect_endpoint($_GET['key']);
    $redirect_url = $adm_red->url;
    
    if ('yes' === get_option('wc_idesigns_wc_pass_order_key')) {
      $redirect_url .= '?key=' . $_GET['key'];
    }
    
    if ($adm_red->redirect) {
      wp_safe_redirect($redirect_url);
    }
  }
  
  
}

function adm_get_redirect_endpoint($order_key): stdClass {
  $res           = new stdClass();
  $res->redirect = false;
  $res->key      = false;
  $res->url      = '';
  
  if ('yes' === get_option('wc_idesigns_wc_pass_order_key')) {
    $res->key = true;
  }
  
  if ('yes' === get_option('wc_idesigns_wc_default_enabled')) {
    $default_url   = get_option('wc_idesigns_wc_thank_default');
    $res->url      = $default_url;
    $res->redirect = $default_url && $default_url !== '';
  }
  
  if (!function_exists('acf_add_local_field_group')) {
    return $res;
  }
  
  $order_id = wc_get_order_id_by_order_key($order_key);
  $order    = wc_get_order($order_id);
  
  foreach ($order->get_items() as $item) {
    $product_id = $item->get_product_id();
    $terms      = get_the_terms($product_id, 'product_cat');
    foreach ($terms as $term) {
      
      $cat_redirect_url = get_field('custom_thank_you_page', $term);
      if ($cat_redirect_url) {
        $res->redirect = true;
        $res->url      = $cat_redirect_url;
        break 2;
      }
      
    }
  }
  
  return $res;
}

add_action('wp_ajax_adm_orr', 'adm_orr');
add_action('wp_ajax_nopriv_adm_orr', 'adm_orr');
function adm_orr() {
  $order_key = sanitize_key($_POST['order_key']);
  $adm_red   = adm_get_redirect_endpoint($order_key);
  wp_send_json($adm_red);
}