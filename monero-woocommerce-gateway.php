<?php
/**
 * Plugin Name: Monero WooCommerce Gateway
 * Description: Enable Monero payments on your WooCommerce store.
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Include our Gateway Class and register Payment Gateway with WooCommerce
 */
add_action('plugins_loaded', 'init_monero_gateway_class');
function init_monero_gateway_class() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    require_once dirname(__FILE__) . '/class-wc-monero-gateway.php';

    add_filter('woocommerce_payment_gateways', 'add_monero_gateway');
    function add_monero_gateway($gateways) {
        $gateways[] = 'WC_Monero_Gateway';
        return $gateways;
    }
}

/**
 * Add custom action links
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links');
function add_action_links($links) {
    $settings_link = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=monero_gateway') . '">' . __('Settings', 'monero-woocommerce-gateway') . '</a>',
    );

    return array_merge($settings_link, $links);
}
