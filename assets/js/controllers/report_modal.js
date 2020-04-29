angular.module('drmApp').controller('ReportsModalController', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.sharedList = 'My';
    $scope.selected_user = '';

    var user_id = sessionStorage.loggedInUserId;
    var sharded_by = sessionStorage.loggedInUserId;
    $rootScope.correctTotalPaginationTemplate =
    "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"prev-page\"></div></button> Page <input ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\"> of </abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"next-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-page\"></div></button></div></div><div class=\"ui-grid-pager-count-container\"></div></div>";

    $scope.show_reports_modal = function () {
        //ui grid code
        $scope.uigridReportsModal();
    }

    $scope.showSharedLists = function(item) {
        $scope.sharedList = item;
            //with ui grid code, displayes grid data according to rules set
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    // Call brand List ui Grid
    $scope.uigridReportsModal = function() {
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        // var c_dir = $scope.ranking.creative_type == 'short' ? '6':'1';
        var c_dir = '6';
        formData.primary_tab = $rootScope.my_list;
        formData.secondary_tab = 'NA';
        formData._search = true;
        formData.rows = '10';
        formData.page = 1;
        formData.sidx = "created";
        formData.sord = 'desc';

        vm.gridOptionsReports = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            paginationPageSize: 10,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
            enableCellEdit: false,
            enableCellEditOnFocus: true,
            onRegisterApi: (gridApi) => {
                gridApi.edit.on.afterCellEdit($scope, function(rowEntity, colDef, newValue, oldValue) {
                    console.log('edited row id:' + rowEntity.id + ', Column:' + colDef.name + ', newValue:' + newValue + ', oldValue:' + oldValue);
                    // Make an API here to update file name on server or to validate invalie/duplicate file name
                });
            },
        };

        vm.gridOptionsReports.columnDefs = [
            { name: 'file_name', pinnedLeft:true, displayName:'File Name', enableCellEdit: true},
            { name: 'filesize', pinnedLeft:true, displayName:'File Size'},
            // { name: 'download_link', pinnedLeft:true, displayName:'Download Link', cellTemplate: '<span ng-if="(row.entity.status == completed)"></span>' },
            { name: 'email_alert', pinnedLeft:true, displayName:'Email Alert', cellTemplate:
            '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" row.entity.disabled class="email_alert checkbox-custom" id=""email_alert_row.entity.id" name="email_alert" ng-click="updateEmailAlerts(row.entity.id)"  {{row.entity.checked}} /><label for="email_alert_row.entity.id" class="checkbox-custom-label row.entity.class"></label></li></ul></nav>'},

            { name: 'shared_report', displayName: 'Shared Report', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="share_filter checkbox-custom" id="share_list_row.entity.id" name="share_list" ng-click="updateShareListStatus(row.entity.id)"  row.entity.checked_shared_list row.entity.disabled_shared_list /><label for="share_filter_row.entity.id" class="checkbox-custom-label \'{{row.entity.disabled_class}}\'"></label></li></ul></nav>'},

            { name: 'copy_list', pinnedLeft:true, displayName: 'Copy To My List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="copy_list checkbox-custom" id="copy_list_row.entity.id" name="copy_list" ng-click="copySharedList(row.entity.id)"  {{row.entity.checked_copy_list}} {{row.entity.disable_copy_list}} /><label for="copy_filter_row.entity.id" class="checkbox-custom-label \'{{row.entity.disabled_copy_list_class}}\'"></label></li></ul></nav>'},
            { name: 'shared_valid_till', pinnedLeft:true, displayName:'Created' },
            { name: 'valid_till', pinnedLeft:true, displayName:'Valid Till' },
        ];
        apiService.post('/get_my_reports_data', formData, config)
        .then(function (response) {
            var data = response.data;
            $scope.PostDataResponse = formData;
            vm.gridOptionsReports.data = data.rows;
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.show_reports_modal(user_id, sharded_by);
});