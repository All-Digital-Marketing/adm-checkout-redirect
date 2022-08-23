<?php
/*
Plugin Name: iDesigns Payment Complete Redirect
Description: Redirect after successful payment complete. Global redirect or per category. Requires 'Advanced Custom Fields' plugin for product category functionality..
Author: danielV
Version: 3.3
Author URI: https://ibbotsondesigns.com.au/
*/
const ADM_IPCR_VER = '3.3';

defined('ABSPATH') || exit;

class admCheckoutRedirectUpdateChecker {
  
  public string $plugin_slug;
  public string $version;
  public string $cache_key;
  public bool   $cache_allowed;
  
  public function __construct() {
    
    $this->plugin_slug   = plugin_basename(__DIR__);
    $this->version       = ADM_IPCR_VER;
    $this->cache_key     = 'adm_custom_upd';
    $this->cache_allowed = true;
    
    add_filter('plugins_api', array($this, 'info'), 20, 3);
    add_filter('site_transient_update_plugins', array($this, 'update'));
    add_action('upgrader_process_complete', array($this, 'purge'), 10, 2);
    
  }
  
  /**
   * @throws JsonException
   */
  public function request() {
    
    $remote = get_transient($this->cache_key);
    
    if (false === $remote || !$this->cache_allowed) {
      
      $remote = wp_remote_get(
        'https://demo.alldigitalmarketing.com.au/plugins/adm-checkout-redirect/info.json',
        array(
          'timeout' => 10,
          'headers' => array(
            'Accept' => 'application/json',
          ),
        )
      );
      
      if (
        is_wp_error($remote)
        || 200 !== wp_remote_retrieve_response_code($remote)
        || empty(wp_remote_retrieve_body($remote))
      ) {
        return false;
      }
      
      set_transient($this->cache_key, $remote, DAY_IN_SECONDS);
      
    }
    
    return json_decode(wp_remote_retrieve_body($remote), false, 512, JSON_THROW_ON_ERROR);
    
  }
  
  
  /**
   * @throws JsonException
   */
  public function info($res, $action, $args) {
    
    // print_r( $action );
    // print_r( $args );
    
    // do nothing if you're not getting plugin information right now
    if ('plugin_information' !== $action) {
      return $res;
    }
    
    // do nothing if it is not our plugin
    if ($this->plugin_slug !== $args->slug) {
      return $res;
    }
    
    // get updates
    $remote = $this->request();
    
    if (!$remote) {
      return $res;
    }
    
    $res = new stdClass();
    
    $res->name           = $remote->name;
    $res->slug           = $remote->slug;
    $res->version        = $remote->version;
    $res->tested         = $remote->tested;
    $res->requires       = $remote->requires;
    $res->author         = $remote->author;
    $res->author_profile = $remote->author_profile;
    $res->download_link  = $remote->download_url;
    $res->trunk          = $remote->download_url;
    $res->requires_php   = $remote->requires_php;
    $res->last_updated   = $remote->last_updated;
    
    $res->sections = array(
      'description'  => $remote->sections->description,
      'installation' => $remote->sections->installation,
      'changelog'    => $remote->sections->changelog,
    );
    
    if (!empty($remote->banners)) {
      $res->banners = array(
        'low'  => $remote->banners->low,
        'high' => $remote->banners->high,
      );
    }
    
    return $res;
    
  }
  
  /**
   * @throws JsonException
   */
  public function update($transient) {
    
    if (empty($transient->checked)) {
      return $transient;
    }
    
    $remote = $this->request();
    
    if (
      $remote
      //        && version_compare($remote->requires_php, PHP_VERSION, '<')
      && version_compare($this->version, $remote->version, '<')
      //        && version_compare($remote->requires, get_bloginfo('version'), '<=')
    ) {
      $res              = new stdClass();
      $res->slug        = $this->plugin_slug;
      $res->plugin      = plugin_basename(__FILE__); // misha-update-plugin/misha-update-plugin.php
      $res->new_version = $remote->version;
      $res->tested      = $remote->tested;
      $res->package     = $remote->download_url;
      
      $transient->response[$res->plugin] = $res;
      
    }
    
    return $transient;
    
  }
  
  public function purge($upgrader, $options): void {
    
    if (
      $this->cache_allowed
      && 'update' === $options['action']
      && 'plugin' === $options['type']
    ) {
      // just clean the cache when new plugin version is installed
      delete_transient($this->cache_key);
    }
    
  }
  
  
}

new admCheckoutRedirectUpdateChecker();

require('inc/adm-checkout-redirect-woo-settings.php');
require('inc/adm-checkout-acf.php');

add_filter('plugin_row_meta', function ($links_array, $plugin_file_name, $plugin_data, $status) {
  
  if (strpos($plugin_file_name, basename(__FILE__))) {
    
    $links_array[] = sprintf(
      '<a href="%s" class="thickbox open-plugin-details-modal">%s</a>',
      add_query_arg(
        array(
          'tab'       => 'plugin-information',
          'plugin'    => plugin_basename(__DIR__),
          'TB_iframe' => true,
          'width'     => 772,
          'height'    => 788,
        ),
        admin_url('plugin-install.php')
      ),
      __('View details')
    );
    
  }
  
  return $links_array;
  
}, 25, 4);


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