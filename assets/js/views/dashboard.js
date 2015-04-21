/*global Backbone, jQuery, _, ENTER_KEY */
var App = App || {};

(function($) {
    'use strict';
// The View for a Person
    App.Views.Dashboard = wp.Backbone.View.extend({
        el: '#saff_dashboard',
        template: wp.template('dashboard'),
        model: App.Models.Dashboard,
        initialize: function() {
            App.spinner = this.$el.find('.saff_spinner');    
            var filter_options = {today: 'Today',
                yesterday: 'Yesterday',
                this_week: 'This week',
                last_week: 'Last week',
                this_month: 'This month',
                last_month: 'Last month',
                three_months: '3 months',
                six_months: '6 months',
                this_year: 'This year',
                last_year: 'Last year'
            };

            //var data = new App.Models.Dashboard({filter_options: filter_options});
            var template = wp.template('dashboard-header');
            //var template = _.template( $('#tmpl-dashboard-header').html());
            this.$el.find('#saff_dashboard_header').html(template({filter_options: filter_options}));

            // Load Current Month data on page load
            this.showDataSmartDates();
        },
        events: {
            'click #show_dashboard_data': 'showData',
            'change #saff_dashboard_smart_date_selector': 'showDataSmartDates',
            'keyup #saff_dashboard_search_text': 'search'
        },
        render: function() {
            //this.$el.html(this.template(this.model.toJSON()));
            //return this;
        },
        showDataSmartDates: function() {
            var selected = $('select#saff_dashboard_smart_date_selector').find('option:selected').val();
            var date_range = saff_dashboard_js_var.smart_dates[ selected ];
            $('input#saff_dashboard_from').val(date_range.from);
            $('input#saff_dashboard_to').val(date_range.to);
            this.showData();
        },
        showData: function() {
            
            // Set KPI
            //var kpi = {net_affiliate_sales: '$100', total_sales: '$500', unpaid_commisions: '$500', visitors: 50, customers: 10, customer_percent: 2};


            var from_date = this.$el.find('#saff_dashboard_from').val();
            var to_date = this.$el.find('#saff_dashboard_to').val();
            var action = 'get_dashboard_data';
            // TODO: Check how to get this url
            var affiliates, kpi;


            
            $('#saff_empty_container').hide();
            $('#saff_main_data_container').hide();
            App.spinner.show();
            $.ajax({
                context: this,
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {from_date: from_date, to_date: to_date, action: action},
                success: function(result) {
                    App.globalData = {
                        'kpi': result.kpi
                    };
                    
                    this.$el.find('#saff_main_data_container').show();
                    if(result.affiliates === undefined) {
                        this.$el.find('#saff_main_data_container').hide();
                        $('#saff_empty_container').show();
                    } else {
                        var newvalue = [];
                        for (var root in result.affiliates) {
                            newvalue.push(result.affiliates[root]);
                        }

                        var template = wp.template('dashboard-kpi');
                        this.$el.find('.saff_dashboard_kpi_container').html(template({kpi: result.kpi}));
                        var AffiliateCollection = new App.Collections.Affiliates(newvalue);
                        this.collection = AffiliateCollection;

                        //var addPersonView = new App.Views.AddAffiliate({collection: affiliateCollection});
                        this.renderAffiliateList(AffiliateCollection);
                        $('.saff_dashboard_main_view_container').show();
                        $('#saff_main_data_container').show();
                    }
                    App.spinner.hide();
                    
                }
            });
        },
        renderAffiliateList: function(collection) {
            var peopleView = new App.Views.Affiliates({collection: collection});
            this.$el.find('.saff_left_panel_lists').html('');
            this.$el.find('.saff_details').html('');
            this.$el.find('.saff_left_panel_lists').html(peopleView.render().el);
        },
        search: function() {
            var search_text = this.$el.find('#saff_dashboard_search_text').val();
            var affiliateCollection = this.collection.search(search_text);
            this.renderAffiliateList(affiliateCollection);
        }
    });
})(jQuery);