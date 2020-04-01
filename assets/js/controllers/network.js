angular.module('drmApp').controller('NetworkController', function ($scope, $timeout, $state, $stateParams, $filter, $interval, uiGridConstants, $rootScope, apiService, $uibModal, $cookies) {
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }
    $scope.letterLists = ['all', '0-9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
    $scope.selectedLetter = 'all';
    $rootScope.headerDisplay = 0;
    var today = new Date();
    var prev_date = new Date();
    prev_date.setMonth(today.getMonth() - 6);
    var nCtrl = this;

    $scope.loadNetworks = function() {
        $scope.showNetworkModal = 1;
        $scope.headerDisplay = 0;
        apiService.post('/get_all_active_networks')
        .then(function (response) {
            var data = response.data;
            $scope.networkTabList = data.result;
        }), (function (response) {
        });
    }
    $scope.loadNetworks();

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

    $scope.hasTabLetterDisable = function (letter) {
        var temp = [];
        if(typeof($scope.networkTabList) != 'undefined'){
            if (letter == 'all')
                return false;
                if (letter == '0-9') {
                    var lists = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                    for (var i in lists) {
                        temp = $scope.networkTabList.filter(function (item) {
                            if (item.network_alias.toLowerCase().indexOf(lists[i]) == 0)
                                return item;
                        });
                        if (temp.length > 0)
                            return false;
                    }
                } else {
                    temp = $scope.networkTabList.filter(function (item) {
                        if (item.network_alias.toLowerCase().indexOf(letter.toLowerCase()) == 0)
                            return item;
                    });
                }
        }
        return temp.length > 0 ? false : true;
    }

    $scope.networkSelectCount = function () {
        if($scope.networkTabList && $scope.networkTabList.length> 0){
            var tempData = $scope.networkTabList.filter(function(item) {
                return item.isSelected == true;
            })
            $scope.valueofcount = tempData.length;
            if (tempData.length > 1 && tempData.length < 9) {
                $scope.networkApply = 0;
            }
            else {
                $scope.networkApply = 1;
            }
            return tempData.length;
        } else {
            return 0;
        }
    }

    $scope.clearNetworkList = function () {
        for (var i in $scope.networkTabList) {
            $scope.networkTabList[i].isSelected = false;
        }
    }

    $scope.changeLatter = function (letter) {
        $scope.selectedLetter = letter;
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

    $scope.networkFilterApply = function () {
        $scope.showNetworkModal = 0;
        $scope.headerDisplay = 1;
        var checked_count = 0;
        var my_network_called = $rootScope.my_network_called = sessionStorage.my_network_called = 1;
        sessionStorage.selected_arr_networks = [];
        sessionStorage.selected_networks_id = [];
        var selected_network_code = []; // network code array
        var selected_network_id = []; // network id array
        angular.forEach($scope.networkTabList, function(value, key) {
            if(value.isSelected) {
                selected_network_code.push(value.network_code);
                selected_network_id.push(value.network_id);
            }
          });
       
        $scope.selected_network_ids = selected_network_id;
        $cookies.put("selected_network_code",selected_network_code);
        $cookies.put("selected_network_id",selected_network_id);

        $rootScope.selected_network_code = selected_network_code;
        $rootScope.selected_network_id = selected_network_id;
      
        if ($scope.type) {
            $scope.jqgridBrandNetworkAirings();
        } else {
            $scope.jqgridAdvNetworkAirings();
        }

        $rootScope.network_category_section = 1;
    }

    $scope.jqgridBrandNetworkAirings = function() {
        console.log('brand grid called');

        /* Network brand grid called */
        $scope.uigridNetworkAiringBrand();
    }

    $rootScope.uigridNetworkAiringBrand = function() {
        var formData = $rootScope.formdata;
        formData.unchecked_category = '';
        formData.length_unchecked = 0;
        var network_code = $rootScope.selected_network_code;
        formData.network_code = network_code.toString();
        var network_ids = $rootScope.selected_network_id;
        formData.network_id = network_ids.toString();
        formData.page = 1;
        formData.networkTab = 'spend_index';
        formData.lifetime_flag = false;
        formData.all_ytd_flag = false;
        formData.breaktype = 'A';
        formData.sidx = 'Total Dollars_spend_index';
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var c_dir = '6';
        var correctTotalPaginationTemplate =
        "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"prev-page\"></div></button> Page <input ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\"> of </abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"next-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-page\"></div></button></div></div><div class=\"ui-grid-pager-count-container\"></div></div>";    
        vm.gridNetworkAiringBrand = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            //Pagination
            paginationPageSizes: [20],
            paginationPageSize: 20,
            paginationTemplate: correctTotalPaginationTemplate,
        };

        apiService.post('/display_airings_brands_with_networks', formData, config)
        .then(function (response) {
            var data = response.data;
            $scope.PostDataResponse = formData;
            vm.gridNetworkAiringBrand.data = data.rows;
            // var checkedPrograms = [];
            // if (data.length != 0) {
            //     $rootScope.checkedRankingPrograms   =  checkedPrograms;
            //     $rootScope.ranking_programs         =  data.rows;
            // }
            vm.gridNetworkAiringBrand.columnDefs = [
                { name: 'id', pinnedLeft:true, width: '60' },

            ];
        }, function (response) {
            // this function handlers error
            console.log("rejected with", response);
        });
    }

});

