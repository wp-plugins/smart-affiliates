<?php
if (!defined('ABSPATH'))
    exit;

if (!class_exists('Saff_Admin_Dashboard')) {

    class Saff_Admin_Dashboard {

        function __construct() {
            add_action('admin_enqueue_scripts', array($this, 'register_admin_dashboard_scripts'));
            add_action('admin_enqueue_scripts', array($this, 'register_admin_dashboard_styles'));
            add_action('wp_ajax_get_dashboard_data', array($this, 'get_dashboard_data'));
            add_action('wp_ajax_change_commission_status', array($this, 'change_commission_status'));
            add_action('wp_ajax_add_payout', array($this, 'add_payout'));
            add_action('wp_ajax_make_payment', array($this, 'make_payment'));
        }
        
        // retrieves a list of users via live search


        function register_admin_dashboard_scripts() {
            
            //Dashboard scripts
            wp_register_script('saff-admin-dashboard-script-app', SAFF_PLUGIN_URL . '/assets/js/app.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-models-affiliate', SAFF_PLUGIN_URL . '/assets/js/models/affiliate.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-models-order', SAFF_PLUGIN_URL . '/assets/js/models/order.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-models-payment', SAFF_PLUGIN_URL . '/assets/js/models/payment.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-collections-affiliates', SAFF_PLUGIN_URL . '/assets/js/collections/affiliates.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-collections-orders', SAFF_PLUGIN_URL . '/assets/js/collections/orders.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-collections-payments', SAFF_PLUGIN_URL . '/assets/js/collections/payments.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-views-affiliate', SAFF_PLUGIN_URL . '/assets/js/views/affiliate.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-views-affiliates', SAFF_PLUGIN_URL . '/assets/js/views/affiliates.js', array('wp-backbone', 'saff-admin-dashboard-script-views-affiliate'));
            wp_register_script('saff-admin-dashboard-script-views-order', SAFF_PLUGIN_URL . '/assets/js/views/order.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-views-add-affiliate', SAFF_PLUGIN_URL . '/assets/js/views/addAffiliate.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-models-dashboard', SAFF_PLUGIN_URL . '/assets/js/models/dashboard.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-views-dashboard', SAFF_PLUGIN_URL . '/assets/js/views/dashboard.js', array('wp-backbone'));
//            /wp_register_script('saff-admin-dashboard-script-views-affiliate-stats', SAFF_PLUGIN_URL . '/assets/js/views/affiliateStats.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script-views-affiliate-details', SAFF_PLUGIN_URL . '/assets/js/views/affiliateDetails.js', array('wp-backbone'));



            wp_register_script('saff-admin-dashboard-script-collections-dummyData', SAFF_PLUGIN_URL . '/assets/js/collections/dummyData.js', array('wp-backbone'));
            wp_register_script('saff-admin-dashboard-script', SAFF_PLUGIN_URL . '/assets/js/saff-admin-dashboard.js', array('jquery'));
        }

        function register_admin_dashboard_styles() {
            wp_register_style('saff-admin-dashboard-font', SAFF_PLUGIN_URL . '/assets/font-awesome/css/font-awesome.min.css');
            wp_register_style('saff-admin-dashboard-jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
            wp_register_style('saff-admin-dashboard-style', SAFF_PLUGIN_URL . '/assets/css/saff-admin-dashboard.css');
        }

        static function saff_dashboard_page() {
            global $wpdb;

            if (!wp_script_is('jquery-ui-datepicker')) {
                wp_enqueue_script('jquery-ui-datepicker');
            }

            if (!wp_style_is('saff-admin-dashboard-jquery-ui-css')) {
                wp_enqueue_style('saff-admin-dashboard-jquery-ui-css');
            }

            wp_enqueue_script('saff-admin-dashboard-script-app');
            wp_enqueue_script('saff-admin-dashboard-script-models-affiliate');
            wp_enqueue_script('saff-admin-dashboard-script-models-order');
            wp_enqueue_script('saff-admin-dashboard-script-models-payment');
            wp_enqueue_script('saff-admin-dashboard-script-collections-affiliates');
            wp_enqueue_script('saff-admin-dashboard-script-collections-orders');
            wp_enqueue_script('saff-admin-dashboard-script-collections-payments');
            wp_enqueue_script('saff-admin-dashboard-script-views-affiliates');
            wp_enqueue_script('saff-admin-dashboard-script-views-affiliate');
            wp_enqueue_script('saff-admin-dashboard-script-views-order');
            wp_enqueue_script('saff-admin-dashboard-script-views-add-affiliate');
            wp_enqueue_script('saff-admin-dashboard-script-models-dashboard');
            wp_enqueue_script('saff-admin-dashboard-script-views-dashboard');

//          //wp_enqueue_script('saff-admin-dashboard-script-views-affiliate-stats');
            wp_enqueue_script('saff-admin-dashboard-script-views-affiliate-details');


            wp_enqueue_script('saff-admin-dashboard-script-collections-dummyData');

            if (!wp_script_is('saff-admin-dashboard-script')) {
                wp_enqueue_script('saff-admin-dashboard-script');
            }

            if (!wp_style_is('saff-admin-dashboard-style')) {
                wp_enqueue_style('saff-admin-dashboard-style');
            }

            if (!wp_style_is('saff-admin-dashboard-font')) {
                wp_enqueue_style('saff-admin-dashboard-font');
            }

            $from = date('d-M-Y', mktime(0, 0, 0, date('n'), 1, date('Y')));
            $to = date('d-M-Y');

            $precalculated_dates = array(
                'today' => get_saff_date_range('today'),
                'yesterday' => get_saff_date_range('yesterday'),
                'this_week' => get_saff_date_range('this_week'),
                'last_week' => get_saff_date_range('last_week'),
                'this_month' => get_saff_date_range('this_month'),
                'last_month' => get_saff_date_range('last_month'),
                'three_months' => get_saff_date_range('three_months'),
                'six_months' => get_saff_date_range('six_months'),
                'this_year' => get_saff_date_range('this_year'),
                'last_year' => get_saff_date_range('last_year')
            );
            
            $wc_order_statuses = wc_get_order_statuses();
            $saff_commission_statuses = get_saff_commission_statuses();

            wp_localize_script('saff-admin-dashboard-script-app', 'saff_dashboard_js_var', array('smart_dates' => $precalculated_dates, 'security_text' => wp_create_nonce(SAFF_AJAX_SECURITY)));
            ?>
            <div class="wrap">
                <h2><?php echo __('Smart Affiliates', SAFF_TEXT_DOMAIN); ?> &bull; <span class="saff_breadcrum"><?php echo __('Dashboard', SAFF_TEXT_DOMAIN); ?></span class=""></h2>
                <div class="saff_dashboard_wrapper" id="saff_dashboard">
                    <div class="saff_dashboard_container">
                        <span class="saff_spinner hidden"><i class="fa fa-refresh fa-4x fa-spin"></i></span>
                        <div class="saff_dashboard_time_period_filter_container" id="saff_dashboard_header">
                            <!-- Template dashboard -->
                        </div>
                        <div class="saff_main_data_container" id="saff_main_data_container">
                            <div class="saff_dashboard_kpi_container" id="saff_dashboard_kpi">
                                <!-- Template -->
                            </div>
                            <div class="saff_dashboard_sort_filter_container">
                                
                            </div>
                            <!--
                            <div class="saff_dashboard_sort_filter_container">
                                <div class="saff_dashboard_sort_filter">
                                    <div class="right">
                                        <input type="text" id="saff_dashboard_search_text_1" name="saff_dashboard_search_text" value="" placeholder="<?php echo __('Search', SAFF_TEXT_DOMAIN); ?>..." />
                                    </div>
                                     <div class="left">
                                         <label><?php echo __('Show', SAFF_TEXT_DOMAIN); ?>:</label>&nbsp;
                                         <select name="saff_dashboard_show" id="saff_dashboard_show">
                                             <option value="earned_commissions"><?php echo __('Earned commissions', SAFF_TEXT_DOMAIN); ?></option>
                                             <option value="paid_commissions"><?php echo __('Paid commissions', SAFF_TEXT_DOMAIN); ?></option>
                                             <option value="unpaid_commissions" selected><?php echo __('Unpaid commissions', SAFF_TEXT_DOMAIN); ?></option>
                                             <option value="net_sales"><?php echo __('Net sales', SAFF_TEXT_DOMAIN); ?></option>
                                             <option value="refunds"><?php echo __('Refunds', SAFF_TEXT_DOMAIN); ?></option>
                                             <option value="customers_count"><?php echo __('Customers count', SAFF_TEXT_DOMAIN); ?></option>
                                         </select>
                                     </div>
                                </div>
                            </div>
                            -->
                            <div class="saff_dashboard_main_view_container">
                                <div class="saff_left_panel">
                                    <div class="saff_left_panel_header">
                                            <!-- <span class="add_affiliate_button saff_right_icons">
                                                <i class="fa fa-user fa-2x"></i>
                                                <i class="fa fa-plus-circle fa-lg"></i>
                                            </span> -->
                                            <!-- <input type="checkbox" class="all_affiliates_checkbox" value="0" id="all_affiliates_checkbox" name="saff_all_affiliates_checkbox" /> --> 
                                            <div class="affiliate_main">
                                                <div class="all_affiliate_row_1">
                                                    <div class="saff_dashboard_sort_filter_container">
                                                        <div class="saff_dashboard_sort_filter">
                                                            <label for="all_affiliates_checkbox"><?php echo __('Affiliates', SAFF_TEXT_DOMAIN); ?></label> <!-- <i class="fa fa-caret-up"></i> -->
                                                            <div class="right">
                                                                <input type="text" id="saff_dashboard_search_text" name="saff_dashboard_search_text" value="" placeholder="<?php echo __('Filter', SAFF_TEXT_DOMAIN); ?>..." />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="all_affiliate_row_2">
                                                    
                                                    <label for="all_affiliates_checkbox" id="total_commission_earned"></label> <!-- <i class="fa fa-caret-down"></i> -->
                                                </div>
                                            </div>
                                    </div>
                                    <div class="saff_left_panel_lists">
                                        <!-- Template tmpl-affiliate-lists will go here -->
                                    </div>
                                </div>
                                <div class="saff_details">
                                    <!-- Template will load here -->
                                </div>
                                <br class="clear"/>
                            </div>
                        </div>
                        <div class="saff_empty_container" id="saff_empty_container">
                            <p class="empty_results"> No data found for this time range</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template for Show -->
            <script id="tmpl-dashboard-header" type="text/html">
                <div class="saff_dashboard_time_period_filter">
                    <p class="saff_smart_date_filter">
                        <?php echo sprintf(__('Affiliate activity from %s to %s', SAFF_TEXT_DOMAIN), '<input type="text" id="saff_dashboard_from" name="saff_dashboard_from" value="' . $from . '">', '<input type="text" id="saff_dashboard_to" name="saff_dashboard_to" value="' . $to . '">'); ?>
                        <input type="button" class="button" id="show_dashboard_data" name="saff_dashboard_show" value="<?php echo __('Show', SAFF_TEXT_DOMAIN); ?>" />
                        <select id="saff_dashboard_smart_date_selector" name="saff_dashboard_smart_date_selector">
                            <# _.each( data.filter_options, function( value, key) { #>
                            <option value="{{ key }}" <# if (key == 'last_month') { #> selected="selected" <# } #> ><?php echo __('{{ value }}', SAFF_TEXT_DOMAIN); ?></option>
                            <# }); #>
                        </select>
                    </p>
                </div>

            </script>
            <!-- Template tmpl-dashboard-header : Over-->

            <!-- Template for Dashboard KPI -->
            <script id="tmpl-dashboard-kpi" type="text/html">
                <div class="saff_dashboard_kpis">

                        <div class="saff_sales_container">
                            <div class="saff_kpis_net_sales_container">
                                <div class="saff_net_sales_value kpi_value">
                                    <span class="saff_currency_symbol"><?php echo SAFF_CURRENCY; ?></span><strong>{{ data.kpi.net_affiliates_sales }}</strong>
                                </div>
                                <div class="saff_net_sales_text kpi_text">
                                    <?php echo __('Net Affiliates Sales', SAFF_TEXT_DOMAIN); ?>
                                </div>
                            </div>
                            
                            <div class="pie_chart"><span><i class="fa fa-pie-chart fa-2x"></i></span></div>
                            
                            <div class="saff_total_sales_container">
                                <div class="saff_total_sales_value kpi_value">
                                    <strong>{{ data.kpi.percent_of_total_sales }}%</strong>
                                </div>
                                <div class="saff_total_sales_text kpi_text">
                                    <?php echo __('of Total Sales', SAFF_TEXT_DOMAIN); ?>
                                </div>
                            </div>
                        </div>

                        <div class="saff_unpaid_commissions_container">
                            <div class="saff_unpaid_commissions_value kpi_value">
                                <span class="saff_currency_symbol"><?php echo SAFF_CURRENCY; ?></span><strong>{{ data.kpi.unpaid_commissions }}</strong>
                            </div>
                            <div class="saff_unpaid_commissions_text kpi_text">
                                <?php echo __('Unpaid Commissions', SAFF_TEXT_DOMAIN); ?>
                            </div>
                        </div>

                        <div class="saff_visitor_customer_container">
                            <div class="saff_visitor_container">
                                <div class="saff_customer_value kpi_value">
                                    <strong>{{ data.kpi.visitors_count }}</strong>
                                </div>
                                <div class="saff_customer_text kpi_text">
                                    <?php echo __('Visitors', SAFF_TEXT_DOMAIN); ?>
                                </div>
                            </div>
                            
                            <div class="pie_chart"><span><i class="fa fa-user fa-2x"></i></span></div>
                            
                            <div class="saff_customer_container">
                                <div class="saff_customer_value kpi_value">
                                    <# 
                                    var percent_of_all_customers = (data.kpi.visitors_count > 0 ) ? ((data.kpi.customers_count * 100) / data.kpi.visitors_count) : 0;
                                    percent_of_all_customers = percent_of_all_customers.toFixed(2);
                                    #>
                                    <strong>{{ data.kpi.customers_count }}</strong> &bull; <small> {{ percent_of_all_customers }}%</small>
                                </div>
                                <div class="saff_customer_text kpi_text">
                                    <?php echo __('Customers', SAFF_TEXT_DOMAIN); ?>
                                </div>
                            </div>
                        </div>
                </div>    
            </script>
            <!-- Template tmpl-dashboard-kpi Over -->

            <!-- Affiliate Lists !-->
            <script id="tmpl-affiliate-lists" type="text/html">
                <div class="affiliate_container">
                    <span class="saff_right_icons hidden">
                        <i class="fa fa-caret-right fa-2x"></i>
                    </span>
                    <div class="affiliate_main">
                        <div class="aff_avatar">
                            <img alt='' src='{{ data.avatar_url }}' class='avatar avatar-32 photo avatar-default' height='32' width='32' />
                        </div>
                        <div class="all_affiliate_row_1">
                            <label for="all_affiliates_checkbox"> {{ data.name }} </label>
                            <span class="pull-right commission_number"><label for="all_affiliates_checkbox"> <?php echo SAFF_CURRENCY; ?>{{ data.stats.current.commissions_earned.toFixed() }} </label></span>
                        </div>
                        <div class="affiliate_coommission_eanred_value">
                            &nbsp;
                        </div>
                    </div>
                     <!-- <input type="checkbox" class="all_affiliates_checkbox" value="0" id="all_affiliates_checkbox" name="saff_all_affiliates_checkbox" /> -->
                </div>
            </script>
            <!-- Template Affiliate Lists Over!-->

            <!-- Add Payout Form !-->
            <script id="tmpl-add-payout-form" type="text/html">
                <div class="saff_details_form">
                   <!-- <span class="close_add_payout_form" id="close_add_payout_form" ><a>Close</a></span> -->
                    <table>
                        <tr>
                            <td><?php echo __('Commission Paid', SAFF_TEXT_DOMAIN); ?></td>
                            <td><input type="text" class="" id="add_payout_commission_paid" name="add_payout_commission_paid" value="" placeholder="<?php echo '0.00'; ?>" /></td>
                        </tr>
                        <tr>
                            <td><?php echo __('Date', SAFF_TEXT_DOMAIN); ?></td>
                            <td><input type="text" id="saff_add_payout_date" name="saff_add_payout_date" value=""></td>
                        </tr>
                        <tr>
                            <td><?php echo __('Payment Method', SAFF_TEXT_DOMAIN); ?></td>
                            <td>
                                <select id="add_payout_payment_method" name="add_payout_payment_method">
                                    <option value="paypal" selected><?php echo __('PayPal', SAFF_TEXT_DOMAIN); ?></option>
                                    <option value="other"><?php echo __('Other', SAFF_TEXT_DOMAIN); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo __('Internal Note', SAFF_TEXT_DOMAIN); ?></td>
                            <td>
                                <textarea id="add_payout_note" name="add_payout_note" placeholder="<?php echo __('Add payout note for your reference', SAFF_TEXT_DOMAIN); ?>" rows="2" cols="15"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="button" class="button-primary" id="add_payout_button" name="add_payout_button" value="<?php echo __('Add Payout', SAFF_TEXT_DOMAIN); ?>" /></td>
                        </tr>
                    </table>
                </div>
            </script>
            <!-- Add Payout Form Over!-->

            <!-- Payout Form !-->
            <script id="tmpl-payout-form" type="text/html">
                <div class="saff_details_form">
                    <span class="close_add_payout_form" id="close_add_payout_form" ><a>Close</a></span>
                    <table>
                        <tr>
                            <td><?php echo __('Commission to Pay', SAFF_TEXT_DOMAIN); ?></td>
                            <td><span class="saff_currency_symbol"><?php echo SAFF_CURRENCY; ?></span><label id="saff_commissions_to_pay"><?php echo '1'; ?></label></td>
                        </tr>
                        <tr>
                            <td><?php echo __('PayPal Balance', SAFF_TEXT_DOMAIN); ?></td>
                            <td><span class="saff_currency_symbol"><?php echo SAFF_CURRENCY; ?></span><label id="saff_paypal_balance"><?php echo '0.00'; ?></label></td>
                        </tr>
                        <tr>
                            <td><?php echo __('Internal Note', SAFF_TEXT_DOMAIN); ?></td>
                            <td>
                                <textarea id="payout_note" name="payout_note" placeholder="<?php echo __('Add payout note for your reference', SAFF_TEXT_DOMAIN); ?>" rows="2" cols="15"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="button" class="button-primary" id="payout_button" value="<?php echo __('Pay', SAFF_TEXT_DOMAIN); ?>" /></td>
                        </tr>
                    </table>
                </div>
            </script>
            <!-- Payout Form Over!-->

            <!-- Payout History !-->
            <script id="tmpl-payout-history" type="text/html">
                <# if ( _.size(data.payout_history) > 0) { #>
                <table class="form-table">
                    <thead>
                        <tr>
                            <td><strong><?php echo __('Date', SAFF_TEXT_DOMAIN); ?></strong></td>
                            <!-- <td><strong><?php echo __('Order', SAFF_TEXT_DOMAIN); ?></strong></td> -->
                            <td><strong><?php echo __('Amount', SAFF_TEXT_DOMAIN); ?></strong></td>
                            <td><strong><?php echo __('Method', SAFF_TEXT_DOMAIN); ?></strong></td>
                            <td><strong><?php echo __('Note', SAFF_TEXT_DOMAIN); ?></strong></td>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <# var previous_date = null;
                            var date_class = "normal";
                            var group_date = false;
                            _.each( data.payout_history, function( payout, key ) {
                            
                            if(previous_date != payout.datetime) {
                                    previous_date = payout.datetime;
                                    group_date = true;
                                    date_class = "groupped_date";
                             } else { date_class = "normal"; group_date = false; }  #>
                        <tr>
                            <td  class="{{ date_class }}">
                                <# if(group_date) { #> 
                                    {{ payout.datetime }} 
                                <# } #>
                            </td>
                            <!-- <td><# if(payout.order_id != null) { #> #{{ payout.order_id }} <# } #>  </td> -->
                            <td><?php echo SAFF_CURRENCY; ?>{{ payout.amount }}</td>
                            <td>{{payout.method.charAt(0).toUpperCase()}}{{payout.method.substring(1).toLowerCase()}}</td>
                            <td>{{ payout.payout_notes }}</td>
                        </tr>
                        <# }); #>
                    </tbody>
                </table> 
                <# }  else { #>
                <p class="empty_results"> <?php echo __('No Payout history found', SAFF_TEXT_DOMAIN); ?></p>
                <# } #>
            </script>
            <!-- Payout History Over!-->

            <!-- Affiliate orders !-->
            <script id="tmpl-affiliates-orders" type="text/html">
                <# if (data.orders.length > 0) { #>
                <table class="form-table">
                    <thead>
                        <tr>
                            <td><strong><?php echo __('Date', SAFF_TEXT_DOMAIN); ?></strong></td>
                            <td><input type="checkbox" id="select_all_orders" name="saff_select_table" class="order_checkbox_all"/></td>
                            <td><strong><?php echo __('Order', SAFF_TEXT_DOMAIN); ?></strong></td>
                            <td><strong><?php echo __('Amount', SAFF_TEXT_DOMAIN); ?></strong></td>
                            <td><strong><?php echo __('Commission', SAFF_TEXT_DOMAIN); ?></strong></td>
                            <td><strong><?php echo __('Commission Status', SAFF_TEXT_DOMAIN); ?></strong></td>
                            <td><strong><?php echo __('Order Status', SAFF_TEXT_DOMAIN); ?></strong></td>
                        </tr>
                    </thead>
                    <tbody>
                        <#  var previous_date = null;
                            data.orders.each(function(order, order_id) { #>
                        <tr>
                            <td class="order_date">
                                <# if(previous_date != order.get('datetime') ) { #>
                                     {{ order.get('datetime') }}
                                <# previous_date = order.get('datetime'); } #>
                            </td>
                            <td><input type="checkbox" id="order_id_{{ order.get('order_id') }}" name="saff_select_order[]" value="{{ order.get('order_id') }}" class="order_checkbox"/></td>
                            <td class="order_id"><a href="{{order.get('order_url') }}" target="_blank">#{{ order.get('order_id') }}</a></td>
                            <td class="order_total"> <span class="order_total_span"> <?php echo SAFF_CURRENCY; ?>{{ order.get('order_total') }}</span></td>
                            <td class="saff_order_commission"><span class="order_commission_span"><?php echo SAFF_CURRENCY; ?>{{ order.get('commission') }}</span></td>
                            <td class="commission_status">{{order.get('status').charAt(0).toUpperCase()}}{{order.get('status').substring(1).toLowerCase()}}</td>
                            <td class="order_status"><# if(order.get('order_status') !== null ) { #> {{order.get('order_status').replace('wc-', '').charAt(0).toUpperCase()}}{{order.get('order_status').replace('wc-', '').substring(1).toLowerCase()}} <# } else { order.set('order_status', 'deleted');  #> <?php echo __('Deleted', SAFF_TEXT_DOMAIN); ?>  <# } #></td>
                        </tr>
                        <# }); #>
                    </tbody>
                </table>
                <# }  else { #>
                <p class="empty_results"> <?php echo __('No orders data found', SAFF_TEXT_DOMAIN); ?></p>
                <# } #>
            </script>
            <!-- Affiliate orders Over!-->

            <!-- Template Affiliate Details Start!-->
            <script id="tmpl-saff-details" type="text/html">
                <div class="saff_details_header">
                    
                    <div class="aff_name">
                            <div class="aff_avatar">
                                <img alt='' src='{{ data.avatar_url }}' class='avatar avatar-32 photo avatar-default' height='32' width='32' />
                                <!-- <i class="fa fa-user fa-3x"></i> -->
                            </div>
                            <div class="aff_detail">
                                <div class="aff_display_name">
                                    <a href="{{ data.edit_url }}" target="_blank"><strong>{{ data.name }}</strong></a>
                                    <!--
                                    <i class="fa fa-pencil fa-lg"></i>
                                    <i class="fa fa-envelope-o fa-lg"></i>
                                    -->
                                </div>
                                <div class="aff_join_detail">
                                    <small><?php echo sprintf(__('Joined %s ago', SAFF_TEXT_DOMAIN), '{{ data.formatted_join_duration }}'); ?></small>
                                </div>
                                <div class="aff_link">
                                    <?php echo sprintf(__('Affiliate link: %s', SAFF_TEXT_DOMAIN), '{{ data.referral_url }}'); ?>
                                </div>
                            </div>
                    </div>
                    <div class="aff_net_sales">
                            <div class="saff_net_sales_value kpi_value">
                                <?php echo SAFF_CURRENCY; ?>{{ data.stats.current.net_affiliates_sales }} &bull; <small><?php echo SAFF_CURRENCY; ?>{{ data.stats.all_time.net_affiliates_sales }}</small>
                            </div>
                            <div class="saff_net_sales_text kpi_text">
                                <?php echo __('Net Sales', SAFF_TEXT_DOMAIN); ?>
                            </div>
                    </div>  

                </div>
                <div class="saff_details_aff_stats">
                    <div class="aff_stats_row_1">
                        <div class="kpi_container saff_kpi_total_sales column_1">
                            <div class="saff_kpi_total_sales_value kpi_value">
                                {{ data.current_percent_of_total_sales }}% &bull; <small>{{ data.all_time_percent_of_total_sales }}%</small>
                            </div>
                            <div class="saff_kpi_total_sales_text kpi_text">
                                <?php echo __('of Total Sales', SAFF_TEXT_DOMAIN); ?>
                            </div>
                        </div>
                        <div class="kpi_container saff_kpi_refunds column_2">
                            <div class="saff_kpi_refunds_value kpi_value">
                                <?php echo SAFF_CURRENCY; ?>{{ data.stats.current.affiliates_refund }} &bull; <small><?php echo SAFF_CURRENCY; ?>{{ data.stats.all_time.affiliates_refund }}</small>
                            </div>
                            <div class="saff_kpi_refunds_text kpi_text">
                                <?php echo __('Refunds', SAFF_TEXT_DOMAIN); ?>
                            </div>
                        </div>
                        <div class="kpi_container saff_kpi_unpaid_commissions column_3">
                            <div class="saff_kpi_unpaid_commissions_value kpi_value">
                                <?php echo SAFF_CURRENCY; ?>{{ data.stats.current.unpaid_commissions }} &bull; <small><?php echo SAFF_CURRENCY; ?>{{ data.stats.all_time.unpaid_commissions }}</small>
                            </div>
                            <div class="saff_kpi_unpaid_commissions_text kpi_text">
                                <?php echo __('Unpaid Commissions', SAFF_TEXT_DOMAIN); ?>
                            </div>
                        </div>
                    </div>
                    <div class="aff_stats_row_2">
                        <div class="kpi_container saff_kpi_customers column_1">
                            <div class="saff_kpi_customers_value kpi_value">
                                {{ data.stats.current.customers_count }} &bull; <small>{{ data.stats.all_time.customers_count }}</small>
                            </div>
                            <div class="saff_kpi_customers_text kpi_text">
                                <?php echo __('Customers', SAFF_TEXT_DOMAIN); ?>
                            </div>
                        </div>
                        <div class="kpi_container saff_kpi_visitors column_2">
                            <div class="saff_kpi_visitors_value kpi_value">
                                {{ data.stats.current.visitors_count }} &bull; <small>{{ data.stats.all_time.visitors_count }}</small>
                            </div>
                            <div class="saff_kpi_visitors_text kpi_text">
                                <?php echo __('Visitors', SAFF_TEXT_DOMAIN); ?>
                            </div>
                        </div>
                        <div class="kpi_container saff_kpi_commissions_earned column_3">
                            <div class="saff_kpi_commissions_earned_value kpi_value">
                                <?php echo SAFF_CURRENCY; ?>{{ data.stats.current.commissions_earned }} &bull; <small><?php echo SAFF_CURRENCY; ?>{{ data.stats.all_time.commissions_earned }}</small>
                            </div>
                            <div class="saff_kpi_commissions_earned_text kpi_text">
                                <?php echo __('Commissions Earned', SAFF_TEXT_DOMAIN); ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="saff_details_aff_payment_container">
                    <!--
                    <div class="saff_details_aff_payment">
                        
                        <div class="saff_payment_action_container">
                            <input type="button" class="button-primary" id="pay_now_button" name="pay_now_button" value="<?php echo __('Pay Now', SAFF_TEXT_DOMAIN); ?>" />
                        </div>
                        
                        <div class="saff_payment_description">
                            <div class="saff_payment_description_row_1">
                               <# if(data.last_payout_details.amount > 0) { #> <?php echo sprintf(__('Last paid %s%s on %s via %s', SAFF_TEXT_DOMAIN), SAFF_CURRENCY, '{{ data.last_payout_details.amount }}', '{{ data.last_payout_details.date }}', '{{ data.last_payout_details.gateway }}'); ?> <# } #>
                            </div>
                            <div class="saff_payment_description_row_2">
                                <small><a id="saff_orders" class="cursor_pointer active_link"><?php echo __('Orders', SAFF_TEXT_DOMAIN); ?></a> <a id="saff_add_payout" class="cursor_pointer"><?php echo __('Add Payout', SAFF_TEXT_DOMAIN); ?></a> <a id="saff_payout_history" class="cursor_pointer"><?php echo __('Payout History', SAFF_TEXT_DOMAIN); ?></a> </small>
                            </div>
                        </div>
                        
                        
                    </div>
                     -->
                    <div class="saff_payment_description">
                        <h2 class="saff-nav-tab-wrapper saff-nav-tab-wrapper">
                            <small><a id="saff_orders" class="cursor_pointer saff-nav-tab saff-nav-tab-active"><?php echo __('Orders', SAFF_TEXT_DOMAIN); ?></a> <a id="saff_payouts" class="cursor_pointer saff-nav-tab"><?php echo __('Payouts', SAFF_TEXT_DOMAIN); ?></a></small>
                        </h2> 
                    </div>
                </div>
                <div id="add_payout_form" class="saff_details_form_container" >

                </div>
                <div id="payout_form" class="saff_details_form_container" >

                </div>
                <div class="saff_details_dynamic_container">
                    <div id="saff_form_table" class="saff_details_form_container">
                        <div class="saff_form_table_action">
                            <span class="saff_filter_commission_status_span">
                                <?php echo __('Filter Orders By', SAFF_TEXT_DOMAIN); ?>:&nbsp;
                                <select id="saff_filter_commission_status" class="saff_filter_commission_status" name="saff_filter_commission_status">
                                    <option value="all"><?php echo __('All', SAFF_TEXT_DOMAIN); ?></option>
                                    <optgroup label="<?php echo __('Order Status', SAFF_TEXT_DOMAIN); ?>">
                                        <?php foreach ($wc_order_statuses as $key => $order_status) { ?>
                                            <option value="<?php echo "os-". $key; ?>"><?php echo $order_status; ?></option>
                                        <?php } ?>
                                    </optgroup>
                                    <optgroup label="<?php echo __('Commission Status', SAFF_TEXT_DOMAIN); ?>">
                                        <?php foreach ($saff_commission_statuses as $key => $commission_status) { ?>
                                            <option value="<?php echo "cs-". $key; ?>"><?php echo $commission_status; ?></option>
                                        <?php } ?>
                                    </optgroup>
                                </select> &nbsp;
                            </span>
                            <span class="saff_commission_status_span">
                                <?php echo __('Update Order Commissions', SAFF_TEXT_DOMAIN); ?>:&nbsp;
                                <select id="saff_commission_status" class="saff_commission_status" name="saff_commission_status">
                                    <?php foreach ($saff_commission_statuses as $key => $commission_status) { ?>
                                            <option value="<?php echo $key; ?>"><?php echo $commission_status; ?></option>
                                    <?php } ?>
                                </select> &nbsp;
                                <input type="button" class="button" id="saff_status_change_button" name="saff_change_status" value="<?php echo __('Apply', SAFF_TEXT_DOMAIN); ?>"  />
                            </span>


                        </div>
                        <div class="saff-affiliates-orders">

                        </div>    
                    </div>   
                </div>
                <div id="saff-payout-history" class="saff-payout-history">
                    <!-- Template Payout History Will Load Here -->
                </div>
                    

            </script>
            <!-- Template Affiliate Details Over!-->
            <?php
        }

        function get_dashboard_data() {
            global $wpdb;

            $from_date = trim($_POST['from_date']);
            $to_date = trim($_POST['to_date']);

            $from = date('d-M-Y', strtotime($from_date));
            $to = date('d-M-Y', strtotime($to_date));

            $affiliate_ids = $wpdb->get_col("SELECT DISTINCT affiliate_id FROM {$wpdb->prefix}saff_hits AS hits WHERE hits.datetime >= '" . date('Y-m-d', strtotime($from)) . ' 00:00:00' . "' AND hits.datetime <= '" . date('Y-m-d', strtotime($to)) . ' 23:59:59' . "'");

            $all_customer_ids = array();
            $all_customer_ids = apply_filters('saff_all_customer_ids', $all_customer_ids, array('from' => $from, 'to' => $to));

            $pname = get_option( 'saff_pname' );
            $pname = ( ! empty( $pname ) ) ? $pname : 'ref';

            $net_affiliates_sales = 0;
            $total_sales = 0;
            $all_time_total_sales = 0;
            $unpaid_commissions = 0;
            $visitors_count = 0;
            $customers_count = 0;


            $smart_affiliates = array();
            foreach ($affiliate_ids as $affiliate_id) {
                
                $actual_affiliate_id = get_affiliate_id_based_on_user_id($affiliate_id);

                $current_data = new Saff_Admin_Affiliates($affiliate_id, $from, $to);
                $net_affiliates_sales = $net_affiliates_sales + $current_data->net_affiliates_sales;
                $total_sales = $current_data->storewide_sales;
                $unpaid_commissions = $unpaid_commissions + $current_data->unpaid_commissions;
                $visitors_count = $visitors_count + $current_data->visitors_count;
                $customers_count = $customers_count + $current_data->customers_count;
                $all_time_data = new Saff_Admin_Affiliates($affiliate_id);
                $all_time_total_sales = $all_time_data->storewide_sales;

                if (!isset($smart_affiliates['affiliates'][$affiliate_id])) {
                    $smart_affiliates['affiliates'][$affiliate_id] = array();
                }

                $smart_affiliates['affiliates'][$affiliate_id]['name'] = $current_data->affiliates_display_names[$affiliate_id];
                $smart_affiliates['affiliates'][$affiliate_id]['affiliate_id'] = $affiliate_id;
                $smart_affiliates['affiliates'][$affiliate_id]['edit_url'] = admin_url('user-edit.php?user_id=' . $affiliate_id);
                $smart_affiliates['affiliates'][$affiliate_id]['referral_url'] = add_query_arg($pname, $actual_affiliate_id, home_url('/'));
                $smart_affiliates['affiliates'][$affiliate_id]['paypal_email'] = get_user_meta($affiliate_id, 'saff_paypal_email', true);
                $smart_affiliates['affiliates'][$affiliate_id]['avatar_url'] = $this->get_avatar_url(get_avatar($affiliate_id, 32));
                $smart_affiliates['affiliates'][$affiliate_id]['last_payout_details'] = $current_data->get_last_payout_details();
                $smart_affiliates['affiliates'][$affiliate_id]['formatted_join_duration'] = $current_data->get_formatted_join_duration();
                $smart_affiliates['affiliates'][$affiliate_id]['stats']['current'] = $current_data;
                $smart_affiliates['affiliates'][$affiliate_id]['stats']['all_time'] = $all_time_data;
                $smart_affiliates['affiliates'][$affiliate_id]['orders_details'] = $current_data->get_affiliates_order_details();
                $smart_affiliates['affiliates'][$affiliate_id]['payout_history'] = $current_data->get_affiliates_payout_history();
            }
            
            $percent_of_total_sales = ( $total_sales > 0 ) ? $net_affiliates_sales * 100 / $total_sales : 0;
            $conversion_rate = ($visitors_count > 0) ? $customers_count * 100 / $visitors_count : 0;

            $smart_affiliates['kpi']['net_affiliates_sales'] = saff_format_price($net_affiliates_sales);
            $smart_affiliates['kpi']['total_sales'] = $total_sales;
            $smart_affiliates['kpi']['all_time_total_sales'] = $all_time_total_sales;
            $smart_affiliates['kpi']['unpaid_commissions'] = $unpaid_commissions;
            $smart_affiliates['kpi']['visitors_count'] = $visitors_count;
            $smart_affiliates['kpi']['customers_count'] = $customers_count;
            $smart_affiliates['kpi']['all_customers_count'] = count($all_customer_ids);
            $smart_affiliates['kpi']['percent_of_total_sales'] = round($percent_of_total_sales, 2);
            $smart_affiliates['kpi']['conversion_rate'] = $conversion_rate;
            
            
            echo json_encode($smart_affiliates);
            unset($smart_affiliates);
            die();
        }

        function change_commission_status() {

            check_ajax_referer(SAFF_AJAX_SECURITY, 'security');

            $order_ids = (!empty($_POST['ids']) ) ? $_POST['ids'] : array();

            $new_status = (!empty($_POST['status']) ) ? $_POST['status'] : '';

            if (empty($order_ids)) {
                echo json_encode(array('error' => __('No orders selected.', SAFF_TEXT_DOMAIN)));
                die();
            }

            if (empty($new_status)) {
                echo json_encode(array('error' => __('Status empty.', SAFF_TEXT_DOMAIN)));
                die();
            }

            global $wpdb;

            $query = "UPDATE " . get_saff_tablename('referrals') . " SET status = '{$new_status}' WHERE post_id IN ( " . implode(',', $order_ids) . " )";

            $records = $wpdb->query($query);

            if ($records === false) {
                echo json_encode(array('error' => sprintf(__('Query failed. Query: %s', SAFF_TEXT_DOMAIN), $query)));
            } else {
                echo json_encode(array('success' => sprintf(__('%d records updated.', SAFF_TEXT_DOMAIN), $records)));
            }
            die();
        }

        function add_payout() {
            check_ajax_referer(SAFF_AJAX_SECURITY, 'security');
            $affiliate_id = (isset($_POST['affiliate_id'])) ? $_POST['affiliate_id'] : '';

            if (!empty($affiliate_id)) {
                global $wpdb;
                $commission = (isset($_POST['commission'])) ? $_POST['commission'] : 0;
                $payment_method = (isset($_POST['payment_method'])) ? $_POST['payment_method'] : 'other';
                $note = (isset($_POST['note'])) ? $_POST['note'] : '';
                $datetime = date('Y-m-d H:i:s', time());
                $datetime = (isset($_POST['date'])) ? date('Y-m-d H:i:s', strtotime($_POST['date'])) : $datetime;

                $single = true;
                $receiver = get_user_meta($affiliate_id, 'saff_paypal_email', $single);
                unset($single);
                $type = '';
                
                $table_name = get_saff_tablename('payouts');
                $currency = get_woocommerce_currency();
                
                $query = $wpdb->prepare("INSERT INTO {$table_name} (`affiliate_id`, `datetime`, `amount`,
                                                                 `currency`, `payout_notes`, `payment_gateway`,
                                                                 `receiver`, `type`) VALUES ( %d, %s, %s, %s, %s, %s, %s, %s )", $affiliate_id, $datetime , $commission, $currency , $note, $payment_method, $receiver, $type);

                $records = $wpdb->query($query);
                
                if ($records === false) {
                    echo json_encode(array('error' => sprintf(__('Query failed. Query: %s', SAFF_TEXT_DOMAIN), $query)));
                } else {
                    $inserted_payout_id = $wpdb->insert_id;
                    $added_payout = array(
                        
                        'affiliate_id' => $affiliate_id,
                        'order_id' => null,
                        'datetime' => date( 'd-M-Y' , strtotime($datetime)) ,
                        'amount' => $commission,
                        'currency' => $currency,
                        'payout_notes' => $note,
                        'method' => $payment_method,
                        'receiver' => $receiver
                    );
                    echo json_encode(array('success' => sprintf(__('%d records inserted.', SAFF_TEXT_DOMAIN), $records),
                             'last_added_payout_id'  => $inserted_payout_id,
                             'last_added_payout_data' => $added_payout
                    ));
                }
                die();
            }
        }

        function make_payment() {
            check_ajax_referer(SAFF_AJAX_SECURITY, 'security');
                        
            $affiliates = (!empty($_POST['affiliates']) ) ? $_POST['affiliates'] : array();
            $currency = (!empty($_POST['currency']) ) ? $_POST['currency'] : array();

            if (empty($affiliates)) {
                echo json_encode(array('ACK' => 'Error', 'error' => __('Affiliates list empty', SAFF_TEXT_DOMAIN)));
                die();
            }

            // For now, only checking for 1st Affiliate, Multiple Affiliates Payout is not yet implemented
            if (isset($affiliates[0]['email']) && !empty($affiliates[0]['email'])) {
                if (isset($affiliates[0]['amount']) && !empty($affiliates[0]['amount'])) {
                    $paypal = new Saff_Paypal();
                    $result = $paypal->process_paypal_mass_payment($affiliates, $currency);
                    if ($result['ACK'] == 'Success') {
                        $orders = (!empty($_POST['selected_orders']) ) ? $_POST['selected_orders'] : array();
                        if (count($orders) > 0) {
                            global $wpdb;
                            
                            
                            foreach ($orders as $order ) {
                                $order_ids[] = $order['order_id'];
                            }
                            
                            $query = "UPDATE " . get_saff_tablename('referrals') . " SET status = '" . SAFF_REFERRAL_STATUS_PAID . "' WHERE post_id IN ( " . implode(',', $order_ids) . " )";
                            $records = $wpdb->query($query);

                            if ($records === false) {
                                echo json_encode(array('error' => sprintf(__('Query failed. Query: %s', SAFF_TEXT_DOMAIN), $query)));
                            } else {
                                $payouts_table = get_saff_tablename('payouts');
                                $affiliate_id = $affiliates[0]['id'];
                                $datetime = date('Y-m-d H:i:s');
                                $amount = $affiliates[0]['amount'];
                                $currency = 'USD';
                                $payout_notes = $affiliates[0]['note'];
                                $receiver = $affiliates[0]['email'];
                                $payment_gateway = 'paypal';
                                $type = '';
                                
                                $wpdb->insert($payouts_table, array(
                                    'affiliate_id' => $affiliate_id,
                                    'datetime' => $datetime,
                                    'amount' => $amount,
                                    'currency' => $currency,
                                    'payout_notes' => $payout_notes,
                                    'payment_gateway' => $payment_gateway,
                                    'receiver' => $receiver,
                                    'type' => $type
                                ));
                                
                                // Last inserted payout id
                                $payout_id = $wpdb->insert_id;
                                
                                if(!empty($payout_id)) {
                                    $payout_orders_table = get_saff_tablename('payout_orders');
                                    foreach ($orders as $order ) {
                                        $wpdb->insert($payout_orders_table, array(
                                            'payout_id' => $payout_id,
                                            'post_id' => $order['order_id'],
                                            'amount' => $order['commission']
                                        ));
                                    }
                                }
                            }
                        }
                    } else {
                        $result = array('ACK' => 'Error', 'error' => $result[L_LONGMESSAGE0]);
                    }
                } else {
                    $result = array('ACK' => 'Error', 'error' => __('Amount Should Greater Than 0', SAFF_TEXT_DOMAIN));
                }
            } else {
                $result = array('ACK' => 'Error', 'error' => __('Affiliate Paypal account is required', SAFF_TEXT_DOMAIN));
            }


            echo json_encode($result);
            die();
        }

        function get_avatar_url($get_avatar) {
            preg_match("/src='(.*?)'/i", $get_avatar, $matches);
            return $matches[1];
        }

    }

}

return new Saff_Admin_Dashboard();