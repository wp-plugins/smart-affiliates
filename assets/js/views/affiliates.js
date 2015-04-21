/*global Backbone, jQuery, _, ENTER_KEY */
var App = App || {};

(function($) {
    'use strict';

    // View for all people
    App.Views.Affiliates = wp.Backbone.View.extend({
        tagName: 'ul',
        className: 'saff_lists',
        
        events: {
        },
        
        initialize: function() {
            this.collection.on('add', this.addOne, this);
        },
        
        render: function() {
            
            // Sort calls the comparator function of a collection
            this.collection.sort();
            this.collection.each(this.addOne, this);

            // Total Commissions earned by all affiliates
            //var total_earned = this.collection.totalEarned();
            //total_earned = Math.round(total_earned * 100) / 100;
            
            var total_unpaid = this.collection.totalUnpaidCommission();
            total_unpaid = Math.round(total_unpaid * 100) / 100;
            
            //$('#total_commission_earned').html('$' + total_earned);
            $('#total_commission_earned').html(saff_dashboard_js_var.saff_currency_symbol + total_unpaid);
            // Load First Afiliates Details
            this.$el.children().find('.affiliate_container').first().trigger('click');
            return this;
        },
        
        addOne: function(affiliate) {
            //this.$el.html(this.template(affiliate.toJSON()));
            //var template_data = this.template(affiliate.toJSON());
            var personView = new App.Views.Affiliate({model: affiliate});
            this.$el.append(personView.render().el);
        }
       
    });
})(jQuery);