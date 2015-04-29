/*global Backbone */
var App = App || {};

(function() {
    'use strict';

    // Person Model
    App.Models.Dashboard = Backbone.Model.extend({
        defaults: {
            filter_options: { today: 'Today', 
                          yesterday: 'Yesterday', 
                          this_week: 'This week', 
                          last_week: 'Last week', 
                          this_month: 'This month',
                          last_month: 'Last month',
                          three_months: '3_months', 
                          six_months: '6 months', 
                          this_year: 'This year', 
                          last_year: 'Last year'
                        },
                        
            kpi: {net_affiliate_sales: '$100', total_sales : '$500', unpaid_commisions: '$500', visitors: 50, customers: 10, customer_percent: 2}
        }
    });
    
})();
