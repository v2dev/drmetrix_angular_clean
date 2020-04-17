angular.module("drmApp").controller("AdvertiserDetailController", function ($scope, $http, $interval, uiGridTreeViewConstants, $state, $rootScope, apiService, $uibModal) {
    if (!apiService.isUserLogged($scope)) {
        // $state.go('home');
        // return;
    }

    $scope.uigridAdvertiser = function () {
        debugger;
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }

        // var c_dir = $scope.ranking.creative_type == 'short' ? '6':'1';
        var c_dir = '6';
        formData.resp_type = formData.responseType;
        formData.form_type = "short_form";
        formData.tab = "brand";
        formData.primary_tab = "";
        formData.secondary_tab = 'NA';
        formData._search = false;
        formData.rows = '10';
        formData.page = '1';
        formData.sidx = 'spend_index';
        formData.sord = 'desc';
        formData.adv_id = $scope.adv_id;
        formData.adv_name = $scope.adv_name;
        formData.cat_id = 'all';
        formData.breaktype = 'A';
        formData.network_code = 'all_networks';
        formData.hour = 'all_hour';
        formData.day = 'all_day';
        formData.dayparts = 'all_dayparts';
        formData.creative_duration = 'all_short_duration';

        vm.gridAdvertiser = {
            // expandableRowTemplate: '/drmetrix_angular_clean/templates/expandableAiringRowtmpt.html',
            expandableRowHeight: 285,
            showTreeExpandNoChildren: true,
            enableGridMenu: true,
            enableSorting: true,
            enableExpandableRowHeader: false,
            //Pagination
            paginationPageSizes: [20],
            paginationPageSize: 20,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
        };

        vm.gridAdvertiser.columnDefs = [
            { name: 'rank', displayName: 'Rank', width: '50' },
            { name: 'status', displayName: 'Status', cellTemplate: '<i class="fa fa-circle" id="active_btn"></i>' },

            { name: 'brand_name', pinnedLeft: true, displayName: 'Brand Name', cellTemplate: '<span>Otezla</span>' },

            { name: 'creative_count', pinnedLeft: true, displayName: 'Creatives' },

            { name: 'category_name', pinnedLeft: true, displayName: 'Category' },

            { name: 'advertiser_name', pinnedLeft: true, displayName: 'Advertiser', cellTemplate: '<span>Celgene</span>' },

            { name: 'airings', pinnedLeft: true, displayName: 'Airings' },

            { name: 'spend_index', pinnedLeft: true, displayName: 'Spend ($)' },
            { name: 'national', pinnedLeft: true, displayName: 'National' },
            { name: 'local', pinnedLeft: true, displayName: 'DPI % ' },
            { name: 'asd', pinnedLeft: true, displayName: 'ASD' },
            { name: 'total_weeks', pinnedLeft: true, displayName: 'total_weeks' },
        ];

        apiService.post('/get_advpage_brands', formData, config)
            .then(function (data) {
                vm.gridAdvertiser.data = data.data.rows;
            }, function (response) {
                // this function handlers error
            });
    }

    $scope.uigridAdvertiser($scope.brand_id, $scope.brand_name, $scope.active_tab, $scope.all_network);
});