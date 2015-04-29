<?php
/*
Plugin Name: Smart Affiliates
Plugin URI: http://www.storeapps.org/product/smart-affiliates
Description: <strong>Smart Affiliates</strong> is the best affiliate management plugin for WooCommerce and WordPress. Track, manage and payout affiliate commissions easily.
Version: 0.2
Author: Store Apps
Author URI: http://www.storeapps.org/
Copyright (c) 2015 Store Apps All rights reserved.
*/

if (!defined('ABSPATH'))
    exit;

register_activation_hook(__FILE__, 'smart_affiliates_activate');
add_action('admin_init', 'saff_redirect');

function smart_affiliates_activate() {
    
    
    require_once 'includes/class-saff-install.php';
    add_option('saff_default_commission_status', 'unpaid');
    add_option('saff_do_activation_redirect', true);
    add_option('saff_pname', 'ref');
}

function saff_redirect() {
    if (get_option('saff_do_activation_redirect', false)) {
        delete_option('saff_do_activation_redirect');
        wp_redirect( admin_url( 'admin.php?page=smart_affiliates_documentation' ) );
        exit;
    }
}

if (!class_exists('Smart_Affiliates')) {

    final class Smart_Affiliates {

        function __construct() {

            $a = array();   
            foreach ($a as $key => $value) {
                    echo $key;
            }
            
            $this->constants();
            $this->includes();

            if (is_admin()) {
                add_action('admin_menu', array($this, 'add_saff_admin_menu'));
            }

            add_filter('query_vars', array($this, 'saff_query_vars'), 10000);
            add_action('parse_request', array($this, 'saff_parse_request'));
        }

        function constants() {

            //$saff_currency_symbol = ( function_exists( 'get_woocommerce_currency_symbol' ) ) ? get_woocommerce_currency_symbol() : $this->get_currency_symbol_from_code( get_option('woocommerce_currency') );
            $saff_currency_symbol = get_woocommerce_currency_symbol();

            define('SAFF_TEXT_DOMAIN', 'smart_affiliates');
            define('SAFF_AFFILIATES_COOKIE_NAME', 'smart_affiliates');
            define('SAFF_TABLE_PREFIX', 'saff_');
            define('SAFF_PLUGIN_FILE', __FILE__);
            define('SAFF_PLUGIN_BASENAME', plugin_basename(SAFF_PLUGIN_FILE));
            define('SAFF_PLUGIN_DIR', dirname(plugin_basename(SAFF_PLUGIN_FILE)));

            // Following Commented code doesn't work for symbolic link.
            define('SAFF_PLUGIN_URL', plugins_url(SAFF_PLUGIN_DIR));
            // define('SAFF_PLUGIN_URL', plugins_url(basename(__DIR__)));

            define('SAFF_PNAME', get_option('saff_pname'));

            define('SAFF_COOKIE_TIMEOUT_BASE', 86400);
            define('SAFF_REGEX_PATTERN', 'affiliates/([^/]+)/?$');

            define('SAFF_CURRENCY', $saff_currency_symbol);
            define('SAFF_DEFAULT_COMMISSION_STATUS', get_option('saff_default_commission_status'));
            define('SAFF_AJAX_SECURITY', 'smart_affiliates_ajax_call');

            define('SAFF_REFERRAL_STATUS_PENDING', 'pending');
            define('SAFF_REFERRAL_STATUS_PAID', 'paid');
            define('SAFF_REFERRAL_STATUS_UNPAID', 'unpaid');
            define('SAFF_REFERRAL_STATUS_REJECTED', 'rejected');
            
            
        }
        
        function admin_menus() {
            
        }

        function includes() {
            include_once 'smart-affiliates-functions.php';

            if (is_admin()) {
                include_once 'includes/migration/class-saff-migrate-affiliates.php';
                include_once 'includes/admin/class-saff-admin-settings.php';
                include_once 'includes/admin/class-saff-admin-affiliates.php';
                include_once 'includes/gateway/paypal/class-saff-paypal.php';
                include_once 'includes/admin/class-saff-admin-dashboard.php';
                include_once 'includes/admin/class-saff-admin-affiliate.php';
                include_once 'includes/admin/class-saff-admin-payouts.php';
                include_once 'includes/admin/class-saff-admin-docs.php';
            }

            include_once 'includes/integration/woocommerce/class-integration-woocommerce.php';
            include_once 'includes/class-saff-affiliate.php';
            include_once 'includes/class-saff-referral.php';
            include_once 'includes/integration/woocommerce/compatibility/class-wc-compatibility.php';
            include_once 'includes/integration/woocommerce/compatibility/class-wc-compatibility-2-2.php';
            include_once 'includes/integration/woocommerce/compatibility/class-wc-compatibility-2-3.php';
            
            define('SAFF_IS_WC_GREATER_THAN_22', version_compare(SA_WC_Compatibility::get_wc_version(), '2.2' , '>='));
        }

        static function wc_compat() {
            return 'SA_WC_Compatibility_2_2';
        }

        function get_currency_symbol_from_code($currency_code = '') {

            $currency_symbol = '';

            if (!empty($currency_code)) {

                switch ($currency_code) {
                    case 'BRL' :
                        $currency_symbol = '&#82;&#36;';
                        break;
                    case 'AUD' :
                    case 'CAD' :
                    case 'MXN' :
                    case 'NZD' :
                    case 'HKD' :
                    case 'SGD' :
                    case 'USD' :
                        $currency_symbol = '&#36;';
                        break;
                    case 'EUR' :
                        $currency_symbol = '&euro;';
                        break;
                    case 'CNY' :
                    case 'RMB' :
                    case 'JPY' :
                        $currency_symbol = '&yen;';
                        break;
                    case 'RUB' :
                        $currency_symbol = '&#1088;&#1091;&#1073;.';
                        break;
                    case 'KRW' : $currency_symbol = '&#8361;';
                        break;
                    case 'TRY' : $currency_symbol = '&#84;&#76;';
                        break;
                    case 'NOK' : $currency_symbol = '&#107;&#114;';
                        break;
                    case 'ZAR' : $currency_symbol = '&#82;';
                        break;
                    case 'CZK' : $currency_symbol = '&#75;&#269;';
                        break;
                    case 'MYR' : $currency_symbol = '&#82;&#77;';
                        break;
                    case 'DKK' : $currency_symbol = '&#107;&#114;';
                        break;
                    case 'HUF' : $currency_symbol = '&#70;&#116;';
                        break;
                    case 'IDR' : $currency_symbol = 'Rp';
                        break;
                    case 'INR' : $currency_symbol = '&#8377;';
                        break;
                    case 'ILS' : $currency_symbol = '&#8362;';
                        break;
                    case 'PHP' : $currency_symbol = '&#8369;';
                        break;
                    case 'PLN' : $currency_symbol = '&#122;&#322;';
                        break;
                    case 'SEK' : $currency_symbol = '&#107;&#114;';
                        break;
                    case 'CHF' : $currency_symbol = '&#67;&#72;&#70;';
                        break;
                    case 'TWD' : $currency_symbol = '&#78;&#84;&#36;';
                        break;
                    case 'THB' : $currency_symbol = '&#3647;';
                        break;
                    case 'GBP' : $currency_symbol = '&pound;';
                        break;
                    case 'RON' : $currency_symbol = 'lei';
                        break;
                    default : $currency_symbol = '';
                        break;
                }
            }

            return $currency_symbol;
        }

        function add_saff_admin_menu() {
            add_object_page(sprintf(__('Dashboard %s Smart Affiliates', 'translate_saff'), '&rsaquo;'), __('Smart Affiliates', 'translate_saff'), 'smart_affiliates', 'smart_affiliates', 'Saff_Admin_Dashboard::saff_dashboard_page', 'dashicons-star-filled');
            add_submenu_page('smart_affiliates', sprintf(__('Dashboard %s Smart Affiliates', 'translate_saff'), '&rsaquo;'), __('Dashboard', 'translate_saff'), 'manage_options', 'smart_affiliates_dashboard', 'Saff_Admin_Dashboard::saff_dashboard_page');
            add_submenu_page('smart_affiliates', sprintf(__('Settings %s Smart Affiliates', 'translate_saff'), '&rsaquo;'), __('Settings', 'translate_saff'), 'manage_options', 'smart_affiliates_settings', 'Saff_Admin_Settings::saff_settings_page');
            add_submenu_page('smart_affiliates', sprintf(__('Docs & Support', 'translate_saff'), '&rsaquo;'), __('Docs & Support', 'translate_saff'), 'manage_options', 'smart_affiliates_documentation', 'Saff_Admin_Docs::saff_docs');
        }

        function saff_query_vars($query_vars) {
            $smart_affiliates_pname = ( defined('SAFF_PNAME') ) ? SAFF_PNAME : 'ref';
            $pname = get_option('saff_pname', $smart_affiliates_pname);

            $affiliates_pname = ( defined('AFFILIATES_PNAME') ) ? AFFILIATES_PNAME : 'affiliates';
            $migrated_pname = get_option('saff_migrated_pname', $affiliates_pname);

            
            $query_vars[] = $pname;
            $query_vars[] = $migrated_pname;

            return $query_vars;
        }

        function saff_parse_request(&$wp) {
            global $wpdb;

            $saff_affiliates_users = get_saff_tablename('affiliates_users');

            $smart_affiliates_pname = ( defined('SAFF_PNAME') ) ? SAFF_PNAME : 'ref';
            $pname = get_option('saff_pname', $smart_affiliates_pname);

            $affiliates_pname = ( defined('AFFILIATES_PNAME') ) ? AFFILIATES_PNAME : 'affiliates';
            $migrated_pname = get_option('saff_migrated_pname', $affiliates_pname);

            
            if (empty($wp->query_vars[$pname])) {
                return;
            }

            // Handle Older affiliates link through migrated pname.
            if (isset($wp->query_vars[$migrated_pname])) {
                $id = trim($wp->query_vars[$migrated_pname]);
                $affiliate_id = get_user_id_based_on_affiliate_id($id);
            } elseif (isset($wp->query_vars[$pname])) {
                $affiliate_id = trim($wp->query_vars[$pname]);
            } else {
                $affiliate_id = 0;
            }

            
            if ($affiliate_id != 0) {
                $affiliate = new Saff_Affiliate($affiliate_id);
                if ($affiliate instanceof Saff_Affiliate && $affiliate->ID != 0) {
                    $is_valid_affiliate = $affiliate->is_valid();
                    var_dump($is_valid_affiliate);
                    die('Yes');
                    
                    if ($is_valid_affiliate) {
                        $encoded_id = saff_encode_affiliate_id($affiliate_id);
                        $days = 2;
                        if ($days > 0) {
                            $expire = time() + SAFF_COOKIE_TIMEOUT_BASE * $days;
                        } else {
                            $expire = 0;
                        }
                        setcookie(
                                SAFF_AFFILIATES_COOKIE_NAME, $encoded_id, $expire, SITECOOKIEPATH, COOKIE_DOMAIN
                        );
                        include_once 'includes/frontend/class-saff-hit.php';
                        Saff_Hit::record_hit($affiliate_id);
                    }
                }

                unset($wp->query_vars[$pname]); // we use this to avoid ending up on the blog listing page
                // use a redirect so that we end up on the desired url without the affiliate id dangling on the url
                $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $current_url = remove_query_arg($pname, $current_url);
                $current_url = preg_replace('#' . str_replace(SAFF_PNAME, $pname, SAFF_REGEX_PATTERN) . '#', '', $current_url);
                // note that we must use delimiters other than / as these are used in AFFILIATES_REGEX_PATTERN
                $status = apply_filters('affiliates_redirect_status_code', 302);
                $status = intval($status);
                switch ($status) {
                    case 300 :
                    case 301 :
                    case 302 :
                    case 303 :
                    case 304 :
                    case 305 :
                    case 306 :
                    case 307 :
                        break;
                    default :
                        $status = 302;
                }
                wp_redirect($current_url, $status);
                exit;
            }
        }

    }

}

add_action('plugins_loaded', 'initialize_smart_affiliates');

// Load smart affiliates only if woocommerce is activated
function initialize_smart_affiliates() {

    $active_plugins = (array) get_option('active_plugins', array());

    if (is_multisite()) {
        $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
    }

    if (( in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) )) {
        new Smart_Affiliates();
    }
}

