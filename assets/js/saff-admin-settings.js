;jQuery(function() {
    
    jQuery(document).ready(function(){
            
            // ajax user search
	jQuery('.affwp-user-search').keyup(function() {
		var user_search = jQuery(this).val();
		jQuery('.affwp-ajax').show();
		data = {
			action: 'saff_search_users',
			user_name: user_search
		};

		jQuery.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: ajaxurl,
			success: function (search_response) {

				jQuery('.affwp-ajax').hide();

				jQuery('#affwp_user_search_results').html('');

				jQuery(search_response.results).appendTo('#affwp_user_search_results');

				if( jQuery('.affwp-woo-coupon-field').length ) {
					var height = jQuery('.affwp-woo-coupon-field #affwp_user_search_results' ).height();
					jQuery('.affwp-woo-coupon-field #affwp_user_search_results').css('top', '-' + height + 'px' );
				}
			}
		});
	});

    });


});




