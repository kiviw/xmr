<?php
if (!class_exists('WC_Payment_Gateway')) {
    return;
}

class WC_Monero_Gateway extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'monero_gateway';
        $this->has_fields = false;
        $this->method_title = 'Monero';
        $this->method_description = 'Accept Monero payments';

        // Actions
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
    }

    // Process the payment and handle order creation
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        // Generate Monero subaddress and save to order
        $subaddress = generate_monero_subaddress_function(); // Implement this function
        update_post_meta($order_id, '_monero_subaddress', $subaddress);

        // Save additional information like product ID for redirection
        $items = $order->get_items();
        foreach ($items as $item) {
            $product_id = $item->get_product_id();
            update_post_meta($order_id, '_product_id', $product_id);
            break; // Assuming only one product in the order
        }

        // Redirect user to the Monero payment page
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

    // Display Monero subaddress on the order received page
    public function thankyou_page($order_id) {
        $subaddress = get_post_meta($order_id, '_monero_subaddress', true);

        if (!empty($subaddress)) {
            echo '<p><strong>Monero Payment Details:</strong></p>';
            echo '<p>Send Monero to the following subaddress:</p>';
            echo "<p><code>$subaddress</code></p>";
            echo '<p>Payment expires in <span id="countdown-timer"></span></p>';
        }
    }

    // Display Monero subaddress in emails
    public function email_instructions($order, $sent_to_admin, $plain_text = false) {
        if ($sent_to_admin || !$order || $order->get_payment_method() !== 'monero_gateway') {
            return;
        }

        $subaddress = get_post_meta($order->get_id(), '_monero_subaddress', true);

        if (!empty($subaddress)) {
            echo '<p><strong>Monero Payment Details:</strong></p>';
            echo '<p>Send Monero to the following subaddress:</p>';
            echo "<p><code>$subaddress</code></p>";
            echo '<p>Payment expires in 40 minutes</p>';
        }
    }
}

// Add the Monero gateway to WooCommerce
function add_monero_gateway($gateways) {
    $gateways[] = 'WC_Monero_Gateway';
    return $gateways;
}

add_filter('woocommerce_payment_gateways', 'add_monero_gateway');
