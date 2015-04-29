<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Referral')) {

    class Saff_Referral {

        static function record_subscription($renewal_order, $original_order, $product_id, $new_order_role) {
            $order_id = $renewal_order->id;
            self::record_referral($order_id);
        }

        static function record_referral($order_id) {
            
            if ($order_id != 0) {

                $wc_compat = Smart_Affiliates::wc_compat();

                $order = SA_WC_Compatibility_2_2::get_order($order_id);
                $post_id = $order_id;

                $order_user_id = get_post_meta($order_id, '_customer_user', true);
                $is_commission_recorded = get_post_meta($order_id, 'is_commission_recorded', true);

                if ($is_commission_recorded == 'yes') {
                    return false;
                }

                $commission_percentage = get_option('saff_storewide_commission');
                
                $amount = ($order->get_total() * $commission_percentage) / 100;
                $currency_id = get_post_meta($order_id, '_order_currency', true);

                $status = SAFF_REFERRAL_STATUS_UNPAID;
                $description = '';
                $data = '';
                $type = '';
                $reference = '';
                $affiliate_id = self::suggest_referral($post_id, $description, $data, $amount, $currency_id, $status, $type, $reference, $order_user_id);

            }

        }

        static function suggest_referral($post_id, $description = '', $data = null, $amount = null, $currency_id = null, $status = null, $type = null, $reference = null, $order_user_id) {
            global $wpdb;

            // Shouldn't check from cookie, figure out the affiliate based on order only

            $wc_compat = Smart_Affiliates::wc_compat();

            $affiliate_id = get_referrer_id();

            // Handle Subscription
            if ( saff_is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) && get_option( 'is_recurring_commission' ) == 'yes' ) {
                $renewal_order = SA_WC_Compatibility_2_2::get_order( $post_id );  // 
                if (WC_Subscriptions_Renewal_Order::is_renewal($renewal_order)) {
                    $parent_order_id = WC_Subscriptions_Renewal_Order::get_parent_order_id($renewal_order);
                    if (!empty($parent_order_id)) {
                        $affiliate_id = $wpdb->get_var("SELECT affiliate_id FROM " . get_saff_tablename('referrals') . " WHERE post_id = '" . $parent_order_id . "'");
                    }
                }
            } 

            if ($affiliate_id) {
                $affiliate_id = self::add_referral($affiliate_id, $post_id, $description, $data, $amount, $currency_id, $status, $type, $reference, $order_user_id);
            }  


            return $affiliate_id;
        }

        static function add_referral($affiliate_id, $post_id, $description = '', $data = null, $amount = null, $currency_id = null, $status = null, $type = null, $reference = null, $order_user_id) {
            global $wpdb;

            if ($affiliate_id) {

                $now = date('Y-m-d H:i:s', time());
                $table = get_saff_tablename('referrals');

                $columns = "(affiliate_id, post_id, datetime, description";
                $formats = "(%d, %d, %s, %s";
                $values = array($affiliate_id, $post_id, $now, $description);

                if (!empty($order_user_id)) {
                    $columns .= ",user_id ";
                    $formats .= ",%d ";
                    $values[] = $order_user_id;
                }

                // add ip
                $ip_address = $_SERVER['REMOTE_ADDR'];
                if (PHP_INT_SIZE >= 8) {
                    if ($ip_int = ip2long($ip_address)) {
                        $columns .= ',ip ';
                        $formats .= ',%d ';
                        $values[] = $ip_int;
                    }
                } else {
                    if ($ip_int = ip2long($ip_address)) {
                        $ip_int = sprintf('%u', $ip_int);
                        $columns .= ',ip';
                        $formats .= ',%s';
                        $values[] = $ip_int;
                    }
                }

                if (is_array($data) && !empty($data)) {
                    $columns .= ",data ";
                    $formats .= ",%s ";
                    $values[] = serialize($data);
                }

                if (!empty($amount) && !empty($currency_id)) {
                    $columns .= ",amount ";
                    $formats .= ",%s ";
                    $values[] = $amount;

                    $columns .= ",currency_id ";
                    $formats .= ",%s ";
                    $values[] = $currency_id;
                }
                if (!empty($status)) {
                    $columns .= ',status ';
                    $formats .= ',%s ';
                    $values[] = $status;
                }

                if (!empty($type)) {
                    $columns .= ',type ';
                    $formats .= ',%s';
                    $values[] = $type;
                }

                if (!empty($reference)) {
                    $columns .= ',reference ';
                    $formats .= ',%s';
                    $values[] = $reference;
                }

                $columns .= ")";
                $formats .= ")";

                // add the referral
                $keys = explode(',', str_replace(' ', '', substr($columns, 1, strlen($columns) - 2)));
                $referral_data = array_combine($keys, $values);
                $record_referral = $referral_data;
                if ($record_referral) {
                    $query = $wpdb->prepare("INSERT INTO $table $columns VALUES $formats", $values);
                    if ($wpdb->query($query) !== false) {
                        update_post_meta($post_id, 'is_commission_recorded', 'yes');
                        if ($referral_id = $wpdb->get_var("SELECT LAST_INSERT_ID()")) {
                            
                            // Do some action here
                        }
                    }
                }
            }

            return $affiliate_id;
        }
        
        static function update_referral_status($order_id) {
            global $wpdb;
            
            if(!empty($order_id)) {
                $wc_compat = Smart_Affiliates::wc_compat();
                $order = SA_WC_Compatibility_2_2::get_order( $order_id );
                $table = get_saff_tablename('referrals');
                $hook = current_filter();
                $status = ( $order->get_total() > 0 ) ? SAFF_REFERRAL_STATUS_UNPAID : SAFF_REFERRAL_STATUS_PAID;
                
                switch($hook) {
                    case 'woocommerce_order_status_cancelled':
                    case 'woocommerce_order_status_refunded':
                        $query = $wpdb->prepare("UPDATE $table SET status = %s WHERE post_id = %d AND status NOT IN (%s)", SAFF_REFERRAL_STATUS_REJECTED, $order_id, SAFF_REFERRAL_STATUS_PAID);
                        break;
                        
                    case 'woocommerce_order_status_completed':
                        $query = $wpdb->prepare("UPDATE $table SET status = %s WHERE post_id = %d AND status NOT IN (%s)", $status, $order_id, SAFF_REFERRAL_STATUS_PAID);
                        break;
                }
                $wpdb->query($query);
            }
        }
    }
}

/*
 * Used "woocommerce_checkout_update_order_meta" action instead of "woocommerce_new_order" hook. Because don't get the whole 
 * order data on "woocommerce_new_order" hook.
 * 
 * Checked woocommerce "includes/class-wc-checkout.php" file and then after use this hook
 * 
 * Track referral before completion of Order with status "Pending"
 * When Order Complets, Change referral status from Pending to Unpaid
 */
add_action('woocommerce_checkout_update_order_meta', 'Saff_Referral::record_referral');
if ( saff_is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
    add_action('woocommerce_subscriptions_renewal_order_created', 'Saff_Referral::record_subscription', 10, 4);
}
add_action('woocommerce_order_status_completed', 'Saff_Referral::update_referral_status');
add_action('woocommerce_order_status_refunded', 'Saff_Referral::update_referral_status');
add_action('woocommerce_order_status_cancelled', 'Saff_Referral::update_referral_status');
