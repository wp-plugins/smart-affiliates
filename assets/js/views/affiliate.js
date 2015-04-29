/*global Backbone, jQuery, _, ENTER_KEY */
var App = App || {};

(function($) {
    'use strict';
// The View for a Person
    App.Views.Affiliate = wp.Backbone.View.extend({
        tagName: 'li',
        className: 'saff_aff_item',
        
        template: wp.template('affiliate-lists'),
        
        initialize: function() {
            
        },
        
        events: {
            'click .affiliate_container': 'showDetails'
        },
        
        render: function() {
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        },
        
        showDetails: function() {
            
            $('.saff_details').html('');
            
            //Highlight Selected Affiliate List
            this.$el.siblings('li.aff_highlight').removeClass('aff_highlight');
            this.$el.addClass('aff_highlight');
            
            //Display Affiliate Details
            var affiliateDetailsView = new App.Views.AffiliateDetails({ model: this.model });
            $('.saff_details').html(affiliateDetailsView.render().el);
        }
    });
})(jQuery);