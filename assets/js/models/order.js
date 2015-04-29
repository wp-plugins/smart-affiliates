/*global Backbone */
var App = App || {};

(function() {
    'use strict';

    // Person Model
    App.Models.Order = Backbone.Model.extend({
        defaults: {
            selected: false
        },
        
        toggleSelect: function() {
            this.set('selected', !(this.get('selected')));
        },
        
        getSelectedOrders: function() {
            
        }
    });
    
})();
