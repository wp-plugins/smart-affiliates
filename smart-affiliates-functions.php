<?php

function saff_encode_affiliate_id($affiliate_id) {
//global $affiliates_options;
//$encoded_id = null;

    /* Provide Encoding
      $id_encoding = get_option( 'aff_id_encoding', AFFILIATES_NO_ID_ENCODING );
      switch ( $id_encoding ) {
      case AFFILIATES_MD5_ID_ENCODING :
      $encoded_id = md5( $affiliate_id );
      break;
      default:
      $encoded_id = $affiliate_id;
      } */

    return $affiliate_id;
}

function get_saff_commission_statuses() {
    
    return array (
        //SAFF_REFERRAL_STATUS_PENDING => 'Prending',
        SAFF_REFERRAL_STATUS_PAID => 'Paid',
        SAFF_REFERRAL_STATUS_UNPAID => 'Unpaid',
        SAFF_REFERRAL_STATUS_REJECTED => 'Rejected'
    );
    
}

function get_saff_tablename($name) {
    global $wpdb;
    return $wpdb->prefix . SAFF_TABLE_PREFIX . $name;
}

function get_referrer_id() {
    $affiliate_id = false;
    if (isset($_COOKIE[SAFF_AFFILIATES_COOKIE_NAME])) {
        $affiliate_id = trim($_COOKIE[SAFF_AFFILIATES_COOKIE_NAME]);
    }
    return $affiliate_id;
}

function get_saff_date_range($for = '', $format = 'd-M-Y') {
    if (empty($for)) {
        return array();
    }
    $today = date($format);
    $date = $date_from = $date_to = new DateTime($today);
    switch ($for) {
        case 'today':
            $from_date = $to_date = $today;
            break;

        case 'yesterday':
            $from_date = $to_date = date($format, strtotime("-1 second", strtotime('today')));
            break;

        case 'this_week':
            $from_date = date($format, mktime(0, 0, 0, date("m"), date("d") - intval(get_option('start_of_week')) - 1, date("Y")));
            $to_date = $today;
            break;

        case 'last_week':
            $from_date = date($format, mktime(0, 0, 0, date("m"), date("d") - intval(get_option('start_of_week')) - 8, date("Y")));
            $to_date = date($format, mktime(0, 0, 0, date("m"), date("d") - intval(get_option('start_of_week')) - 2, date("Y")));
            break;

        case 'this_month':
            $from_date = date($format, mktime(0, 0, 0, date("n"), 1, date("Y")));
            $to_date = $today;
            break;

        case 'last_month':
            $from_date = date($format, mktime(0, 0, 0, date("n") - 1, 1, date("Y")));
            $to_date = date($format, strtotime("-1 second", strtotime(date("m") . '/01/' . date("Y") . ' 00:00:00')));
            break;

        case 'three_months':
            $from_date = date($format, mktime(0, 0, 0, date("n") - 2, 1, date("Y")));
            $to_date = $today;
            break;

        case 'six_months':
            $from_date = date($format, mktime(0, 0, 0, date("n") - 5, 1, date("Y")));
            $to_date = $today;
            break;

        case 'this_year':
            $from_date = date($format, mktime(0, 0, 0, 1, 1, date("Y")));
            $to_date = $today;
            break;

        case 'last_year':
            $from_date = date($format, mktime(0, 0, 0, 1, 1, date("Y") - 1));
            $to_date = date($format, strtotime("-1 second", strtotime('01/01/' . date("Y") . ' 00:00:00')));
            break;
    }

    return array(
        'from' => $from_date,
        'to' => $to_date
    );
}

function get_user_id_based_on_affiliate_id($affiliate_id) {
    global $wpdb;
    $saff_affiliates_users = get_saff_tablename('affiliates_users');
    if ( is_numeric( $affiliate_id ) ) {
        $result = $wpdb->get_var("SELECT user_id FROM {$saff_affiliates_users} WHERE affiliate_id = {$affiliate_id} ");
    } else {
        $result = 0;
        $results = $wpdb->get_results("SELECT user_id, MD5( affiliate_id ) AS affiliate_id_md5 FROM {$saff_affiliates_users}", "ARRAY_A");
        $user_to_affiliate = array();
        foreach ( $results as $result ) {
            $user_to_affiliate[ $result['user_id'] ] = $result['affiliate_id_md5'];
        }
        $user_id = array_search( $affiliate_id, $user_to_affiliate );
        if ( $user_id !== false ) {
            $result = $user_id;
        }
    }
    if (!empty($result)) {
        $affiliate_id = $result;
    }
    
    return $affiliate_id;
}

function get_affiliate_id_based_on_user_id($user_id) {
    global $wpdb;
    $saff_affiliates_users = get_saff_tablename('affiliates_users');
    $result = $wpdb->get_var("SELECT affiliate_id FROM {$saff_affiliates_users} WHERE user_id = {$user_id} ");
    if (!empty($result)) {
        $user_id = $result;
    }
    
    return $user_id;
}

function saff_is_plugin_active( $plugin = '' ) {
    if ( ! empty( $plugin ) ) {
        if ( function_exists( 'is_plugin_active' ) ) {
            return is_plugin_active( $plugin );
        } else {
            $active_plugins = (array) get_option('active_plugins', array());
            if (is_multisite()) {
                $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
            }
            if ( ( in_array( $plugin, $active_plugins ) || array_key_exists( $plugin, $active_plugins ) ) ) {
                return true;
            }
        }
    }
    return false;
}


/*
 * Format price value
 */
function saff_format_price( $price, $decimals = null, $decimal_separator = null, $thousand_separator = null ) {
    
    if(empty($decimals)) {
        $decimals = saff_get_price_decimals();
    }    
    
    if(empty($decimal_separator)) {
        $decimal_separator = saff_get_price_decimal_separator();
    }
    
    if(empty($decimal_separator)) {
        $thousand_separator = saff_get_price_thousand_separator();
    }
    
    
    
    return number_format( $price, $decimals, $decimal_separator, $thousand_separator );
}

/**
 * Return the number of decimals after the decimal point.
 * @return int
 */
function saff_get_price_decimals() {
    return absint( get_option( 'woocommerce_price_num_decimals', 2 ) );
}

/**
 * Return the thousand separator for prices
 * @return string
 */
function saff_get_price_thousand_separator() {
    $separator = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ), ENT_QUOTES );
    return $separator;
}

/**
 * Return the decimal separator for prices
 * @return string
 */
function saff_get_price_decimal_separator() {
    $separator = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ), ENT_QUOTES );
    return $separator ? $separator : '.';
}

function saff_is_user_affiliate( $user ) {
    
    $is_affiliate = false;
    $user_id = 0;
    if (is_int($user)) {
        $user_id = $user;
        $user = new WP_User($user);
    } elseif ($user instanceof WP_User) {
        $user_id = $user->ID;
    }

    if ($user instanceof WP_User) {

        $have_meta = get_user_meta($user_id, 'saff_is_affiliate', true);
        if ($have_meta) {
            $is_affiliate = ($have_meta == 'yes') ? true : false;
        } else {
            if($user instanceof WP_User) {
                $role_name = $user->roles[0];
                $get_affiliate_roles = get_option('affiliate_users_roles');
                $is_affiliate = (in_array($role_name, $get_affiliate_roles)) ? true : false;
            }    
        }
    }
    
    return $is_affiliate;
}

