/*global Backbone, jQuery, _, ENTER_KEY */
var App = App || {};

(function($) {
    'use strict';

    // View for all people
    App.Views.AffiliateStats = wp.Backbone.View.extend({
        
    template: wp.template('affiliate-stats'),
        
        initialize: function() {
            
        },
        
        render: function() {
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        },
        
        addOne: function(person) {
            
        }
    });
})(jQuery);