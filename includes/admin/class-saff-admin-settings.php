<?php
if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Admin_Settings')) {

    class Saff_Admin_Settings {

        function __construct() {

            add_action('admin_enqueue_scripts', array($this, 'register_admin_settings_styles'));
            add_action('admin_enqueue_scripts', array($this, 'register_admin_settings_scripts'));
            //add_action( 'wp_ajax_saff_search_users', array($this, 'saff_search_users' ));
        }
        
        
        function saff_search_users() {

            if (empty($_POST['user_name'])) {
                die('-1');
            }


            $search_query = htmlentities2(trim($_POST['user_name']));

            $found_users = get_users(array(
                'number' => 9999,
                'search' => $search_query . '*'
                    )
            );

            if ($found_users) {
                $user_list = '<ul>';
                foreach ($found_users as $user) {
                    $user_list .= '<li><a href="#" data-id="' . esc_attr($user->ID) . '" data-login="' . esc_attr($user->user_login) . '">' . esc_html($user->user_login) . '</a></li>';
                }
                $user_list .= '</ul>';

                echo json_encode(array('results' => $user_list, 'id' => 'found'));
            } else {
                echo json_encode(array('results' => '<p>' . __('No users found', 'affiliate-wp') . '</p>', 'id' => 'fail'));
            }

            die();
        }

        function register_admin_settings_styles() {
            wp_register_style('saff-admin-settings-style', SAFF_PLUGIN_URL . '/assets/css/saff-admin-settings.css');
        }
        
        function register_admin_settings_scripts() {
            wp_register_script('saff-admin-settings-script', SAFF_PLUGIN_URL . '/assets/js/saff-admin-settings.js', array('jquery'));
        }

        static function saff_settings_page() {
            global $wpdb, $wp_roles;
            
            
            wp_enqueue_script('saff-admin-settings-script');

            if (!wp_style_is('saff-admin-settings-style')) {
                wp_enqueue_style('saff-admin-settings-style');
            }
            ?>
            <div class="wrap">
                <h2><?php echo __('Smart Affiliates', SAFF_TEXT_DOMAIN); ?> &bull; <?php echo __('Settings', SAFF_TEXT_DOMAIN); ?></h2>
                <div class="saff_settings_wrapper">
                <form action="" method="post">
            <?php
            if (isset($_POST['saff_save_admin_settings'])) {

                if (isset($_POST['affiliate_users_roles'])) {
                    update_option('affiliate_users_roles', $_POST['affiliate_users_roles']);
                }

                if (isset($_POST['approve_commissions'])) {
                    update_option('approve_commissions', $_POST['approve_commissions']);
                }

                if (isset($_POST['approve_commissions_after_days'])) {
                    update_option('approve_commissions_after_days', $_POST['approve_commissions_after_days']);
                }

                if (isset($_POST['min_commissions_balance'])) {
                    update_option('min_commissions_balance', $_POST['min_commissions_balance']);
                }

                if (isset($_POST['min_commission_balance_first_after_amount'])) {
                    update_option('min_commission_balance_first_after_amount', $_POST['min_commission_balance_first_after_amount']);
                }

                if (isset($_POST['min_commission_balance_full_after_amount'])) {
                    update_option('min_commission_balance_full_after_amount', $_POST['min_commission_balance_full_after_amount']);
                }

                if (isset($_POST['min_commission_balance_reserve_amount'])) {
                    update_option('min_commission_balance_reserve_amount', $_POST['min_commission_balance_reserve_amount']);
                }

                if (isset($_POST['saff_storewide_commission'])) {
                    update_option('saff_storewide_commission', $_POST['saff_storewide_commission']);
                }

                if (isset($_POST['is_recurring_commission'])) {
                    update_option('is_recurring_commission', $_POST['is_recurring_commission']);
                } else {
                    update_option('is_recurring_commission', 'no');
                }

                if (isset($_POST['saff_is_paypal_sandbox'])) {
                    update_option('saff_is_paypal_sandbox', $_POST['saff_is_paypal_sandbox']);
                } else {
                    update_option('saff_is_paypal_sandbox', 'no');
                }

                if (isset($_POST['saff_paypal_api_username'])) {
                    update_option('saff_paypal_api_username', $_POST['saff_paypal_api_username']);
                }

                if (isset($_POST['saff_paypal_api_password'])) {
                    update_option('saff_paypal_api_password', $_POST['saff_paypal_api_password']);
                }

                if (isset($_POST['saff_paypal_api_signature'])) {
                    update_option('saff_paypal_api_signature', $_POST['saff_paypal_api_signature']);
                }
            }

            $affiliate_users_roles = get_option('affiliate_users_roles');
            $min_commissions_balance = get_option('min_commissions_balance');
            $approve_commissions = get_option('approve_commissions');
            ?>
                    <table class="form-table">
                        
                        <!--
                        <tr class="form-row form-required">
				<th scope="row">
					<label for="user_name"><?php _e( 'User', 'affiliate-wp' ); ?></label>
				</th>
				<td>
					<span class="affwp-ajax-search-wrap">
						<input type="text" name="user_name" id="user_name" class="affwp-user-search" autocomplete="off" />
						<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
					</span>
					<div id="affwp_user_search_results"></div>
					<p class="description"><?php _e( 'Begin typing the name of the affiliate to perform a search for their associated user account.', 'affiliate-wp' ); ?></p>
				</td>
			</tr>
                        -->
                        
                        <tr>
                            <th scope="row"><label><?php echo __('Affiliate Users Roles', SAFF_TEXT_DOMAIN); ?></label></th>
                            <td>
                                <p><?php echo __('Users with following roles are affiliates', SAFF_TEXT_DOMAIN); ?></p>
                                <table class="saff_roles_table">
                    <?php
                    $i = 1;
                    foreach ($wp_roles->role_names as $role => $label) {
                        if (( $i % 2 ) != 0) {
                            ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" id="affiliate_users_roles_<?php echo $role; ?>" name="affiliate_users_roles[]" value="<?php echo $role; ?>" <?php echo (!empty($affiliate_users_roles) && in_array($role, $affiliate_users_roles) ) ? 'checked' : ''; ?> /> <label for="affiliate_users_roles_<?php echo $role; ?>"><?php echo $label; ?></label>
                                                </td>								
                                        <?php } else { ?>
                                                <td>
                                                    <input type="checkbox" id="affiliate_users_roles_<?php echo $role; ?>" name="affiliate_users_roles[]" value="<?php echo $role; ?>" <?php echo (!empty($affiliate_users_roles) && in_array($role, $affiliate_users_roles) ) ? 'checked' : ''; ?> /> <label for="affiliate_users_roles_<?php echo $role; ?>"><?php echo $label; ?></label>
                                                </td>
                                            </tr>
                                                <?php
                                            }
                                            if (( $i == count($wp_roles->role_names) ) && ( $i % 2 ) != 0) {
                                                ?>
                                </tr>
                                            <?php
                                        }
                                        $i++;
                                    }
                                    ?>
                    </table>
                    </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="saff_storewide_commission"><?php echo __('Commission', SAFF_TEXT_DOMAIN); ?></label></th>
                        <td><input name="saff_storewide_commission" type="number" id="saff_storewide_commission" class="saff_small_input_number" value="<?php form_option('saff_storewide_commission'); ?>" class="regular-text" /> %</td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php echo __('Approve Commisssions', SAFF_TEXT_DOMAIN); ?></label></th>
                        <td>
                            <input type="radio" id="approve_commissions_instant" name="approve_commissions" value="instant" <?php echo ( empty($approve_commissions) || $approve_commissions == 'instant' ) ? 'checked' : ''; ?>/>&nbsp;
                            <label for="approve_commissions_instant"><?php echo __('Immediately after order completes', SAFF_TEXT_DOMAIN); ?></label>
                        </td>
                    </tr>
                    <!-- <tr>
                        <th scope="row"></th>
                        <td>
                            <input type="radio" id="approve_commissions_after_days" name="approve_commissions" value="after_days" <?php echo ( $approve_commissions == 'after_days' ) ? 'checked' : ''; ?>/>&nbsp;
                            <label for="approve_commissions_after_days"><?php echo __('After', SAFF_TEXT_DOMAIN); ?> <input type="number" class="saff_small_input_number" id="approve_commissions_after_days_input" name="approve_commissions_after_days" value="<?php echo form_option('approve_commissions_after_days'); ?>" /><?php echo __('days of order completes', SAFF_TEXT_DOMAIN); ?></label>
                        </td>
                    </tr> -->
                    <tr>
                        <th scope="row"><label><?php echo __('Minimum Commission Balance Requirement', SAFF_TEXT_DOMAIN); ?></label></th>
                        <td>
                            <input type="radio" id="min_commissions_balance_no" name="min_commissions_balance" value="no" <?php echo ( empty($min_commissions_balance) || $min_commissions_balance == 'no' ) ? 'checked' : ''; ?>/>&nbsp;
                            <label for="min_commissions_balance_no"><?php echo __('Not required', SAFF_TEXT_DOMAIN); ?></label>
                        </td>
                    </tr>
                    <!-- <tr>
                        <th scope="row"></th>
                        <td>
                            <input type="radio" id="min_commissions_balance_first_after_amount" name="min_commissions_balance" value="first_after_amount" <?php echo ( $min_commissions_balance == 'first_after_amount' ) ? 'checked' : ''; ?>/>&nbsp;
                            <label for="min_commissions_balance_first_after_amount"><?php echo sprintf(__('First payout after %s', SAFF_TEXT_DOMAIN), SAFF_CURRENCY); ?> <input type="number" class="saff_small_input_number" name="min_commission_balance_first_after_amount" value="<?php echo form_option('min_commission_balance_first_after_amount'); ?>" /></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <td>
                            <input type="radio" id="min_commissions_balance_full_after_amount" name="min_commissions_balance" value="full_after_amount" <?php echo ( $min_commissions_balance == 'full_after_amount' ) ? 'checked' : ''; ?>/>&nbsp;
                            <label for="min_commissions_balance_full_after_amount"><?php echo sprintf(__('Always pay in full after %s', SAFF_TEXT_DOMAIN), SAFF_CURRENCY); ?> <input type="number" class="saff_small_input_number" name="min_commission_balance_full_after_amount" value="<?php echo form_option('min_commission_balance_full_after_amount'); ?>" /></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <td>
                            <input type="radio" id="min_commissions_balance_reserve_amount" name="min_commissions_balance" value="reserve_amount" <?php echo ( $min_commissions_balance == 'reserve_amount' ) ? 'checked' : ''; ?>/>&nbsp;
                            <label for="min_commissions_balance_reserve_amount"><?php echo sprintf(__('Always maintain a reserve of %s', SAFF_TEXT_DOMAIN), SAFF_CURRENCY); ?> <input type="number" class="saff_small_input_number" name="min_commission_balance_reserve_amount" value="<?php echo form_option('min_commission_balance_reserve_amount'); ?>" /></label>
                        </td>
                    </tr> -->
                    <?php if ( saff_is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) { ?>
                    <tr>
                        <th scope="row">
                            <label for="is_recurring_commission"><?php echo __('Recurring commission?', SAFF_TEXT_DOMAIN); ?></label>
                        </th>
                        <td>
                            <?php 
                                $is_recurring_commission = get_option( 'is_recurring_commission' );
                                $echo = true;
                            ?>
                            <input type="checkbox" id="is_recurring_commission" name="is_recurring_commission" value="yes" <?php checked( $is_recurring_commission, 'yes', $echo ); ?>/>
                            <span class="description"><?php echo __( 'Check if you want to give commissions to recurring orders also.', SAFF_TEXT_DOMAIN ); ?></span>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <th scope="row">
                            <label for="saff_is_paypal_sandbox"><?php echo __('PayPal Sandbox?', SAFF_TEXT_DOMAIN); ?></label>
                        </th>
                        <td>
                            <?php 
                                $saff_is_paypal_sandbox = get_option( 'saff_is_paypal_sandbox' );
                                $echo = true;
                            ?>
                            <input type="checkbox" id="saff_is_paypal_sandbox" name="saff_is_paypal_sandbox" value="yes" <?php checked( $saff_is_paypal_sandbox, 'yes', $echo ); ?>/>
                            <span class="description"><?php echo __( 'Check to enable PayPal Sandbox mode. All the payments will be redirected to PayPal Sandbox.', SAFF_TEXT_DOMAIN ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php echo __('PayPal API Details', SAFF_TEXT_DOMAIN); ?></label></th>
                        <td>
                            <p><?php echo sprintf(__('API details are required for paying out commission. (%s)', SAFF_TEXT_DOMAIN), '<a href="https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/" target="_blank">' . __('Where to find', SAFF_TEXT_DOMAIN) . '</a>'); ?></p>
                            <table class="saff_paypal_api_details_table">
                                <tr>
                                    <th><label for="saff_paypal_api_username"><?php echo __('User Name', SAFF_TEXT_DOMAIN); ?></label></th>
                                    <td><input type="text" id="saff_paypal_api_username" name="saff_paypal_api_username" value="<?php echo form_option('saff_paypal_api_username'); ?>" /></td>
                                </tr>
                                <tr>
                                    <th><label for="saff_paypal_api_password"><?php echo __('Password', SAFF_TEXT_DOMAIN); ?></label></th>
                                    <td><input type="text" id="saff_paypal_api_password" name="saff_paypal_api_password" value="<?php echo form_option('saff_paypal_api_password'); ?>" /></td>
                                </tr>
                                <tr>
                                    <th><label for="saff_paypal_api_signature"><?php echo __('Signature', SAFF_TEXT_DOMAIN); ?></label></th>
                                    <td><input type="text" id="saff_paypal_api_signature" name="saff_paypal_api_signature" value="<?php echo form_option('saff_paypal_api_signature'); ?>" /></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    </table>
                    <input type="submit" class="button-primary" name="saff_save_admin_settings" value="<?php echo __('Save changes', SAFF_TEXT_DOMAIN); ?>" />
                </form>
                </div>
            </div>
            <?php
        }

    }

}

return new Saff_Admin_Settings();
