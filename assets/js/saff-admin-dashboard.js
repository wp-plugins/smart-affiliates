;jQuery(function() {
    jQuery('#saff_dashboard_from, #saff_dashboard_to').datepicker({
        dateFormat: 'dd-M-yy'
    });
    
    jQuery(document).ready(function(){


	    jQuery('.saff-affiliates-orders').on('click', 'input[id^="order_id_"]', function(){
	    	var prevValue = parseFloat(jQuery('#saff_commissions_to_pay').text());
	    	var commission = parseFloat(jQuery(this).closest('.saff_order_commission').text());
	    	var newValue = prevValue + commission;
	    	jQuery('#saff_commissions_to_pay').text(newValue);
	    });
            
    });


});




