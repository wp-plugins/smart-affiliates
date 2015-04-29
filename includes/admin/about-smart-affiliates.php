<?php
/*
 * About Icegram
 */

if ( !defined( 'ABSPATH' ) ) exit;

// Actions for support

?>
        <div class="wrap about-wrap">             
            <h1><?php _e( "Welcome to Smart Affiliates", "smart-affiliates" ); ?></h1>
                <?php
                 if ((saff_is_plugin_active('affiliates/affiliates.php') || saff_is_plugin_active('affiliates-pro/affiliates-pro.php')) && defined('AFFILIATES_TP')) {
                    $tables = $wpdb->get_results("SHOW TABLES LIKE '" . $wpdb->prefix . AFFILIATES_TP . "%'", 'ARRAY_A');
                    $show_notification = get_option('show_migrate_affiliates_notification', 'yes');
                    //Note: To test migration uncomment following code
                    // $show_notification = 'yes';
                    if (!empty($tables) && $show_notification != 'no') {
                        
                        ?>
                        <div>
                        <div>
                        <?php echo __('We found data from the "Affiliates". Do you want to migrate it?', SAFF_TEXT_DOMAIN); ?>
                                <span class="migrate_affiliates_actions">
                                    <a href="<?php echo add_query_arg(array('page' => 'smart_affiliates_settings', 'migrate' => 'affiliates', 'is_from_docs' => 1), admin_url('admin.php')); ?>" class="button-primary" id="migrate_yes" ><?php echo __('Migrate Now', SAFF_TEXT_DOMAIN); ?></a>
                                    <a href="<?php echo add_query_arg(array('page' => 'smart_affiliates_settings', 'migrate' => 'ignore_affiliates',  'is_from_docs' => 1), admin_url('admin.php')); ?>" class="button" id="migrate_no" ><?php echo __('Dismiss', SAFF_TEXT_DOMAIN); ?></a>
                                </span>
                            
                            <p>Note: Once you migrate from Affiliates plugin, please deactivate Affiliates. Affiliates and Smart Affiliates can't work together</p>
                        </div>
                        </div>
                        <?php
                    }
                }
                
                ?>
            
            
           
            <div class="changelog">


                <hr>

                <div class="feature-section col">
                        <div class="col-1">        
                                <h2 class=""><?php _e( "Setting up Smart Affiliates", "smart-affiliates" ); ?></h2>
                                <ol>
                                <li><p><?php echo sprintf(__("Go to %s","smart-affiliates"), '<a href="' . add_query_arg(array("page" => "smart_affiliates_settings"), admin_url("admin.php")) . '">Smart Affiliates - Settings</a>'); ?></p></li>
                                <li><p><?php _e("Select 'User Roles' which you want to set as affiliate. All users with selected roles will become your affiliates","smart-affiliates"); ?></p></li>
                                <li><p><?php _e("Set % commission you want to pay to your affiliate when they generate a sale for you","smart-affiliates"); ?></p></li>
                                <li><p><?php _e("All commissions are approved automatically","smart-affiliates"); ?></p></li>
                                <li><p><?php echo sprintf(__("You can set PayPal API Username, Password and Signature to make affiliate payouts. (Note: Make payout feature is in beta currently). %s","smart-affiliates"), '<a href="http://www.putler.com/support/faq/how-to-get-paypal-api-username-password-and-signature-information">Learn how to get API details</a>'); ?></p></li>
                                </ol>
                        </div>
                    
                        <div class="col-2">
                               
                        </div>
                        <div class="col-3 last-feature">
                                
                        </div>
                </div>                
                <hr>

                <div class="feature-section col">
                        <div class="col-1">
                            <h2><?php _e("FAQ / Common Problems", "smart-affiliates"); ?></h2>

                                <h4><?php _e("Where do Affiliates login / get their stats from?", "smart-affiliates"); ?></h4>
                                <p><?php _e("This feature will come soon. Currently only administrators can see Affiliate dashboard.", "smart-affiliates"); ?></p>

                                <h4><?php _e("How do I add affiliates?", "smart-affiliates"); ?></h4>
                                <p><?php _e("If you want to make all users of specific role as an affiliate, go to settings panel and check specific role. All users under that role will become your affiliate. Additionally, go to Users - All Users and select a user. You will see 'Is Affiliate' option. Check that option and that user will become your affiliate.", "smart-affiliates"); ?></p>

                                <h4><?php _e("Where's the link affiliates use to link to my site?", "smart-affiliates"); ?></h4>
                                <p><?php _e("You can see any affiliate's 'affiliate link' under their name in the Smart Affiliates dashboard..Additionally, go to Users - All Users and select a user. You will see their affiliate link on their profile page. Copy it and pass it on to your affiliates.", "smart-affiliates"); ?></p>
                        </div>
                        <div class="col-2 last-feature">                                

                        </div>
                </div>
            </div>            
        </div>