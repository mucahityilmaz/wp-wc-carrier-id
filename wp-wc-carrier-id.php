<?php
/**
 * Plugin Name: Carrier ID for Shipping Methods
 * Plugin URI: https://github.com/mucahityilmaz/wp-wc-carrier-id
 * Description: Adds a Carrier ID field to shipping methods of WooCommerce
 * Version: 1.0.0
 * Author: Mucahit Yilmaz
 * Author URI: https://www.linkedin.com/in/mucahityilmaz
 * Developer: Mucahit Yilmaz
 * Developer URI: https://github.com/mucahityilmaz
 * Text Domain: wp-wc-carrier-id
 * Domain Path: /languages
 *
 * WC requires at least: 2.2
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    if ( ! function_exists( 'wp_wc_carrier_id_add_field' ) ) {
        
        /**
         * Adds "Carrier ID" field to shipping instance form.
         */
        function wp_wc_carrier_id_add_field( $settings ) {
            $settings['carrier_id'] = array(
                'title'       => __( 'Carrier ID', 'wp-wc-carrier-id' ),
                'type'        => 'text',
                'placeholder' => __( 'Carrier ID', 'wp-wc-carrier-id' ),
                'description' => '',
            );

            return $settings;
        }
    }

    if ( ! function_exists( 'wp_wc_carrier_id_add_filter' ) ) {
        
        /**
         * Registers new shipping instance form field for "Flat Rate" and "Free Shipping"
         */
        function wp_wc_carrier_id_add_filter() {
            
            add_filter( 'woocommerce_shipping_instance_form_fields_flat_rate', 'wp_wc_carrier_id_add_field' );
            add_filter( 'woocommerce_shipping_instance_form_fields_free_shipping', 'wp_wc_carrier_id_add_field' );
        }
        add_action( 'woocommerce_init', 'wp_wc_carrier_id_add_filter' );
    }

    if ( ! function_exists( 'wp_wc_carrier_id_save' ) ) {
        
        /**
         * Saves the Carrier ID for the order when its status is updated to "Processing"
         */
        function wp_wc_carrier_id_save($order_id) {
            
            $the_order = wc_get_order($order_id);
            $shipping_methods = array_values($the_order->get_shipping_methods());

            if (!empty($shipping_methods)) {
                foreach($shipping_methods as $shipping_method){
                    if ($shipping_method->get_method_id() === 'flat_rate' || $shipping_method->get_method_id() === 'free_shipping' ) {
                        $option_id = 'woocommerce_' . $shipping_method->get_method_id() . '_' . $shipping_method->get_instance_id() . '_settings';
                        $option_list = get_option($option_id);
    
                        if (isset($option_list['carrier_id'])) {
                            update_post_meta($order_id, '_carrier_id', $option_list['carrier_id']);
                        }
                    }
                }
            }
        }
        add_action( 'woocommerce_order_status_processing', 'wp_wc_carrier_id_save', 10, 1 );
    }
}
