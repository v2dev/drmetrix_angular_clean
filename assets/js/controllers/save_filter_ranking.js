"use strict";
angular.module('drmApp').controller('SaveFilterRankingModalController', function($scope, $rootScope, $uibModalInstance, $state, apiService, $timeout){
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    var filter_array = [];
    var export_refine_apply_filter = 1;
    $scope.filter_error = "";
    $scope.alertClass = "";

    $scope.save_filter = function (tab, overwrite_flag, filter_id) {
    	$scope.filter_error = "";
        if (filter_id === undefined) {
            filter_id = false;
        }
        if (overwrite_flag === undefined) {
            overwrite_flag = 0;
        }
        var duplicate_found = 0;
        var filter_name = $('#filter_name_' + tab).val();
        if ($.inArray(filter_name, filter_array) != -1 && overwrite_flag == 1) {
            duplicate_found = 1;
        }
        filter_array.push(filter_name);
        var checkSplChar = $rootScope.cleanFileName(filter_name);
        if (filter_name == '' || checkSplChar == 1) {
            duplicate_filter = 1;
            //$("#filter_save_error_" + tab).show();
            //$("#filter_save_error_" + tab).addClass('filter_save_error');
            //$('#filter_save_error_' + tab).text('Invalid Filter name.');
            $scope.filter_error = "Invalid Filter Name";
            $scope.alertClass = 'filter_save_error';
            if (overwrite_flag == 1) {
                $scope.ranking.save_filter_called = 0;
            }
            return false;
        }
        
        $scope.ranking.save_filter_called = 1;
        var srch_txt = '';
        if ($("#globalSearchText").val().length > 0) {
            srch_txt = $("#globalSearchText").val();
        }
        var page = 'ranking';
        if ($rootScope.menuSelected == 'ranking') {
            page = 'ranking';
        } else if ($rootScope.menuSelected == 'my_networks' || $rootScope.menuSelected == 'mob_my_networks') {
            page = 'network';
        }

        var primary_tab = '';
        if ($rootScope.type == 'brands') {
            primary_tab = 'brand';
        } else if ($rootScope.type == 'advertisers') {
            primary_tab = 'adv';
        }

        var secondary_tab = 'NA';
        if (page == 'network') {
            if ($(".market_class").hasClass("active_alert")) {
                secondary_tab = 'market_share';
            } else if ($(".airing_class").hasClass("active_alert")) {
                secondary_tab = 'airings';
            }
        }

        var flag = $rootScope.active_flag;
        var frequency = '';
        if ($scope.schedule_email) {
            if ($scope.ranking.freq_filter_options.daily) {
                frequency = 'daily';
            }
            if ($scope.ranking.freq_filter_options.weekly) {
                frequency += (frequency ? ',' : '') + 'weekly';
            }
            if ($scope.ranking.freq_filter_options.monthly) {
                frequency += (frequency ? ',' : '') + 'monthly';
            }
            if ($scope.ranking.freq_filter_options.quarterly) {
                frequency += (frequency ? ',' : '') + 'quarterly';
            }
        }
        if (frequency == '') {
            frequency = 'none';
        }
        var flag_for_duplicate_check = 0;
        var programs = '';
        if(sessionStorage.my_network_called == 0) {
          programs = $scope.ranking.ranking_programs;
        }
        
        var formdata = { 'filter_name': filter_name, 'page': page, 'primary_tab': primary_tab, 'secondary_tab': secondary_tab, 'search_text': srch_txt, 'flag': flag, 'frequency': frequency, export_refine_apply_filter: export_refine_apply_filter, 'overwrite_flag': overwrite_flag, 'filter_id': filter_id, 'programs': programs, 'duplicate_found': duplicate_found };
        apiService.post('/save_user_filter', formdata)
            .then(function (response) {
            	var data = response.data;
                //$("#filter_save_error_" + tab).removeClass('filter_save_error');
                if (data.status) {
                    $scope.ranking.yes_no_filter = 0;
                    $scope.schedule_email = false;
                    $scope.ranking.freq_filter_options = { daily: false, weekly: false, monthly: false, quarterly: false };
                    $scope.ranking.save_filter_called = 0;
                    $scope.ranking.yes_save_filter_called = 0;
                    $('#filter_name_' + tab).val('');
                    //$("#filter_save_error_" + tab).show();
                    var display_text = 'saved';
                    if (data.status == 2) {
                        filter_array = [];
                        display_text = 'updated';
                    }
                    if (data.status == 3) {
                        //$("#filter_save_error_" + tab).addClass('filter_save_error');
                        //$("#filter_save_error_" + tab).html(data.message);
                        $scope.filter_error = data.message;
                        $scope.alertClass = 'filter_save_error';
                    } else {
                        filter_array = [];
                        //$("#filter_save_error_" + tab).addClass('filter_save_success');
                        /*$("#filter_save_error_" + tab).html('Filter ' + display_text + ' successfully!').fadeOut(2000, function () {
                            $("#modalSaveFilter_" + tab).modal('hide');
                        });*/
                        $scope.filter_error = 'Filter ' + display_text + ' successfully!';
                        $scope.alertClass = 'filter_save_success';
                        $timeout(function(){$uibModalInstance.dismiss(),200});
                    }
                } else {
                    if (data.isDuplicate) {
                        $scope.ranking.yes_no_filter = 1;
                        $scope.ranking.filter_id = data.filter_id
                        var html = 'A filter by this ' + data.isDuplicate + ' already exists, if you wish to overwrite the existing filter please click Yes or click No to cancel';
                        $scope.ranking.save_filter_called = 1;
                        //$("#filter_save_error_" + tab).addClass('filter_save_error');
                        //$("#filter_save_error_" + tab).show();
                        //$("#filter_save_error_" + tab).html(html);
                        $scope.filter_error = html;
                        $scope.alertClass = 'filter_save_error';
                        return false;
                    }
                }
            },function (error) {
                filter_array = [];
            });
    }
    // $scope.saveFilterModdal = function() {
        
    // }
    // $scope.saveFilterModdal();

});