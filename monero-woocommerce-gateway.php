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

// Generate Monero Subaddress when Order is Created
add_action('woocommerce_new_order', 'generate_monero_subaddress', 10, 1);

function generate_monero_subaddress($order_id) {
    // Your logic to generate Monero subaddress here
    // You may want to store the generated subaddress in order meta for later use
    $subaddress = your_generate_subaddress_function();

    // Store the subaddress in order meta
    update_post_meta($order_id, '_monero_subaddress', $subaddress);
}

// Display Monero Subaddress on Checkout Page
add_action('woocommerce_review_order_before_submit', 'display_monero_subaddress');

function display_monero_subaddress() {
    $order_id = wc_get_order_id_by_order_key(WC()->session->get('order_awaiting_payment'));
    $subaddress = get_post_meta($order_id, '_monero_subaddress', true);

    if (!empty($subaddress)) {
        echo '<p><strong>Monero Payment Details:</strong></p>';
        echo '<p>Send Monero to the following subaddress:</p>';
        echo "<p><code>$subaddress</code></p>";
    }
}

// Check Monero Transaction Status and Redirect
add_action('woocommerce_thankyou', 'check_monero_transaction_status', 10, 1);

function check_monero_transaction_status($order_id) {
    // Your logic to check Monero transaction status here
    $subaddress = get_post_meta($order_id, '_monero_subaddress', true);

    if (!empty($subaddress)) {
        // Assume your function to check Monero transactions is named check_monero_transactions
        $transaction_status = check_monero_transactions($subaddress);

        if ($transaction_status >= 1) {
            // Redirect user to home page for successful transaction
            wp_redirect(home_url());
            exit;
        } elseif ($transaction_status === 0) {
            // Redirect user to product page if transaction is in progress (0 confirmation)
            $product_id = get_post_meta($order_id, '_product_id', true); // Adjust this based on your setup
            wp_redirect(get_permalink($product_id));
            exit;
        } else {
            // Transaction failed or not confirmed after 40 minutes, cancel the order
            wc_cancel_order($order_id);
            // Redirect user to shop page
            wp_redirect(wc_get_page_permalink('shop'));
            exit;
        }
    }
}

// Enqueue Countdown Timer Script
add_action('wp_enqueue_scripts', 'enqueue_countdown_timer_script');

function enqueue_countdown_timer_script() {
    wp_enqueue_script('countdown-timer', plugin_dir_url(__FILE__) . 'countdown-timer.js', array('jquery'), '1.0', true);
    $localized_data = array(
        'order_id' => wc_get_order_id_by_order_key(WC()->session->get('order_awaiting_payment')),
        'expiration_time' => strtotime('+40 minutes', current_time('timestamp')),
    );
    wp_localize_script('countdown-timer', 'countdown_timer_data', $localized_data);
}
