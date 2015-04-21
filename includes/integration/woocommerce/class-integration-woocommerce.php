<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Integration_Woocommerce')) {

    class Saff_Integration_Woocommerce {

    	function __construct() {

            add_filter( 'saff_storewide_sales', array( $this, 'woocommerce_storewide_sales' ), 10, 2 );
            add_filter( 'saff_completed_affiliates_sales', array( $this, 'woocommerce_affiliates_sales' ), 10, 2 );
            add_filter( 'saff_affiliates_refund', array( $this, 'woocommerce_affiliates_refund' ), 10, 2 );
            add_filter( 'saff_all_customer_ids', array( $this, 'woocommerce_all_customer_ids' ), 10, 2 );
            add_filter( 'saff_order_details', array( $this, 'woocommerce_order_details' ), 10, 2 );
            if ( saff_is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
                add_filter( 'woocommerce_subscriptions_renewal_order_items', array( $this, 'saff_modify_renewal_order' ), 10, 5 );
            }
    	}

    	function woocommerce_storewide_sales( $storewide_sales = 0, $post_ids = array() ) {

            global $wpdb;
            
            // Backward Compatibility
            
            if(SAFF_IS_WC_GREATER_THAN_22) {
                $woocommerce_sales_query = "SELECT SUM( postmeta.meta_value ) AS order_total 
                                            FROM {$wpdb->posts} AS posts 
                                            LEFT JOIN {$wpdb->postmeta} AS postmeta 
                                                ON ( posts.ID = postmeta.post_id AND postmeta.meta_key LIKE '_order_total' ) 
                                            WHERE posts.post_type LIKE 'shop_order' AND posts.post_status = 'wc-completed'";

                if ( ! empty( $post_ids ) ) {
                        $woocommerce_sales_query .= $wpdb->prepare( " AND posts.ID IN ( " . str_repeat( '%d,', ( count( $post_ids ) - 1 ) ) . "%d )", $post_ids );
                }

                $woocommerce_sales = $wpdb->get_var( $woocommerce_sales_query );
            } else {
                $completed_term = get_term_by( 'slug', 'completed', 'shop_order_status', 'ARRAY_A' );   // It'll return net storewide sales
                $completed_term_taxonomy_id = ( ! empty( $completed_term['term_taxonomy_id'] ) ) ? $completed_term['term_taxonomy_id'] : '';

                $woocommerce_sales_query = "SELECT SUM( postmeta.meta_value ) AS order_total 
                                            FROM {$wpdb->posts} AS posts 
                                            LEFT JOIN {$wpdb->postmeta} AS postmeta 
                                                ON ( posts.ID = postmeta.post_id AND postmeta.meta_key LIKE '_order_total' ) 
                                            LEFT JOIN {$wpdb->term_relationships} AS tr ON ( tr.object_id = postmeta.post_id ) 
                                            WHERE posts.post_type LIKE 'shop_order' AND posts.post_status = 'publish'";

                if ( ! empty( $post_ids ) ) {
                        $woocommerce_sales_query .= $wpdb->prepare( " AND posts.ID IN ( " . str_repeat( '%d,', ( count( $post_ids ) - 1 ) ) . "%d )", $post_ids );
                }

                if ( ! empty( $completed_term_taxonomy_id ) ) {
                    $woocommerce_sales_query .= $wpdb->prepare( " AND tr.term_taxonomy_id IN ( %d )", $completed_term_taxonomy_id );
                }

                $woocommerce_sales = $wpdb->get_var( $woocommerce_sales_query );
            
            }
            
            if ( ! empty( $woocommerce_sales ) ) {
                        $storewide_sales = $storewide_sales + $woocommerce_sales;
                    }

            return $storewide_sales;

    	}

        function woocommerce_affiliates_sales( $affiliates_sales = 0, $post_ids = array() ) {
            // Calling storewide_sales because post ids are already filtered order ids via affiliates
            return $this->woocommerce_storewide_sales( $affiliates_sales, $post_ids );
        }

        function woocommerce_affiliates_refund( $affiliates_refund = 0, $post_ids = array() ) {

            global $wpdb;
            
            // Backward Compatibility
            if(SAFF_IS_WC_GREATER_THAN_22) {
                $woocommerce_refunds_query = "SELECT SUM( postmeta.meta_value ) AS order_total 
                                                    FROM {$wpdb->posts} AS posts 
                                                    LEFT JOIN {$wpdb->postmeta} AS postmeta 
                                                    ON ( posts.ID = postmeta.post_id AND postmeta.meta_key LIKE '_order_total' ) 
                                                    WHERE posts.post_type LIKE 'shop_order' AND posts.post_status = 'wc-refunded'";

                if ( ! empty( $post_ids ) ) {
                    $woocommerce_refunds_query .= $wpdb->prepare( " AND posts.ID IN ( " . str_repeat( '%d,', ( count( $post_ids ) - 1 ) ) . "%d )", $post_ids );
                }

                $woocommerce_refunds = $wpdb->get_var( $woocommerce_refunds_query );
                
            } else {
            
                $refunded_term = get_term_by( 'slug', 'refunded', 'shop_order_status', 'ARRAY_A' );
                $refunded_term_taxonomy_id = ( ! empty( $refunded_term['term_taxonomy_id'] ) ) ? $refunded_term['term_taxonomy_id'] : '';

                $woocommerce_refunds_query = "SELECT SUM( postmeta.meta_value ) AS order_total 
                                                    FROM {$wpdb->posts} AS posts 
                                                    LEFT JOIN {$wpdb->postmeta} AS postmeta 
                                                        ON ( posts.ID = postmeta.post_id AND postmeta.meta_key LIKE '_order_total' ) 
                                                    LEFT JOIN {$wpdb->term_relationships} AS tr ON ( tr.object_id = postmeta.post_id ) 
                                                    WHERE posts.post_type LIKE 'shop_order' AND posts.post_status = 'publish'";

                if ( ! empty( $post_ids ) ) {
                    $woocommerce_refunds_query .= $wpdb->prepare( " AND posts.ID IN ( " . str_repeat( '%d,', ( count( $post_ids ) - 1 ) ) . "%d )", $post_ids );
                }

                if ( ! empty( $refunded_term_taxonomy_id ) ) {
                    $woocommerce_refunds_query .= $wpdb->prepare( " AND tr.term_taxonomy_id IN ( %d )", $refunded_term_taxonomy_id );
                }

                $woocommerce_refunds = $wpdb->get_var( $woocommerce_refunds_query );
            }
            
            if ( ! empty( $woocommerce_refunds ) ) {
                $affiliates_refund = $affiliates_refund + $woocommerce_refunds;
            }

            return $affiliates_refund;

        }

        function woocommerce_all_customer_ids( $all_customer_ids = array(), $datetime = array() ) {

            global $wpdb;

            // Backward Compatibility
            if(SAFF_IS_WC_GREATER_THAN_22) {
                $woocommerce_customer_ids_query = "SELECT DISTINCT postmeta.meta_value AS customer_ids 
                                                        FROM {$wpdb->postmeta} AS postmeta
                                                        LEFT JOIN {$wpdb->posts} AS posts 
                                                        ON ( posts.ID = postmeta.post_id AND postmeta.meta_key LIKE '_customer_user' ) 
                                                        WHERE posts.post_type LIKE 'shop_order' AND posts.post_status IN ('wc-completed', 'wc-processing')";

                if ( ! empty( $datetime['from'] ) ) {
                    $woocommerce_customer_ids_query .= $wpdb->prepare( " AND posts.post_date >= %s", date('Y-m-d', strtotime($datetime['from'])) . ' 00:00:00' );
                }

                if ( !empty( $datetime['to'] ) ) {
                    $woocommerce_customer_ids_query .= $wpdb->prepare( " AND posts.post_date <= %s", date('Y-m-d', strtotime($datetime['to'])) . ' 23:59:59' );
                }
                $woocommerce_customer_ids = $wpdb->get_col( $woocommerce_customer_ids_query );
            } else {
            
                $completed_term = get_term_by( 'slug', 'completed', 'shop_order_status', 'ARRAY_A' );   // It'll return net storewide sales
                $completed_term_taxonomy_id = ( ! empty( $completed_term['term_taxonomy_id'] ) ) ? $completed_term['term_taxonomy_id'] : '';

                $processing_term = get_term_by( 'slug', 'processing', 'shop_order_status', 'ARRAY_A' );   // It'll return net storewide sales
                $processing_term_taxonomy_id = ( ! empty( $processing_term['term_taxonomy_id'] ) ) ? $processing_term['term_taxonomy_id'] : '';

                $target_term_taxonomy_ids = array( $completed_term_taxonomy_id, $processing_term_taxonomy_id );

                $woocommerce_customer_ids_query = "SELECT DISTINCT postmeta.meta_value AS customer_ids 
                                                        FROM {$wpdb->postmeta} AS postmeta
                                                        LEFT JOIN {$wpdb->posts} AS posts 
                                                            ON ( posts.ID = postmeta.post_id AND postmeta.meta_key LIKE '_customer_user' ) 
                                                        LEFT JOIN {$wpdb->term_relationships} AS tr ON ( tr.object_id = postmeta.post_id ) 
                                                        WHERE posts.post_type LIKE 'shop_order' AND posts.post_status = 'publish'";

                if ( ! empty( $datetime['from'] ) ) {
                    $woocommerce_customer_ids_query .= $wpdb->prepare( " AND posts.post_date >= %s", date('Y-m-d', strtotime($datetime['from'])) . ' 00:00:00' );
                }

                if ( !empty( $datetime['to'] ) ) {
                    $woocommerce_customer_ids_query .= $wpdb->prepare( " AND posts.post_date <= %s", date('Y-m-d', strtotime($datetime['to'])) . ' 23:59:59' );
                }

                if ( ! empty( $target_term_taxonomy_ids ) ) {
                    $woocommerce_customer_ids_query .= $wpdb->prepare( " AND tr.term_taxonomy_id IN ( %d )", $target_term_taxonomy_ids );
                }

                $woocommerce_customer_ids = $wpdb->get_col( $woocommerce_customer_ids_query );

            }
            
            if ( ! empty( $woocommerce_customer_ids ) ) {
                $all_customer_ids = array_merge( $all_customer_ids, $woocommerce_customer_ids );
            }

            return $all_customer_ids;
        }

        function woocommerce_order_details( $affiliates_order_details = array(), $order_ids = array() ) {
            global $wpdb;
            if ( ! empty( $affiliates_order_details ) ) {
                
                // For woocommerce > 2.2
                if(SAFF_IS_WC_GREATER_THAN_22) {
                    if(count($order_ids) > 0) {
                        $query = " SELECT ID, post_status FROM {$wpdb->posts} WHERE ID IN ( " . implode(',', $order_ids) . ")";
                        $orders = $wpdb->get_results($query, 'ARRAY_A');
                        $order_id_to_status = array();
                        foreach ($orders  as $order) {
                            //$order_id_to_status[$order['ID']] = ucfirst(str_replace('wc-', '', $order['post_status']));
                            $order_id_to_status[$order['ID']] = $order['post_status'];
                        }
                    }
                } else {    
                    $object_terms = array();

                    if ( ! empty( $order_ids ) ) {
                        $object_terms = wp_get_object_terms( $order_ids, 'shop_order_status', array( 'fields' => 'all_with_object_id' ) );
                    }

                    if ( ! empty( $object_terms ) ) {

                        $order_id_to_status = array();

                        foreach ( $object_terms as $objects ) {
                            $order_id_to_status[ $objects->object_id ] = $objects->slug;
                        }
                    }
                }
                
                foreach ( $affiliates_order_details as $order_id => $order_details ) {
                     $affiliates_order_details[ $order_id ]['order_status'] = isset($order_id_to_status[ $order_id ]) ? $order_id_to_status[ $order_id ] : 'wc-deleted' ;
                }
            }

            return $affiliates_order_details;

        }

        function saff_modify_renewal_order( $order_items, $original_order_id, $renewal_order_id, $product_id, $new_order_role ) {
            $is_commission_recorded = get_post_meta( $renewal_order_id, 'is_commission_recorded', true );
            if ( $is_commission_recorded === 'yes' ) {
                if ( get_option( 'is_recurring_commission' ) === 'yes' ) {
                    update_post_meta( $renewal_order_id, 'is_commission_recorded', 'no' );
                }
            }
            return $order_items;
        }
        
        

    }

}

new Saff_Integration_Woocommerce();