/*global Backbone, jQuery, _, ENTER_KEY */
var App = App || {};

(function($) {
    'use strict';

    // View for all people
    App.Views.AffiliateDetails = wp.Backbone.View.extend({
        template: wp.template('saff-details'),
        initialize: function() {
            _.bindAll(this, 'render');
            if (this.model) {
                //this.model.on('change', this.render, this);
            }
        },
        events: {
            'change #saff_filter_commission_status': 'filterOrders',
            'click #saff_status_change_button': 'changeStatus',
            'click input.order_checkbox': 'checkedOrders',
            'click #saff_add_payout': 'showAddPayoutForm',
            'click #saff_payouts': 'showPayouts',
            'click #saff_orders': 'showOrdersCommission',
            'click #pay_now_button': 'showPayoutForm',
            'click #payout_button': 'processPayout',
            'click #add_payout_button': 'addPayout',
            'click #saff_payout_history': 'showPayoutHistory',
            'click #select_all_orders': 'selectOrders',
            'click #close_add_payout_form' : 'close',
            'click .order_checkbox' : 'selectOrder'
        },
        
        render: function() {
            var totalSales = parseFloat( App.globalData.kpi.total_sales );
            var allTimeTotalSales = parseFloat( App.globalData.kpi.all_time_total_sales );
            var stats = this.model.get('stats');
            
            var currentAffiliatesSales = parseFloat( stats.current.affiliates_sales );
            var allTimeAffiliatesSales = parseFloat( stats.all_time.affiliates_sales );
            var currentPercentOfTotalSales = currentAffiliatesSales * 100 / totalSales;
            var allTimePercentOfTotalSales = allTimeAffiliatesSales * 100 / allTimeTotalSales;
            this.model.set('current_percent_of_total_sales', currentPercentOfTotalSales.toFixed(2));
            this.model.set('all_time_percent_of_total_sales', allTimePercentOfTotalSales.toFixed(2));
            this.$el.html(this.template(this.model.toJSON()));
            //this.renderOrders(this.model);
            this.showOrdersCommission();
            
            return this;
        },
        
        filterOrders: function() {
            var status = $.trim($("#saff_filter_commission_status").val());
            if (status === 'all') {
                var model = this.model;
            } else {
                // Copy by value. actual module won't change by changing into model variable
                var model = $.extend(true, {}, this.model);
                var orders = model.get('orders');
                var filtered_orders = orders.filter_orders_by_status(status);
                model.set('orders', filtered_orders);
            }
            /*
             this.$el.html(this.template(model.toJSON()));
             var templates = wp.template('affiliates-orders');
             this.$el.find('.saff_details_form_container').append(templates({orders: model.get('orders')}));
             */
            this.renderOrders(model);
            $("#saff_filter_commission_status").val(status);
            return this;
        },
        
       
        
        selectOrder: function() {
            var orders = this.model.get('orders');
            var selectedOrders = orders.getSelectedOrders();
            this.setTotalCommissions(selectedOrders);
        },
        
        setTotalCommissions: function(selectedOrders) {
            this.total_commissions = this.calculateTotalCommission(selectedOrders);
            //this.$el.find('#saff_commissions_to_pay').text(this.total_commissions);
            this.$el.find('#add_payout_commission_paid').val(this.total_commissions);
        },
        
        selectOrders: function() {
            var select_all = this.$el.find('#select_all_orders')[0];
            var checked = (select_all.checked) ? true : false;
            var orderCollection = this.model.get('orders');
            this.$el.find('.order_checkbox').each(function() { //loop through each checkbox
                var order_id = this.value;
                var order = orderCollection.where({order_id: order_id})[0];
                if(order.get('selected') != checked) {
                    order.toggleSelect();
                }    
                this.checked = checked;  //select all checkboxes              
            });
            
            this.selectOrder();
        },
        
        showAddPayoutForm: function() {
            //add_payout_form
            this.$el.find('#payout_form').hide();
            
            this.$el.find('#saff_add_payout').addClass('saff-nav-tab-active');
            this.$el.find('#saff_payout_history').removeClass('saff-nav-tab-active');
            this.$el.find('#saff_orders').removeClass('saff-nav-tab-active');
            
            // Render Add Payout Form
            var templates = wp.template('add-payout-form');
            this.$el.find('#add_payout_form').html(templates);
            this.$el.find('#add_payout_form').show();
            
            this.$el.find('#saff_add_payout_date').val($.datepicker.formatDate('dd-M-yy', new Date()));
            this.$el.find('#saff_add_payout_date').datepicker({
                    dateFormat: 'dd-M-yy'
            });
            
            // Render Payout History
            var templates = wp.template('payout-history');
            this.$el.find('.saff_details_dynamic_container').hide();
            this.renderPayoutHistory(this.model);
        },
        
        showOrdersCommission: function() {
            // Hide Payout form if is visible
            this.$el.find('#add_payout_form').hide();
            this.$el.find('#saff-payout-history').hide();
            
            // Render Add Payout Form
            var templates = wp.template('add-payout-form');
            this.$el.find('#add_payout_form').html(templates);
            this.$el.find('#add_payout_form').show();
            
            this.$el.find('#saff_add_payout_date').val($.datepicker.formatDate('dd-M-yy', new Date()));
            this.$el.find('#saff_add_payout_date').datepicker({
                    dateFormat: 'dd-M-yy'
            });
            
            this.$el.find('#saff_orders').addClass('saff-nav-tab-active');
            this.$el.find('#saff_payouts').removeClass('saff-nav-tab-active');
            //this.$el.find('#saff_add_payout').removeClass('saff-nav-tab-active');
            
            this.renderOrders(this.model);
        },
        
        showPayouts: function() {
            //add_payout_form
            this.$el.find('#payout_form').hide();
            this.$el.find('#add_payout_form').hide();
            this.$el.find('#saff_payouts').addClass('saff-nav-tab-active');
            this.$el.find('#saff_orders').removeClass('saff-nav-tab-active');
            
            // Render Payout History
            var templates = wp.template('payout-history');
            this.$el.find('.saff_details_dynamic_container').hide();
            this.renderPayoutHistory(this.model);
        },
        
        showPayoutHistory: function() {
            var templates = wp.template('payout-history');
            
            this.$el.find('#saff_payout_history').addClass('saff-nav-tab-active');
            this.$el.find('#saff_add_payout').removeClass('saff-nav-tab-active');
            this.$el.find('#saff_orders').removeClass('saff-nav-tab-active');
            
            this.$el.find('#add_payout_form').hide();
            this.$el.find('.saff_details_dynamic_container').hide();
            this.renderPayoutHistory(this.model);
        },
        
        renderOrders: function(model) {
            var templates = wp.template('affiliates-orders');
            this.$el.find('.saff-affiliates-orders').html(templates({orders: model.get('orders')}));
            this.$el.find('.saff_details_dynamic_container').show();
        },
        
        renderPayoutHistory: function(model) {
           var templates = wp.template('payout-history');
           this.$el.find('.saff-payout-history').html(templates({payout_history: model.get('payout_history')}));
           this.$el.find('#saff-payout-history').show();
        },
        
        updateStatus: function(status) { 
            if(!status) {
                var status = $.trim($('#saff_commission_status').val());
            }    
            var orders = this.model.get('orders');
            var selectedOrders = orders.getSelectedOrders();
            var selected_order_ids = [];
            var order_id;
            selectedOrders.each(function(order) {
                order_id = order.get('order_id');
                selected_order_ids.push(order_id);
                this.model.get('orders').where({order_id: order_id})[0].set({status: status, selected: false});
            }, this);

            App.spinner.show();
            $.ajax({
                context: this,
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'change_commission_status',
                    security: saff_dashboard_js_var.security_text,
                    ids: selected_order_ids,
                    status: status
                },
                success: function(response) {
                    // FilteredOrders function will filter collections based on set filter and then show orders
                    this.filterOrders();
                    App.spinner.hide();
                }
            });
        },
        
        changeStatus: function() {
            this.updateStatus();
        },
        
        updateUnpaidCommission: function(status, commission) {
            var stats = this.model.get('stats');
            var unpaid_commissions = stats.current.unpaid_commissions;
            stats.current.unpaid_commissions = 20;
            this.model.set('stats', stats);
            this.render();
        },
        
        checkedOrders: function(event) {
            var order_id = event.currentTarget.value;
            this.setCheckedOrders(order_id);
        },
        
        setCheckedOrders: function(order_id) {
            var orderCollection = this.model.get('orders');
            var order = orderCollection.where({order_id: order_id})[0];
            order.toggleSelect();
        },
        
        calculateTotalCommission: function(selectedOrders) {
            var total_commissions = 0;
            var commission = 0;
            var order_id;
            var selected_order_ids = [];
            selectedOrders.each(function(order) {
                order_id = order.get('order_id');
                if(order.get('commission') === null) {
                    commission = 0;
                } else {
                    commission = order.get('commission');
                }
                
                if(order.get('status') == 'unpaid') {
                    total_commissions = parseFloat(total_commissions) + parseFloat(commission);
                    selected_order_ids.push(order_id);
                }    
            }, this);
            
             
            total_commissions = total_commissions.toFixed(2);
            return total_commissions;
        },
        
        showPayoutForm: function() {
            var orders = this.model.get('orders');
            var selectedOrders = orders.getSelectedOrders();
            var selected_order_ids = [];
            var order_id;
            
            /*
            var total_commissions = 0;
            var commission = 0;
            selectedOrders.each(function(order) {
                order_id = order.get('order_id');
                if(order.get('commission') === null) {
                    commission = 0;
                } else {
                    commission = order.get('commission');
                }
                
                if(order.get('status') == 'unpaid') {
                    total_commissions = parseFloat(total_commissions) + parseFloat(commission);
                    selected_order_ids.push(order_id);
                }    
            }, this);
            */
            this.total_commissions = this.calculateTotalCommission(selectedOrders);
            this.selected_order_ids = selected_order_ids;
            
            var templates = wp.template('payout-form');
            this.$el.find('#payout_form').html(templates);
            this.$el.find('#saff_commissions_to_pay').text(this.total_commissions);
            this.$el.find('#add_payout_form').hide();
            this.$el.find('#payout_form').show();
            
            $.ajax({
                context: this,
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_paypal_balance',
                    security: saff_dashboard_js_var.security_text
                },
                success: function(response) {
                    if (response.amount != undefined && response.amount != '') {
                        this.$el.find('#saff_paypal_balance').text(response.amount);
                    }
                }
            });
        },
        
        processPayout: function() {
            var affiliates = [];
            var affiliate = {};
            affiliate.id = this.model.get('affiliate_id');
            affiliate.email = this.model.get('paypal_email');
            affiliate.amount = this.$el.find('#saff_commissions_to_pay').text();
            affiliate.unique_id = 'smart_affiliates_mass_payment';
            affiliate.note = this.$el.find('#payout_note').val();
            affiliates.push(affiliate);
            
            var orders = this.model.get('orders');
            var selectedOrders = orders.getSelectedOrders();
            var selected_order_ids = [];
            var order_id;
            var total_commissions = 0;
            var commission = 0;
            selectedOrders.each(function(order) {
                order_id = order.get('order_id');
                if(order.get('commission') === '') {
                    commission = 0;
                } else {
                    commission = order.get('commission');
                }
                
                total_commissions = parseFloat(total_commissions) + parseFloat(commission);
                //selected_order_ids.push(order_id);
                selected_order_ids.push({order_id: order_id, commission: commission});
                //this.model.get('orders').where({order_id: order_id})[0].set({status: status, selected: false});
            }, this);
            
            App.spinner.show();
            $.ajax({
                context: this,
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'make_payment',
                    security: saff_dashboard_js_var.security_text,
                    affiliates: affiliates,
                    currency: 'USD',
                    selected_orders: selected_order_ids
                },
                success: function(response) {
                    // FilteredOrders function will filter collections based on set filter and then show orders
                    if(response.ACK == 'Success') {
                        selectedOrders.each(function(order) {
                            order_id = order.get('order_id');
                            this.model.get('orders').where({order_id: order_id})[0].set({status: 'paid', selected: false});
                        }, this);
                        this.$el.find('#payout_form').hide();
                        this.filterOrders();
                    } else {
                        // Show Error Message
                        alert(response.error);
                    }    
                    App.spinner.hide();
                }
            });
        },
        addPayout: function() {
            var commission = $('#add_payout_commission_paid').val();
            var payment_method = $('#add_payout_payment_method').val();
            var note = $('#add_payout_note').val();
            var date = $('#saff_add_payout_date').val();
            var affiliate_id = this.model.get('affiliate_id');
            
            if(commission <= 0) {
                alert('Commission amount should be greater than 0');
                return;
            }
            $.ajax({
                context: this,
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'add_payout',
                    security: saff_dashboard_js_var.security_text,
                    commission: commission,
                    payment_method: payment_method,
                    note: note,
                    date: date,
                    affiliate_id: affiliate_id
                },
                success: function(response) {
                    var payout_history = this.model.get('payout_history');   
                    var last_added_payout_id = response.last_added_payout_id;
                    var last_added_payout_data = response.last_added_payout_data;
                    payout_history[last_added_payout_id] = last_added_payout_data;
                    this.updateStatus('paid');
                    this.resetPayoutForm();
                    //this.renderPayoutHistory(this.model);
                    
                    // FilteredOrders function will filter collections based on set filter and then show orders
                    //this.$el.find('#add_payout_form').hide();
                    //this.filterOrders();
                }
            });
        },
        
        resetPayoutForm: function() {
            this.$el.find('#add_payout_commission_paid').val('');
        },
        
        
        
        close: function() {
            $('#add_payout_form').hide();
            $('#payout_form').hide();
        }
    });
})(jQuery);