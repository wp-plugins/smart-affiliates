<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Admin_Affiliate')) {

    class Saff_Admin_Affiliate {

        /**
         * Checks if an affiliate id is from a currently valid affiliate.
         * @param string $affiliate_id the affiliate id
         * @return returns the affiliate id if valid, otherwise FALSE
         */
        var $aff_id = null;
        
        var $active_affiliate = null;

        function __construct($aff_id = '') {
            global $wpdb;
            
            //add_action( 'show_user_profile', array( $this, 'saff_paypal_email_field' ) );
            //add_action( 'edit_user_profile', array( $this, 'saff_paypal_email_field' ) );
            add_action( 'edit_user_profile', array( $this, 'saff_can_be_affiliate' ) );
            add_action( 'edit_user_profile', array( $this, 'saff_affiliate_link' ) );
            //add_action( 'personal_options_update', array( $this, 'save_saff_paypal_email' ) );
            //add_action( 'edit_user_profile_update', array( $this, 'save_saff_paypal_email' ) );
            add_action( 'edit_user_profile_update', array( $this, 'save_saff_can_be_affiliate' ) );
            

            if ( ! empty( $aff_id ) ) {
                if ($this->active_affiliate !== false) {
                    return $this->active_affiliate;
                }

                return self::get_instance($aff_id);
            }
        }
        
        

        function saff_paypal_email_field( $user ) {
            
            $saff_paypal_email = get_user_meta( $user->ID, 'saff_paypal_email', true );
            $pname = get_option( 'saff_pname' );
            $affiliate_link = add_query_arg( $pname, get_affiliate_id_based_on_user_id( $user->ID ), trailingslashit( site_url() ) );
            
            ?>
            <h3><?php echo __( 'Smart Affiliates', SAFF_TEXT_DOMAIN ); ?></h3>
            <table class="form-table">
                <tr>
                    <th><label for="saff_paypal_email"><?php echo __( 'PayPal Email', SAFF_TEXT_DOMAIN ); ?></label></th>
                    <td><input type="text" id="saff_paypal_email" name="saff_paypal_email" value="<?php echo $saff_paypal_email; ?>" class="regular-text" /><br />
                    <span class="description"><?php echo __( 'Affiliate\'s PayPal Email to process payment', SAFF_TEXT_DOMAIN ); ?></span></td>
                </tr>
            </table>
            <?php  
        }
        
        function saff_affiliate_link( $user ) {
            
            $can_be_affiliate = saff_is_user_affiliate($user);
            
            if($can_be_affiliate) {
                $affiliate_link = add_query_arg( $pname, get_affiliate_id_based_on_user_id( $user->ID ), trailingslashit( site_url() ) );

            
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="saff_affiliate_link"><?php echo __( 'Affiliate URL', SAFF_TEXT_DOMAIN ); ?></label></th>
                    <td><label><?php echo $affiliate_link; ?></label></td>
                </tr>
            </table>
            <?php  }
        }
        
        function saff_can_be_affiliate($user) {
            
            $can_be_affiliate = saff_is_user_affiliate($user);
            
            
            ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="saff_affiliate_link"><?php echo __( 'Is Affiliate?', SAFF_TEXT_DOMAIN ); ?></label></th>
                    <td><input type="checkbox" name="saff_is_affiliate" value="yes" <?php if($can_be_affiliate) { echo 'checked="checked"'; } ?>></td>
                </tr>
            </table>
            <?php 
        }
        
        function save_saff_can_be_affiliate( $user ) {
            
            if(is_int($user)) {
                $user_id = $user;
                $user = new WP_User($user);
            }
            
            if(isset( $_POST['saff_is_affiliate'] ) &&  $_POST['saff_is_affiliate'] == 'yes' ) {
                update_user_meta($user_id, 'saff_is_affiliate', 'yes');
            } else {
                update_user_meta($user_id, 'saff_is_affiliate', 'no');
            }
            
        }

        function save_saff_paypal_email( $user ) {
            
            if ( isset( $_POST['saff_paypal_email'] ) && is_email( $_POST['saff_paypal_email'] ) ) {
                update_user_meta( $user, 'saff_paypal_email', $_POST['saff_paypal_email'] );
            }
        }

        function get_instance($aff_id) {

            if ($aff_id == self::aff_id && self::active_affiliate !== false) {
                return self::active_affiliate;
            }

            $aff = new WP_User($aff_id);
            self::$active_affiliate = $aff;
            return self::$active_affiliate;
        }

        function is_valid_affiliate($affiliate) {
            
        }
        // Update Record Hit of an affiliate
    }
}

return new Saff_Admin_Affiliate();