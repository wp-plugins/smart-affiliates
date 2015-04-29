<?php
if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Migrate_Affiliates')) {

    class Saff_Migrate_Affiliates {

        function __construct() {

            if (is_admin()) {
                add_action('admin_init', array($this, 'track_affiliates_migration'));
            }
        }

        function track_affiliates_migration() {
            global $wpdb;

            if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'smart_affiliates_settings') {

                if (isset($_REQUEST['migrate'])) {

                    if ($_REQUEST['migrate'] == 'affiliates') {
                           if (!function_exists('_affiliates_get_tablename')) {
                               wp_die(sprintf(__('Could not locate Affiliates plugin. Make sure that it is installed & activated. %s', SAFF_TEXT_DOMAIN), '<a href="' . $_SERVER['HTTP_REFERER'] . '">' . __('Back', SAFF_TEXT_DOMAIN) . '</a>'));
                           } 

                        $this->map_missing_affiliates();
                        $this->migrate_hits();
                        $this->migrate_referrals();
                        $this->update_commission_status();
                        $this->migrate_affiliates_users();
                        
                        // GEt pname from Affiliates and set pnmae for saff
                        $affiliates_pname = ( defined('AFFILIATES_PNAME') ) ? AFFILIATES_PNAME : 'affiliates';
                        $pname = get_option('aff_pname', $affiliates_pname);
                        update_option( 'saff_migrated_pname', $pname);
                        
                        update_option( 'show_migrate_affiliates_notification', 'no' );
                        
                    }

                    if ($_REQUEST['migrate'] == 'ignore_affiliates') {
                        update_option('show_migrate_affiliates_notification', 'no');
                    }
                    
                    if($_REQUEST['is_from_docs'] == 1) {
                        $docs_page = add_query_arg(array('page' => 'smart_affiliates_documentation'), admin_url('admin.php'));
                        wp_safe_redirect($docs_page);
                    }   
                }
                
                if ((saff_is_plugin_active('affiliates/affiliates.php') || saff_is_plugin_active('affiliates-pro/affiliates-pro.php')) && defined('AFFILIATES_TP')) {
                    $tables = $wpdb->get_results("SHOW TABLES LIKE '" . $wpdb->prefix . AFFILIATES_TP . "%'", 'ARRAY_A');
                    $show_notification = get_option('show_migrate_affiliates_notification', 'yes');
                    //Note: To test migration uncomment following code
                    // $show_notification = 'yes';
                    if (!empty($tables) && $show_notification != 'no') {
                        ?>
                        <div class="description updated">
                            <p>
                        <?php echo __('We found data from the "Affiliates". Do you want to migrate it?', SAFF_TEXT_DOMAIN); ?>
                                <span class="migrate_affiliates_actions">
                                    <a href="<?php echo add_query_arg(array('page' => 'smart_affiliates_settings', 'migrate' => 'affiliates'), admin_url('admin.php')); ?>" class="button-primary" id="migrate_yes" ><?php echo __('Migrate Now', SAFF_TEXT_DOMAIN); ?></a>
                                    <a href="<?php echo add_query_arg(array('page' => 'smart_affiliates_settings', 'migrate' => 'ignore_affiliates'), admin_url('admin.php')); ?>" class="button" id="migrate_no" ><?php echo __('Dismiss', SAFF_TEXT_DOMAIN); ?></a>
                                </span>
                            </p>
                        </div>
                        <?php
                    }
                }
            }
        }

        function migrate_affiliates_users() {
            global $wpdb;
            
            $saff_affiliates_users = get_saff_tablename('affiliates_users');
            $affiliates_users = _affiliates_get_tablename('affiliates_users');
            $create_table = $wpdb->query("CREATE TABLE IF NOT EXISTS {$saff_affiliates_users} LIKE {$affiliates_users} "); 
            $result = $wpdb->query("INSERT {$saff_affiliates_users} SELECT * FROM {$affiliates_users} ");
        }
        function map_missing_affiliates() {
            global $wpdb;

            $affiliates_to_users_query = "SELECT affiliates.affiliate_id, affiliates.name, affiliates.email, affiliates_users.user_id
												FROM " . _affiliates_get_tablename('affiliates') . " AS affiliates
												LEFT JOIN " . _affiliates_get_tablename('affiliates_users') . " AS affiliates_users
													ON ( affiliates.affiliate_id = affiliates_users.affiliate_id ) ";

            $affiliates_to_users_results = $wpdb->get_results($affiliates_to_users_query, 'ARRAY_A');

            $affiliate_ids_to_user_ids = array();

            if (!empty($affiliates_to_users_results)) {

                foreach ($affiliates_to_users_results as $affiliate) {

                    if (empty($affiliate['user_id'])) {

                        $user_data = array(
                            'user_login' => sanitize_user($affiliate['name']),
                            'user_email' => $affiliate['email'],
                            'display_name' => $affiliate['name'],
                            'user_pass' => sanitize_user($affiliate['name'])
                        );

                        $user_id = wp_insert_user($user_data);

                        if (!is_wp_error($user_id)) {
                            update_user_meta($user_id, 'saff_paypal_email', $affiliate['email']);
                            $affiliate_ids_to_user_ids[$affiliate['affiliate_id']] = $user_id;
                            $wpdb->insert(
                                    _affiliates_get_tablename('affiliates_users'), array(
                                'user_id' => $user_id,
                                'affiliate_id' => $affiliate['affiliate_id']
                                    )
                            );
                        }
                    } else {
                        update_user_meta($affiliate['user_id'], 'saff_paypal_email', $affiliate['email']);
                    }
                }
            }

            return $affiliate_ids_to_user_ids;
        }

        function migrate_hits() {
            global $wpdb;

            if (!function_exists('_affiliates_get_tablename')) {

                wp_die(sprintf(__('Could not locate Affiliates plugin. Make sure that it is installed & activated. %s', SAFF_TEXT_DOMAIN), '<a href="' . $_SERVER['HTTP_REFERER'] . '">' . __('Back', SAFF_TEXT_DOMAIN) . '</a>'));
            }

            $migrate_hits_query = "INSERT INTO " . get_saff_tablename('hits') . " ( affiliate_id, datetime, ip, user_id, count, type )
									SELECT affiliates_users.user_id, hits.datetime, hits.ip, hits.user_id, hits.count, hits.type FROM " . _affiliates_get_tablename('hits') . " AS hits
										INNER JOIN " . _affiliates_get_tablename('affiliates_users') . " AS affiliates_users ON ( hits.affiliate_id = affiliates_users.affiliate_id )
										";

            $wpdb->query($migrate_hits_query);
        }

        function migrate_referrals() {
            global $wpdb;

            if (!function_exists('_affiliates_get_tablename')) {

                wp_die(sprintf(__('Could not locate Affiliates plugin. Make sure that it is installed & activated. %s', SAFF_TEXT_DOMAIN), '<a href="' . $_SERVER['HTTP_REFERER'] . '">' . __('Back', SAFF_TEXT_DOMAIN) . '</a>'));
            }

            $affiliates_accepted_status = ( defined('AFFILIATES_REFERRAL_STATUS_ACCEPTED') ) ? AFFILIATES_REFERRAL_STATUS_ACCEPTED : 'accepted';
            $default_affiliates_status = get_option('aff_default_referral_status', $affiliates_accepted_status);

            $saff_default_commission_status = ( defined('SAFF_DEFAULT_COMMISSION_STATUS') ) ? SAFF_DEFAULT_COMMISSION_STATUS : get_option('saff_default_commission_status');

            $migrate_referrals_query = "INSERT INTO " . get_saff_tablename('referrals') . " ( affiliate_id, post_id, datetime, description, ip, user_id, amount, currency_id, data, status, type, reference )
									SELECT affiliates_users.user_id, referrals.post_id, referrals.datetime, referrals.description, referrals.ip, referrals.user_id, referrals.amount, referrals.currency_id, referrals.data, 
										CASE referrals.status
											WHEN '" . ( defined('AFFILIATES_REFERRAL_STATUS_ACCEPTED') ? AFFILIATES_REFERRAL_STATUS_ACCEPTED : 'accepted' ) . "' THEN '" . ( defined('SAFF_REFERRAL_STATUS_UNPAID') ? SAFF_REFERRAL_STATUS_UNPAID : 'unpaid' ) . "'
											WHEN '" . ( defined('AFFILIATES_REFERRAL_STATUS_CLOSED') ? AFFILIATES_REFERRAL_STATUS_CLOSED : 'closed' ) . "' THEN '" . ( defined('SAFF_REFERRAL_STATUS_PAID') ? SAFF_REFERRAL_STATUS_PAID : 'paid' ) . "'
											WHEN '" . ( defined('AFFILIATES_REFERRAL_STATUS_PENDING') ? AFFILIATES_REFERRAL_STATUS_PENDING : 'pending' ) . "' THEN '" . ( defined('SAFF_REFERRAL_STATUS_UNPAID') ? SAFF_REFERRAL_STATUS_UNPAID : 'unpaid' ) . "'
											WHEN '" . ( defined('AFFILIATES_REFERRAL_STATUS_REJECTED') ? AFFILIATES_REFERRAL_STATUS_REJECTED : 'rejected' ) . "' THEN '" . ( defined('SAFF_REFERRAL_STATUS_REJECTED') ? SAFF_REFERRAL_STATUS_REJECTED : 'rejected' ) . "'
											END,
										referrals.type, referrals.reference FROM " . _affiliates_get_tablename('referrals') . " AS referrals
											LEFT JOIN " . _affiliates_get_tablename('affiliates_users') . " AS affiliates_users ON ( affiliates_users.affiliate_id = referrals.affiliate_id )
											";

            $wpdb->query($migrate_referrals_query);
        }
        
        function update_commission_status() {
            global $wpdb;
            
            $wc_compat = Smart_Affiliates::wc_compat();

            $referrals_table = get_saff_tablename('referrals');
            
            $query = $wpdb->prepare("SELECT DISTINCT post_id FROM {$referrals_table} WHERE status = %s" , SAFF_REFERRAL_STATUS_PENDING);
            $order_ids = $wpdb->get_col($query);
            
            if(count($order_ids) > 0) {
                $args = array('fields' => 'all_with_object_id');
                $order_status_details = wp_get_object_terms($order_ids, 'shop_order_status', $args);
                if(count($order_status_details) > 0) {
                    $statuses = array();
                    foreach ($order_status_details as $detail) {
                        $statuses[$detail->slug][] = $detail->object_id;
                    }
                    
                    foreach ($statuses as $order_status => $order_ids) {

                        if ( SA_WC_Compatibility_2_2::is_wc_gte_22() && strpos( $order_status, 'wc-' ) === 0 ) {
                            $order_status = substr( $order_status, 3 );
                        }
                            
                        switch($order_status) {
                            case 'refunded':
                            case 'cancelled':
                            case 'failed':    
                                $commission_status = SAFF_REFERRAL_STATUS_REJECTED;
                                break;
                            
                            case 'completed':
                            case 'pending':
                            case 'on-hold':
                            case 'processing':
                                $commission_status = SAFF_REFERRAL_STATUS_UNPAID;
                                break;
                        }
                        
                        $query = "UPDATE {$referrals_table} SET status = '{$commission_status}' WHERE post_id IN ( " .  implode(',', $order_ids) . ")";
                        $wpdb->query($query);
                    }
                }
            }
        }

    }

    return new Saff_Migrate_Affiliates();
}
