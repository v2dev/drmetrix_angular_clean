angular.module('drmApp').controller('FiltersModalController', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.sharedFilter = 'My';
    $scope.selected_user = '';
    
    $scope.show_user_filters = function () {
        //ui grid code
    }

    $scope.show_user_filters();
    
    $scope.showSharedFilters = function(item) {
        $scope.sharedFilter = item;
            //with ui grid code, displayes grid data according to rules set
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

   

    // Call filter ui Grid
    $scope.uigridFilterModal = function() {
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        // var c_dir = $scope.ranking.creative_type == 'short' ? '6':'1';
        var c_dir = '6';
        formData.tab = 'ranking';
        formData.primary_tab = 'brand';
        formData.secondary_tab = 'NA';
        formData._search = true;
        formData.rows = '10';
        formData.page = 1;
        formData.sidx = "created_date";
        formData.sord = 'desc';

        vm.gridOptions = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            //Pagination
            paginationPageSizes: [20],
            paginationPageSize: 20,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                // $scope.gridApi.grid.registerRowsProcessor( $scope.singleFilter, 200 );
            }
        };
        $scope.loading = true;
        vm.gridOptions.columnDefs = [
            // { name: 'row.entity.checked_schedule_email', pinnedLeft:true, displayName:'ID' },
            // { name: 'row.entity.disabled_schedule_email', pinnedLeft:true, displayName:'ID' },
            { name: 'full_name', pinnedLeft:true, displayName:'User'},
            { name: 'name', pinnedLeft:true, displayName:'Filter Name'},
            { name: 'primary_tab', pinnedLeft:true, displayName:'Tab' },
            { name: 'created_date', pinnedLeft:true, displayName:'Created On' },

            { name: 'copy_filter', pinnedLeft:true, displayName: 'Copy To My Filters', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="copy_filter checkbox-custom" id="copy_filter_{{row.entity.id}}" name="copy_filter" ng-click="copySharedFilter({{row.entity.id}})"  {{row.entity.checked_copy_filter}} {{row.entity.disable_copy_filter}} /><label for="copy_filter_{{row.entity.id}}" class="checkbox-custom-label {{row.entity.disabled_copy_filter_class}}"></label></li></ul></nav>'},

            { name: 'shared_filter', displayName: 'Share Filter', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="share_filter checkbox-custom" id="share_filter_\'{{row.entity.id}}\'" name="share_filter" ng-click="updateShareFilterStatus(\'{{row.entity.id}}\')"  \'{{row.entity.checked_shared_filter}}\' \'{{row.entity.disabled_shared_filter}}\' /><label for="share_filter_\'{{row.entity.id}}\'" class="checkbox-custom-label \'{{row.entity.disabled_class}}\'"></label></li></ul></nav>'},

            { name: 'query_string', pinnedLeft:true, displayName:'Detail', cellTemplate:'<span title="{{COL_FIELD}}">{{row.entity.query_string == \'\' ? \'-\' : row.entity.query_string | limitTo: 60}}</span>' },

            {name: 'schedule_email', pinnedLeft:true, displayName:'Schedule Email',  cellTemplate:'<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="checkbox-custom" id="schedule_email_\'{{row.entity.id}}\'" ng-click="updateScheduleEmailStatus(\'{{row.entity.id}}\',\'{{row.entity.email_schedulable_direct}}\')" data-frequency="\'{{row.entity.email_schedulable_direct}}\'" \'{{row.entity.checked_schedule_email}}\' \'{{row.entity.disabled_schedule_email}}\' /><label for="schedule_email_\'{{row.entity.id}}\'" class="checkbox-custom-label \'{{row.entity.disabled_schedule_email_class}}\'"></label></li></ul></nav>'},
            { name: 'apply', pinnedLeft:true, displayName:'Apply', cellTemplate: '<a href="javascript:void(0)" ng-click="apply_user_filter(\'{{row.entity.id}}\');" id="apply_filter_{{row.entity.id}}">Apply</a>' },
        ];
        apiService.post('/get_user_filter_list', formData, config)
        .then(function (data) {
            $scope.loading = false;
            $scope.PostDataResponse = formData;
            vm.gridOptions.data = data.data.rows;
        }, function (response) {
            // this function handlers error 
        });
    }

    $scope.uigridFilterModal();
});
