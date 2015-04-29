<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Admin_Affiliate')) {

    class Saff_Admin_Affiliates {

        var $affiliate_ids;
        var $from;
        var $to;
        var $sales_post_types;
        var $storewide_sales;
        var $affiliates_sales;
        var $net_affiliates_sales;
        var $unpaid_commissions;
        var $visitors_count;
        var $customers_count;
        var $affiliates_refund;
        var $paid_commissions;
        var $commissions_earned;
        var $formatted_join_duration;
        var $affiliates_orders;
        var $last_payout_details;
        var $affiliates_display_names;

        /**
         * 	Constructor
         *
         * 	@param array 
         */
        function __construct($affiliate_ids = array(), $from = '', $to = '') {

            if (!empty($affiliate_ids)) {

                $this->affiliate_ids = (!is_array($affiliate_ids) ) ? array($affiliate_ids) : $affiliate_ids;

                $this->from = (!empty($from) ) ? date('Y-m-d', strtotime($from)) : '';

                $this->to = (!empty($to) ) ? date('Y-m-d', strtotime($to)) : '';

                $this->sales_post_types = apply_filters('saff_sales_post_types', array('shop_order'));

                $this->storewide_sales = $this->get_storewide_sales();

                $this->affiliates_orders = $this->get_affiliates_orders();

                $this->affiliates_refund = $this->get_affiliates_refund();

                $this->affiliates_sales = $this->get_affiliates_sales();

                $this->net_affiliates_sales = $this->get_net_affiliates_sales();

                $this->unpaid_commissions = $this->get_unpaid_commissions();

                $this->visitors_count = $this->get_visitors_count();

                $this->customers_count = $this->get_customers_count();

                $this->paid_commissions = $this->get_paid_commissions();

                $this->commissions_earned = $this->get_commissions_earned();

                $this->formatted_join_duration = $this->get_formatted_join_duration();

                $this->last_payout_details = $this->get_last_payout_details();

                $this->affiliates_display_names = $this->get_affiliates_display_names();

                return $this;
            }

            return false;
        }

        /**
         * Function to get storewide sales
         * 
         * @return float storewide sales
         */
        function get_storewide_sales() {
            global $wpdb;

            $storewide_post_id_query = "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE 1 AND post_status = 'wc-completed' ";

            if (!empty($this->sales_post_types)) {
                $storewide_post_id_query .= $wpdb->prepare(" AND post_type IN ( " . str_repeat('%s,', ( count($this->sales_post_types) - 1)) . "%s )", $this->sales_post_types);
            }

            if (!empty($this->from)) {
                $storewide_post_id_query .= $wpdb->prepare(" AND post_date >= %s", $this->from . ' 00:00:00');
            }

            if (!empty($this->to)) {
                $storewide_post_id_query .= $wpdb->prepare(" AND post_date <= %s", $this->to . ' 23:59:59');
            }


            $post_ids = $wpdb->get_col($storewide_post_id_query);

            $storewide_sales = 0;

            if (!empty($post_ids)) {

              // Let 3rd party plugin developers to calculate storewide sales for their custom post type
              // Remember to add sales to $storewide_sales
			
                $wc_compat = Smart_Affiliates::wc_compat();

		$storewide_post_id_query = "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE 1";

		if ( ! SA_WC_Compatibility_2_2::is_wc_gte_22() ) {
                    $storewide_post_id_query .= " AND post_status = 'publish'";
		}

                $storewide_sales = apply_filters('saff_storewide_sales', $storewide_sales, $post_ids);
            }

            return $storewide_sales;
        }

        /**
         * Function to get affiliates sales
         * 
         * @return float affiliates sales
         */
        function get_affiliates_sales() {
            global $wpdb;

            $post_ids = $this->affiliates_orders;

            $affiliates_sales = 0;
            $completed_affiliates_sales = 0;

            if (!empty($post_ids)) {

                // Let 3rd party plugin developers to calculate affiliates sales for their custom post type


                $completed_affiliates_sales = apply_filters('saff_completed_affiliates_sales', $completed_affiliates_sales, $post_ids);

                $refunded_affiliates_sales = $this->affiliates_refund;

                $affiliates_sales = $completed_affiliates_sales + $refunded_affiliates_sales;
            }

            return $affiliates_sales;
        }

        /**
         * Function to get net affiliates sales
         * 
         * @return float net affiliates sales
         */
        function get_net_affiliates_sales() {
            global $wpdb;

            $net_affiliates_sales = $this->affiliates_sales - $this->affiliates_refund;

            return $net_affiliates_sales;
        }

        /**
         * Function to get visitors count
         * 
         * @return int visitors count
         */
        function get_visitors_count() {
            global $wpdb;

            $visitors_ip_query = "SELECT DISTINCT CONCAT_WS( ':', ip, user_id ) FROM " . get_saff_tablename('hits') . " WHERE 1";

            // If no affiliates, get total visitors count from all affiliates
            // If more than one affiliates, get total visitors count from all those affiliates

            if (!empty($this->affiliate_ids)) {
                $visitors_ip_query .= $wpdb->prepare(" AND affiliate_id IN ( " . str_repeat('%d,', ( count($this->affiliate_ids) - 1)) . "%d )", $this->affiliate_ids);
            } else {
                $visitors_ip_query .= $wpdb->prepare(" AND affiliate_id != 0");
            }

            if (!empty($this->from)) {
                $visitors_ip_query .= $wpdb->prepare(" AND datetime >= %s", $this->from . ' 00:00:00');
            }

            if (!empty($this->to)) {
                $visitors_ip_query .= $wpdb->prepare(" AND datetime <= %s", $this->to . ' 23:59:59');
            }

            $unique_ip = $wpdb->get_col($visitors_ip_query);
            $visitors_count = count($unique_ip);


            return $visitors_count;
        }

        /**
         * Function to get customers count
         * 
         * @return int customers count
         */
        function get_customers_count() {
            global $wpdb;

            $customers_ip_query = "SELECT DISTINCT IF( user_id > 0, user_id, CONCAT_WS( ':', ip, user_id ) ) FROM " . get_saff_tablename('referrals') . " WHERE 1";

            // If no affiliates, get total customers count from all affiliates
            // If more than one affiliates, get total customers count from all those affiliates

            if (!empty($this->affiliate_ids)) {
                $customers_ip_query .= $wpdb->prepare(" AND affiliate_id IN ( " . str_repeat('%d,', ( count($this->affiliate_ids) - 1)) . "%d )", $this->affiliate_ids);
            } else {
                $customers_ip_query .= $wpdb->prepare(" AND affiliate_id != 0");
            }

            if (!empty($this->from)) {
                $customers_ip_query .= $wpdb->prepare(" AND datetime >= %s", $this->from . ' 00:00:00');
            }

            if (!empty($this->to)) {
                $customers_ip_query .= $wpdb->prepare(" AND datetime <= %s", $this->to . ' 23:59:59');
            }

            $unique_ip = $wpdb->get_col($customers_ip_query);
            $customers_count = count($unique_ip);


            return $customers_count;
        }

        /**
         * Function to get affiliates refund
         * 
         * @return float affiliates refund
         */
        function get_affiliates_refund() {
            global $wpdb;

            $post_ids = $this->affiliates_orders;

            $affiliates_refund = 0;

            if (!empty($post_ids)) {

                // Let 3rd party plugin developers to calculate affiliates sales for their custom post type


                $affiliates_refund = apply_filters('saff_affiliates_refund', $affiliates_refund, $post_ids);
            }

            return floatval($affiliates_refund);
        }

        /**
         * Function to get paid commissions
         * 
         * @return float paid commissions
         */
        function get_paid_commissions() {
            global $wpdb;

            $paid_commissions_query = "SELECT SUM(amount) FROM " . get_saff_tablename('referrals') . " WHERE 1";

            $paid_commissions_query .= $wpdb->prepare(" AND status LIKE %s", 'paid');

            // If no affiliate find out paid commissions to all affiliates
            // If more than one affiliate find out paid commissions to all those affiliates

            if (!empty($this->affiliate_ids)) {
                $paid_commissions_query .= $wpdb->prepare(" AND affiliate_id IN ( " . str_repeat('%d,', ( count($this->affiliate_ids) - 1)) . "%d )", $this->affiliate_ids);
            } else {
                $paid_commissions_query .= $wpdb->prepare(" AND affiliate_id != 0");
            }

            if (!empty($this->from)) {
                $paid_commissions_query .= $wpdb->prepare(" AND datetime >= %s", $this->from . ' 00:00:00');
            }

            if (!empty($this->to)) {
                $paid_commissions_query .= $wpdb->prepare(" AND datetime <= %s", $this->to . ' 23:59:59');
            }

            $paid_commissions = $wpdb->get_var($paid_commissions_query);

            return floatval($paid_commissions);
        }

        /**
         * Function to get unpaid commissions
         * 
         * @return float unpaid commissions
         */
        function get_unpaid_commissions() {
            global $wpdb;

            $unpaid_commissions_query = "SELECT SUM(amount) FROM " . get_saff_tablename('referrals') . " WHERE 1";

            $unpaid_commissions_query .= $wpdb->prepare(" AND status LIKE %s", 'unpaid');

            // If no affiliate find out paid commissions to all affiliates
            // If more than one affiliate find out paid commissions to all those affiliates

            if (!empty($this->affiliate_ids)) {
                $unpaid_commissions_query .= $wpdb->prepare(" AND affiliate_id IN ( " . str_repeat('%d,', ( count($this->affiliate_ids) - 1)) . "%d )", $this->affiliate_ids);
            } else {
                $unpaid_commissions_query .= $wpdb->prepare(" AND affiliate_id != 0");
            }

            if (!empty($this->from)) {
                $unpaid_commissions_query .= $wpdb->prepare(" AND datetime >= %s", $this->from . ' 00:00:00');
            }

            if (!empty($this->to)) {
                $unpaid_commissions_query .= $wpdb->prepare(" AND datetime <= %s", $this->to . ' 23:59:59');
            }

            $unpaid_commissions = $wpdb->get_var($unpaid_commissions_query);


            return floatval($unpaid_commissions);
        }

        /**
         * Function to get commissions earned
         * 
         * @return float commissions earned
         */
        function get_commissions_earned() {
            global $wpdb;

            $commissions_earned = $this->paid_commissions + $this->unpaid_commissions;

            return floatval($commissions_earned);
        }

        /**
         * Function to get formatted join duration
         * 
         * @return string formatted join duration
         */
        function get_formatted_join_duration() {
            global $wpdb;

            // Return affiliate join duration in human readable format
            // only when count of $affiliate_ids is one
            // Return empty string otherwise

            if (!empty($this->affiliate_ids) && count($this->affiliate_ids) == 1) {
                $affiliate = get_userdata($this->affiliate_ids[0]);
                $from = strtotime($affiliate->user_registered);
                $to = current_time('timestamp');
                $formatted_join_duration = human_time_diff($from, $to);
            } else {
                $formatted_join_duration = '';
            }

            return $formatted_join_duration;
        }

        /**
         * Function to get affiliates orders
         * 
         * @return array affiliates order ids
         */
        function get_affiliates_orders() {
            global $wpdb;

            $affiliates_orders_query = "SELECT DISTINCT post_id FROM " . get_saff_tablename('referrals') . " WHERE 1";

            // If no affiliates get orders of all affiliates
            // If more than one affiliate, get orders of all those affiliates

            if (!empty($this->affiliate_ids)) {
                $affiliates_orders_query .= $wpdb->prepare(" AND affiliate_id IN ( " . str_repeat('%d,', ( count($this->affiliate_ids) - 1)) . "%d )", $this->affiliate_ids);
            } else {
                $affiliates_orders_query .= $wpdb->prepare(" AND affiliate_id != 0");
            }

            if (!empty($this->from)) {
                $affiliates_orders_query .= $wpdb->prepare(" AND datetime >= %s", $this->from . ' 00:00:00');
            }

            if (!empty($this->to)) {
                $affiliates_orders_query .= $wpdb->prepare(" AND datetime <= %s", $this->to . ' 23:59:59');
            }
            
            // Orderby Clause
            $order_by = " ORDER BY datetime DESC ";
            $affiliates_orders_query .= $order_by;

            $affiliates_orders = $wpdb->get_col($affiliates_orders_query);

            return $affiliates_orders;
        }

        /**
         * Function to get affiliates order details
         * 
         * @return array affiliates order details
         */
        function get_affiliates_order_details() {
            global $wpdb;

            $order_ids = $this->affiliates_orders;

            $affiliates_order_details = array();

            $affiliates_order_details_query = "SELECT referrals.post_id AS order_id, DATE_FORMAT( referrals.datetime, '%d-%b-%Y' ) AS datetime,
                                                           IFNULL( postmeta.meta_value, 0.00 ) AS order_total,
                                                           IFNULL( referrals.amount, 0.00 ) AS commission,
                                                           referrals.status
							  						FROM " . get_saff_tablename('referrals') . " AS referrals
														LEFT JOIN {$wpdb->postmeta} AS postmeta
															ON ( postmeta.post_id = referrals.post_id AND postmeta.meta_key LIKE '_order_total' )
													WHERE 1";

            if (!empty($order_ids)) {
                $affiliates_order_details_query .= $wpdb->prepare(" AND referrals.post_id IN ( " . str_repeat('%d,', ( count($order_ids) - 1)) . "%d )", $order_ids);
            }

            if (!empty($this->affiliate_ids)) {
                $affiliates_order_details_query .= $wpdb->prepare(" AND referrals.affiliate_id IN ( " . str_repeat('%d,', ( count($this->affiliate_ids) - 1)) . "%d )", $this->affiliate_ids);
            } else {
                $affiliates_order_details_query .= $wpdb->prepare(" AND referrals.affiliate_id != 0");
            }

            if (!empty($this->from)) {
                $affiliates_order_details_query .= $wpdb->prepare(" AND referrals.datetime >= %s", $this->from . ' 00:00:00');
            }

            if (!empty($this->to)) {
                $affiliates_order_details_query .= $wpdb->prepare(" AND referrals.datetime <= %s", $this->to . ' 23:59:59');
            }

            // Order By
            $order_by = " ORDER BY referrals.datetime DESC ";
            $affiliates_order_details_query .= $order_by;

            
            $affiliates_order_details_results = $wpdb->get_results($affiliates_order_details_query, 'ARRAY_A');
            if (!empty($affiliates_order_details_results)) {
                foreach ($affiliates_order_details_results as $result) {
                    $affiliates_order_details[$result['order_id']] = $result;
                    $affiliates_order_details[$result['order_id']]['order_url'] = admin_url("post.php?post=" . $result['order_id'] . "&action=edit");
                }
            }

            // Let 3rd party developers to add additional details in orders details

            $affiliates_order_details = apply_filters('saff_order_details', $affiliates_order_details, $order_ids);

            return $affiliates_order_details;
        }

        /**
         * Function to get affiliates payout history
         * 
         * @return array affiliates payout history
         */
        function get_affiliates_payout_history() {
            global $wpdb;

            $affiliates_payout_history = array();

            $affiliates_payout_history_query = "SELECT payouts.payout_id, payout_orders.post_id AS order_id,
                                                                                                                DATE_FORMAT( payouts.datetime, '%d-%b-%Y' ) as datetime,
														payouts.amount AS amount,
														payouts.currency AS currency,
														payouts.payment_gateway AS method,
														payouts.payout_notes
													FROM " . get_saff_tablename('payouts') . " AS payouts
														LEFT JOIN " . get_saff_tablename('payout_orders') . " AS payout_orders
															ON ( payout_orders.payout_id = payouts.payout_id )
													WHERE 1";

            if (!empty($this->affiliate_ids)) {
                $affiliates_payout_history_query .= $wpdb->prepare(" AND payouts.affiliate_id IN ( " . str_repeat('%d,', ( count($this->affiliate_ids) - 1)) . "%d )", $this->affiliate_ids);
            } else {
                $affiliates_payout_history_query .= $wpdb->prepare(" AND payouts.affiliate_id != 0");
            }

            if (!empty($this->from)) {
                //$affiliates_payout_history_query .= $wpdb->prepare(" AND payouts.datetime >= %s", $this->from . ' 00:00:00');
            }

            if (!empty($this->to)) {
                //$affiliates_payout_history_query .= $wpdb->prepare(" AND payouts.datetime <= %s", $this->to . ' 23:59:59');
            }

            // Recent payout first
            $order_by = " ORDER BY payouts.datetime ASC ";
            
            $affiliates_payout_history_query .= $order_by;
            
            
            
            $affiliates_payout_history_results = $wpdb->get_results($affiliates_payout_history_query, 'ARRAY_A');

            $order_ids = array();

            if (!empty($affiliates_payout_history_results)) {

                foreach ($affiliates_payout_history_results as $result) {

                    $affiliates_payout_history[$result['payout_id']] = $result;
                    $order_ids[] = $result['order_id'];
                }
            }

            // Let 3rd party developers to add additional details in payout history

            $affiliates_payout_history = apply_filters('saff_payout_history', $affiliates_payout_history, $order_ids);

            return $affiliates_payout_history;
        }

        /**
         * Function to get last payout details
         * 
         * @return array last payout details
         */
        function get_last_payout_details($amount = true, $date = true, $gateway = true) {
            global $wpdb;

            $last_payout_details = array(
                'amount' => '',
                'date' => '',
                'gateway' => ''
            );

            $payout_details_query = "SELECT * FROM " . get_saff_tablename('payouts') . " WHERE 1";

            // If no affiliate find out last payout details from payout to all affiliates
            // If more than one affiliate find out last payout details from payout to all those affiliates

            if (!empty($this->affiliate_ids)) {
                $payout_details_query .= $wpdb->prepare(" AND affiliate_id IN ( " . str_repeat('%d,', ( count($this->affiliate_ids) - 1)) . "%d )", $this->affiliate_ids);
            } else {
                $payout_details_query .= $wpdb->prepare(" AND affiliate_id != 0");
            }

            if (!empty($this->from)) {
                $payout_details_query .= $wpdb->prepare(" AND datetime >= %s", $this->from . ' 00:00:00');
            }

            if (!empty($this->to)) {
                $payout_details_query .= $wpdb->prepare(" AND datetime <= %s", $this->to . ' 23:59:59');
            }

            $payout_details_query .= " ORDER BY payout_id DESC";


            $payout_details = $wpdb->get_row($payout_details_query, "ARRAY_A");

            // Return only amount, date & gateway only when asked

            if ($amount) {
                $last_payout_details['amount'] = (!empty($payout_details['amount']) ) ? $payout_details['amount'] : '';
            }

            if ($date) {
                $last_payout_details['date'] = (!empty($payout_details['datetime']) ) ? date('d-M-Y', strtotime($payout_details['datetime'])) : '';
            }

            if ($gateway) {
                $last_payout_details['gateway'] = (!empty($payout_details['payment_gateway']) ) ? $payout_details['payment_gateway'] : '';
            }

            // Hook to add more details about last payout

            $last_payout_details = apply_filters('saff_last_payout_details', $last_payout_details, $payout_details);

            return $last_payout_details;
        }

        /**
         * Function to get affiliate's display_name
         * 
         * @return array where key is user id & value is their display name
         */
        function get_affiliates_display_names() {
            global $wpdb;

            $affiliates_display_names = array();

            if (!empty($this->affiliate_ids)) {

                // for one affiliate, use this method to get display name
                if (count($this->affiliate_ids) == 1) {

                    $user_data = get_user_by('id', $this->affiliate_ids[0]);

                    if ($user_data !== false) {

                        $affiliates_display_names[$this->affiliate_ids[0]] = (!empty($user_data->display_name) ) ? $user_data->display_name : '';

                        return $affiliates_display_names;
                    }
                }

                $affiliates_display_name_query = $wpdb->prepare("SELECT ID,
																	display_name
																	FROM {$wpdb->users}
																	WHERE ID IN ( " . str_repeat('%d,', ( count($this->affiliate_ids) - 1)) . "%d )", $this->affiliate_ids);


                $results = $wpdb->get_results($affiliates_display_name_query, 'ARRAY_A');

                if ($results) {

                    foreach ($results as $result) {

                        $affiliates_display_names[$result['ID']] = $result['display_name'];
                    }
                }
            }

            return $affiliates_display_names;
        }

    }

}