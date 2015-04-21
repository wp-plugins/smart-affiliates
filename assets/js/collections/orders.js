var App = App || {};

(function() {
    'use strict';

    App.Collections.Orders = Backbone.Collection.extend({
        model: App.Models.Order,

        // Sort orders based on date
        comparator: function( model ){
            return -model.get('datetime'); // '-' indicated that sort in decending order. 
        },
        
        filter_orders_by_status: function(status) {

            // os - order status, cs - commission status
            var filter_status = 'status';
            if(status.indexOf("os-") > -1) {
                filter_status = 'order_status';
                status = status.replace('os-', '');
            } else if(status.indexOf("cs-") > -1) {
                filter_status = 'status';
                status = status.replace('cs-', '');
            }
            
            
            var filtered = this.filter(function(order) {
                return order.get(filter_status) === status;
            });
            
            return new App.Collections.Orders(filtered);
        },
        
        getSelectedOrders: function() {
            var filtered = this.filter(function(order) {
                return order.get("selected") === true;
            });
            
            return new App.Collections.Orders(filtered);
        },
        
        totalCommissions: function() {
            return this.reduce(function(memo, value) { 
                return parseFloat(memo) + parseFloat(value.get('stats').current.commissions_earned); 
            },0);
        }
    });




})();

