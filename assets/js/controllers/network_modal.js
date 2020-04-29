"use strict";
angular.module('drmApp').controller('NetworkModalController', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.selectedLetter = 'all';
    $scope.selectedNetwork = '';
    $scope.letterLists    = ['all', '0-9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
    var params = {"sd":"2020-02-24","ed":"2020-03-01","startDate":1,"val":1,"c":1,"type":0,"cat":"all","flag":2,"spanish":"0,1","responseType":"(response_url = 1 or response_mar = 1 or response_sms = 1 or response_tfn = 1 )","unchecked_category":"","length_unchecked":0,"creative_duration":"10,15,20,30,45,60,75,90,105,120,180,240,300","new_filter_opt":"none","lifetime_flag":false,"all_ytd_flag":false,"refine_filter_opt":"","refine_filter_opt_text":"","refine_apply_filter":0,"applied_ids":"","primary_tab":"", };
   
    var today = new Date();
    var prev_date = new Date();
    prev_date.setMonth(today.getMonth() - 6);

    $scope.dataOfAllNetworks = function (call_from) {
        $scope.searchNet = '';
        var ndata;
        if (call_from == 'AllNetwroksFunction') {
            ndata = JSON.parse(localStorage.all_networks_data);
        } else {
            ndata = JSON.parse(localStorage.active_networks_data);
        }

        $scope.networkLists = ndata.result;
        var netCount = ndata.result.length;
       
        $scope.netCount = netCount;
        $scope.network_name = 'All (' + netCount + ')';
    }

    $scope.networkSearchBy = function (data, selectedLetter) {
        return function (item) {
            if (data && data != "") {
                return item.network_alias.toLowerCase().indexOf(data.toLowerCase()) > -1;
            } else if (selectedLetter != 'all') {
                return item.network_alias.toLowerCase().indexOf(selectedLetter.toLowerCase()) == 0;
            } else {
                return item;
            }
        };
    }

    $scope.checkLiveDateStatus = function (live_date) {
        var l_date = new Date(live_date);
        if (l_date.getTime() >= prev_date.getTime() && l_date.getTime() <= today.getTime()) {
            return true;
        } else {
            return false;
        }
    }

    $scope.getLiveDate = function (live_date) {
        var year = live_date.substring(0, 4);
        var month = live_date.substring(5, 7);
        var date = live_date.substring(8, 10);
        return month + "/" + date + "/" + year;
    }

    $scope.getNetworksWithAllFilters = function () {
        var call_api = 1;
        if (localStorage.activeNetwroksParams != undefined && localStorage.active_networks_data != undefined) {
            if (JSON.stringify(params) == localStorage.activeNetwroksParams) {
                $scope.dataOfAllNetworks('ActiveNetwroksFunction');
                call_api = 0;
            } else {
                localStorage.activeNetwroksParams = JSON.stringify(params);
            }
        } else {
            localStorage.activeNetwroksParams = JSON.stringify(params);
        }

        if(call_api) {
            $scope.networkNotLoaded = 1;
            apiService.post('/get_networks_with_all_filters', params)
            .then(function (response) {
                $scope.networkNotLoaded = 0;
                var data = response.data;
                localStorage.setItem('active_networks_data', JSON.stringify(data));
                $scope.dataOfAllNetworks('ActiveNetwroksFunction');
            }),(function (data, status, headers, config) {
            });
        }
    }

    $scope.getAllActiveInactiveNetworks = function () {
        var new_filter_opt = 'none';
        if (localStorage.all_networks_data != undefined) {
            $scope.dataOfAllNetworks('AllNetwroksFunction', new_filter_opt);
        } else {
            $scope.networkNotLoaded = 1;
            apiService.post('/get_all_active_inactive_networks', {})
            .then(function (response) {
                var ndata = response.data;
                $scope.networkNotLoaded = 0;
                localStorage.setItem('all_networks_data', JSON.stringify(ndata));
                $scope.dataOfAllNetworks('AllNetwroksFunction', new_filter_opt);
            }),(function () {
                console.log("error in get all networks");
            });

        }
    }
    
    $scope.hasLetterDisable = function (letter) {
        var temp = [];
        if(typeof($scope.networkLists) != 'undefined'){
            if (letter == 'all')
                return false;
                if (letter == '0-9') {
                    var lists = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                    for (var i in lists) {
                        temp = $scope.networkLists.filter(function (item) {
                            if (item.network_alias.toLowerCase().indexOf(lists[i]) == 0)
                                return item;
                        });
                        if (temp.length > 0)
                            return false;
                    }
                } else {
                    temp = $scope.networkLists.filter(function (item) {
                        if (item.network_alias.toLowerCase().indexOf(letter.toLowerCase()) == 0)
                            return item;
                    });
                }
        }
        return temp.length > 0 ? false : true;
    }

    $scope.changeLatter = function (letter) {
        $scope.selectedLetter = letter;
    }

    $scope.getNetworksWithAllFilters();

    $scope.getSelectedNetworkName = function() {
        var result;
        angular.forEach($scope.networkLists, function (item) {
            if (item.network_id == $scope.selectedNetwork) {
                result =  item.network_alias;
            }
        });
        return result;
       
    }

    $scope.applyNetworkModal = function() {
        $rootScope.networkDisplayName = $scope.getSelectedNetworkName();
        $rootScope.$broadcast("CallParentMethod", {'network_id' : $scope.selectedNetwork, 'network_alias' : $rootScope.networkDisplayName });
        $uibModalInstance.dismiss();
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});