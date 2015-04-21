var App = App || {};

(function() {
    'use strict';

    App.Collections.Affiliates = Backbone.Collection.extend({
        model: App.Models.Affiliate,

        // Sort affiliate list based on total commission earned
        comparator: function( model ){
            var stats = model.get('stats');
            return -stats.current.commissions_earned; // '-' indicated that sort in decending order. 
        },
        
        search: function(search_text) {
            
            if(search_text === '') {
                return this;
            }
            
            // make it lowercase and search into the lowercase name also
            search_text = search_text.toLowerCase();
            var pattern = new RegExp(search_text);
            var filtered = this.filter(function(affiliate) {
                //return affiliate.get("name") === search_text;
                return pattern.test(affiliate.get("name").toLowerCase());
            });
            
            return new App.Collections.Affiliates(filtered);
        },
        
        totalEarned: function() {
            return this.reduce(function(memo, value) {
                //return parseFloat(memo) + parseFloat(value.get('stats').current.commissions_earned); 
                return memo + value.get('stats').current.commissions_earned; 
            },0);
        },
        
        totalUnpaidCommission: function() {
            return this.reduce(function(memo, value) {
                //return parseFloat(memo) + parseFloat(value.get('stats').current.commissions_earned); 
                return memo + value.get('stats').current.unpaid_commissions; 
            },0);
        }

    });




})();

