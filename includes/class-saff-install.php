<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Install')) {

    class Saff_Install {
        
        function __construct() {
            $this->install();
        }

        function install() {
            $this->create_tables();
        }

        function create_tables() {
            global $wpdb;
                
            $collate = '';

            if ($wpdb->has_cap('collation')) {
                if (!empty($wpdb->charset)) {
                    $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
                }
                if (!empty($wpdb->collate)) {
                    $collate .= " COLLATE $wpdb->collate";
                }
            }

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $saff_tables = "
							CREATE TABLE IF NOT EXISTS {$wpdb->prefix}saff_hits (
							  	affiliate_id bigint(20) UNSIGNED NOT NULL DEFAULT '0',
								datetime datetime NOT NULL,
								ip int(10) UNSIGNED DEFAULT NULL,
								user_id bigint(20) UNSIGNED DEFAULT NULL,
								count int DEFAULT 1,
								type varchar(10) DEFAULT NULL
							) $collate;
							CREATE TABLE IF NOT EXISTS {$wpdb->prefix}saff_referrals (
							  	referral_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
								affiliate_id bigint(20) unsigned NOT NULL default '0',
								post_id bigint(20) unsigned NOT NULL default '0',
								datetime datetime NOT NULL,
								description varchar(5000),
								ip int(10) unsigned default NULL,
								user_id bigint(20) unsigned default NULL,
								amount decimal(18,2) default NULL,
								currency_id char(3) default NULL,
								data longtext default NULL,
								status varchar(10) NOT NULL DEFAULT 'pending',
								type varchar(10) NULL,
								reference varchar(100) DEFAULT NULL,
								PRIMARY KEY  (referral_id),
								KEY saff_referrals_apd (affiliate_id, post_id, datetime),
								KEY saff_referrals_da (datetime, affiliate_id),
								KEY saff_referrals_sda (status, datetime, affiliate_id),
								KEY saff_referrals_tda (type, datetime, affiliate_id),
								KEY saff_referrals_ref (reference(20))
							) $collate;
							CREATE TABLE IF NOT EXISTS {$wpdb->prefix}saff_payouts (
							  	payout_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
								affiliate_id bigint(20) unsigned NOT NULL default '0',
								datetime datetime NOT NULL,
								amount decimal(18,2) default NULL,
								currency char(3) default NULL,
								payout_notes varchar(5000),
								payment_gateway varchar(20) NULL,
								receiver varchar(50) NULL,
								type varchar(10) NULL,
								PRIMARY KEY  (payout_id),
								KEY saff_payouts_da (datetime, affiliate_id),
								KEY saff_payouts_tda (type, datetime, affiliate_id)
							) $collate;
							CREATE TABLE IF NOT EXISTS {$wpdb->prefix}saff_payout_orders (
							  	payout_id bigint(20) UNSIGNED NOT NULL,
								post_id bigint(20) unsigned NOT NULL default '0',
								amount decimal(18,2) default NULL,
								KEY saff_payout_orders (payout_id, post_id)
							) $collate;
							";
                                                        
            dbDelta($saff_tables);
        }

    }

}

return new Saff_Install();