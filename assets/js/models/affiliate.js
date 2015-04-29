/*global Backbone */
var App = App || {};

(function() {
    'use strict';

    // Person Model
    App.Models.Affiliate = Backbone.Model.extend({
        defaults: {
        },
        initialize: function() {
            var order_details = this.get('orders_details');
            var orders = [];
            for (var root in order_details) {
                orders.push(order_details[root]);
            }
            
            var payment_details = this.get('payout_history');
            var payments = [];
            for (var root in payment_details) {
                payments.push(payment_details[root]);
            }

            this.set({
                orders: new App.Collections.Orders(orders),
                payments: new App.Collections.Payments(payments)
            });
        }
    });

})();
