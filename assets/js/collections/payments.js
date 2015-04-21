var App = App || {};

(function() {
    'use strict';

    App.Collections.Payments = Backbone.Collection.extend({
        model: App.Models.Order
    });




})();

