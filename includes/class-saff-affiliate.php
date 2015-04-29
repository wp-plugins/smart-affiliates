<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Affiliate')) {

    class Saff_Affiliate extends WP_User {

        /**
         * Checks if an affiliate id is from a currently valid affiliate.
         * @param string $affiliate_id the affiliate id
         * @return returns the affiliate id if valid, otherwise FALSE
         */
        
        function is_valid() {
           return saff_is_user_affiliate($this);
        }
        // Update Record Hit of an affiliate
    }
}