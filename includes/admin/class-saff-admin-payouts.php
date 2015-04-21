<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Admin_Payouts')) {

    class Saff_Admin_Payouts {
        /*
         * Track payouts. 
         * 
         * $data
         *  - affiliate_id
         *  - amount
         *  - Curency
         *  - payput_notes
         *  - payment_gateway
         *  - receiver
         *  - type
         *  - orders
         */

        static function record_payouts($data) {
            global $wpdb;

            extract($data);

            $table = $table = get_saff_tablename('payouts');

            if (!empty($affiliate_id)) {
                $columns[] = 'affiliate_id';
                $formats[] = '%d';
                $values[] = $affiliate_id;

                $columns[] = 'datetime';
                $formats[] = '%s';
                $values[] = date('Y-m-d H:i:s', time());

                $columns[] = 'amount';
                $formats[] = '%d';
                $values[] = $amount;

                $columns[] = 'currency';
                $formats[] = '%s';
                $values[] = $currency;

                $columns[] = 'payout_notes';
                $formats[] = '%s';
                $values[] = $payout_notes;

                $columns[] = 'payment_gateway';
                $formats[] = '%s';
                $values[] = $payment_gateway;

                $columns[] = 'receiver';
                $formats[] = '%s';
                $values[] = $receiver;

                $columns[] = 'type';
                $formats[] = '%s';
                $values[] = $type;


                $columns_str = "( " . implode(', ', $columns) . " ) ";
                $formats_str = "( " . implode(', ', $formats) . " ) ";

                $query = $wpdb->prepare("INSERT INTO $table $columns_str VALUES $formats_str ", $values);
                $wpdb->query($query);

                $last_inserted_payout_id = $wpdb->insert_id;
                if ($last_inserted_payout_id > 0 && count($orders) > 0) {
                    $table = get_saff_tablename('payout_orders');
                    $query = "INSERT INTO $table (`payout_id`, `post_id`, `amount`) VALUES ( '%d', '%d', '%d' )";
                    foreach ($orders as $order_id => $amount) {
                        $prepared_query = $wpdb->prepare($query, $last_inserted_payout_id, $order_id, $amount);
                        $wpdb->query($prepared_query);
                    }
                }
            }
        }
    }
}