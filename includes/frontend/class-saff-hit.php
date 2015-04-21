<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Hit')) {

    class Saff_Hit {

        /**
         * Checks if an affiliate id is from a currently valid affiliate.
         * @param string $affiliate_id the affiliate id
         * @return returns the affiliate id if valid, otherwise FALSE
         */
        
        function __construct($aff_id) {
        
        }

        

        static function record_hit($affiliate_id, $now = null, $type = null) {
            global $wpdb;
            // add a hit
            // @todo check/store IPv6 addresses
            //$http_user_agent = $_SERVER['HTTP_USER_AGENT'];

            $table = get_saff_tablename('hits');
            if ($now == null) {
                $now = time();
            }
            $date = date('Y-m-d', $now);
            $time = date('H:i:s', $now);
            $datetime = date('Y-m-d H:i:s', $now);

            $columns = '(affiliate_id, datetime, type';
            $formats = '(%d,%s,%s';
            $values = array($affiliate_id, $datetime, $type);
                
            // TODO: IP address is not tracked for localhost. Need to check whehter it works with public IP or not
            $ip_address = $_SERVER['REMOTE_ADDR'];
            if (PHP_INT_SIZE >= 8) {
                if ($ip_int = ip2long($ip_address)) {
                    $columns .= ',ip';
                    $formats .= ',%d';
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
            if ($user_id = get_current_user_id()) {
                $columns .= ',user_id';
                $formats .= ',%d';
                $values[] = $user_id;
            }
            
            $columns .= ')';
            $formats .= ')';
            
            
            $query = $wpdb->prepare("INSERT INTO $table $columns VALUES $formats ON DUPLICATE KEY UPDATE count = count + 1", $values);
            $wpdb->query($query);
        }
    }
}