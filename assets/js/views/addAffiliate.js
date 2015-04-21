/*global Backbone, jQuery, _, ENTER_KEY */
var App = App || {};

(function($) {
    'use strict';

    // View for all people
    App.Views.AddAffiliate = Backbone.View.extend({
	el: '#addPerson',

	events: {
		'submit': 'submit'
	},

	submit: function(e) {
		e.preventDefault();
		var newPersonName = $(e.currentTarget).find('input[type=text]').val();
		var person = new App.Models.Affiliate({ name: newPersonName });
		this.collection.add(person);

	}
});

})(jQuery);

