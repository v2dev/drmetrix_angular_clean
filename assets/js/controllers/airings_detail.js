angular.module("drmApp").controller("airingsDetailController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal){
    if (!apiService.isUserLogged($scope)) {
        // $state.go('home');
        // return;
    }
    $rootScope.correctTotalPaginationTemplate =
    "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"prev-page\"></div></button> Page <input ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\"> of </abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"next-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-page\"></div></button></div></div><div class=\"ui-grid-pager-count-container\"></div></div>";
$scope.uigridAiringSpend = function(){
    var formData = $rootScope.formdata;
    var vm = this;
    var config = {
        headers : {
            'Content-Type': 'application/json; charset=utf-8'
        }
    }

    // var c_dir = $scope.ranking.creative_type == 'short' ? '6':'1';
    var c_dir = '6';
    formData.tab = "brand";
    formData.primary_tab = "";
    formData.secondary_tab = 'NA';
    formData._search = true;
    formData.rows = '10';
    formData.page = '1';
    formData.sidx = $scope.sidx;
    formData.sord = 'desc';
    formData.brand_id = $scope.brand_id;
    formData.brand_name = $scope.brand_name;
    formData.cat_id = 'all';
    formData.breaktype = 'A';
    formData.network_code = 'all_networks';
    formData.hour = 'all_hour';
    formData.day = 'all_day';
    formData.dayparts = 'all_dayparts';
    formData.creative_duration = 'all_short_duration';

    vm.gridAiringSpend = {
        expandableRowTemplate: '/drmetrix_angular_clean/templates/expandableAiringRowtmpt.html',
        expandableRowHeight: 285,
        showTreeExpandNoChildren: true,
        enableGridMenu: true,
        enableSorting: true,
        enableExpandableRowHeader: false,
        //Pagination
        paginationPageSizes: [20],
        paginationPageSize: 20,
        paginationTemplate: $rootScope.correctTotalPaginationTemplate,
            onRegisterApi: function (gridApi) {
                gridApi.expandable.on.rowExpandedStateChanged($scope, function (row) {
                    formData.tab = 'brand';
                    formData.network_code = 'all_networks';
                    formData.network_id = '29';
                    formData.network_code = null;
                    formData.programs_ids = '';
                    formData.dpi = '';
                    formData.startDate = '1';
                    formData.is_adv_page  = 0;
                    if (row.isExpanded) {
                        row.entity.subAiringGridOptions = {
                            columnDefs: [
                            // { name: 'id', displayName:'id', width:'50' },
                            { name: 'creative_name', displayName:'Creatives' },
                            { name: 'language', displayName:'Type' },
                            { name: 'classification', displayName:'Classification' },
                            { name: 'duration', displayName:'Length' },
                            { name: 'program_count', displayName:'Program', cellTemplate: '<a href="" ng-click="subAiringGridOptions.appScope.vm.getProgramsByNetwork();">{{COL_FIELD}}</a>' },
                            { name: 'airings', displayName:'Total Airings', cellTemplate:'<a href="#" ng-click="grid.appScope.showAiringSpendGraph(\'airings\',\''+$scope.brand_id+'\', {{row.entity.id}}, {{row.entity.creative_name}}, {{row.entity.airings}},{{row.entity.dpi}},\''+formData.network_id+'\')">{{COL_FIELD}}</a>' },
                            { name: 'total_spend', displayName:'Total Spend ($)',cellTemplate: '<a href="#" ng-click="grid.appScope.showAiringSpendGraph(\'spend\',\''+$scope.brand_id+'\',row.entity.id, row.entity.creative_name, row.entity.total_spend,row.entity.dpi, \''+formData.network_id+'\')">{{COL_FIELD}}</a>' },
                            { name: 'response_type', displayName:'Response Type', cellTemplate:'<span class="response_img"><a href="#" ng-if="(row.entity.response_url==1)" title="URL" ><img src="/drmetrix_angular_clean/assets/images/url-icon.svg" alt="URL" /></a><a href="#" ng-if="(row.entity.response_sms == 1)" title="SMS"><img src="/drmetrix_angular_clean/assets/images/sms-icon.svg" alt="SMS" /></a><a href="#" ng-if="(row.entity.response_tfn == 1)" title="Telephone"><img src="/drmetrix_angular_clean/assets/images/telephone-icon.svg" alt="Telephone" /></a><a href="#" ng-if="(row.entity.response_mar == 1)" title="Mobile"><img src="/drmetrix_angular_clean/assets/images/mobile-icon.svg" alt="Mobile" /></a></span>' },
                            { name: 'national_count', displayName:'National Airings' },
                            { name: 'national', displayName:'National %' },
                            { name: 'national_spend', displayName:'National Spend ($)' },
                            { name: 'local_count', displayName:'DPI Airings' },
                            { name: 'local', displayName:'DPI %' },
                            { name: 'local_spend', displayName:'DPI Spend ($)' },
                            { name: 'first_aired', displayName:'First Aired' },
                            { name: 'last_aired', displayName:'Last Aired' },
                        ],enableGridMenu: true,
                          enableSelectAll: true,
                          paginationPageSize: 10,
                          paginationTemplate: $rootScope.correctTotalPaginationTemplate,
                          exporterCsvFilename: 'mySubFile.csv',
                          exporterCsvLinkElement: angular.element(document.querySelectorAll(".custom-csv-link-location")),
                          exporterExcelFilename: 'mySubFile.xlsx',
                          exporterExcelSheetName: 'Sheet11'
                      };

                        apiService.post('/creatives_networks', formData, config)
                        .then(function (data) {
                            row.entity.subAiringGridOptions.data = data.data.rows;
                        }, function (response) {
                            // this function handlers error
                        });
                    }
                });
            },
    };

    vm.gridAiringSpend.columnDefs = [
        // { name: 'id', displayName:'id', width:'50' },
        { name: 'network_code', displayName:'Network', cellTemplate:'<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subAiringGridOptions.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>' },

        { name: 'dpi', pinnedLeft:true, displayName:'ID', cellTemplate:'<span ng-if="row.entity.dpi!=\'\'" class="dpi-symbol" style="width:30px;"><img src="assets/images/dpi_symbol.png"/></span><span ng-if="row.entity.dpi==\'\'"> - </span>'},

        { name: 'creatives', pinnedLeft:true, displayName:'Creatives' },

        { name: 'program_count', pinnedLeft:true, displayName:'Program', cellTemplate: '<a href="" ng-click="grid.appScope.getProgramsByNetwork(row.entity.id,\'brand\',\''+$scope.brand_id+'\',row.entity.network_alias,row.entity.program_count);">{{COL_FIELD}}</a>' },

        { name: 'airings', pinnedLeft:true, displayName:'Total Airings', cellTemplate:'<a href="#" ng-click="grid.appScope.showAiringSpendGraph(\'airings\',row.entity.id, \'\', row.entity.network_alias, row.entity.airings, row.entity.dpi, row.entity.id)">{{COL_FIELD}}</a>' },

        { name: 'total_spend', pinnedLeft:true, displayName:'Total Spend', cellTemplate: '<a href="#" ng-click="grid.appScope.showAiringSpendGraph(\'spend\',row.entity.id, \'\', \'row.entity.network_alias\', \'row.entity.total_spend\',row.entity.dpi,row.entity.id)">{{COL_FIELD}}</a>' },

        { name: 'national_count', pinnedLeft:true, displayName:'National Airings' },
        { name: 'national', pinnedLeft:true, displayName:'National' },
        { name: 'nat_spend', pinnedLeft:true, displayName:'National Spend' },
        { name: 'local_count', pinnedLeft:true, displayName:'DPI Airings' },
        { name: 'local_spend', pinnedLeft:true, displayName:'DPI Spend ($)' },
        { name: 'local', pinnedLeft:true, displayName:'DPI % ' },
        { name: 'duration', pinnedLeft:true, displayName:'ASD' },
    ];
    apiService.post('/brand_networks', formData, config)
    .then(function (data) {
        $scope.PostDataResponse = formData;
        vm.gridAiringSpend.data = data.data.rows;
    }, function (response) {
        // this function handlers error
    });
}

$scope.uigridAiringSpend($scope.brand_id, $scope.brand_name, $scope.active_tab, $scope.all_network);
    
$scope.getProgramsByNetwork = function(network_id,brand,brand_id,network_alias,program_count) {
    $rootScope.network_id = network_id;
    $scope.brand = brand;
    $rootScope.brand_id = brand_id;
    $scope.network_alias = network_alias;
    $scope.program_count = program_count;
    $scope.openProgramModal();
}

$scope.openProgramModal = function() {
    $scope.modalInstance =  $uibModal.open({
        templateUrl: "./templates/modals/ProgramDialog.html",
        controller: "ProgramModalCtrl",
        size: 'lg modal-dialog-centered',
      });
}

});

angular.module('drmApp').controller('ProgramModalCtrl', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    // Call brand List ui Grid
    $scope.uigridProgramModdal = function() {
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var c_dir = '6';
        formData.network_id = $rootScope.network_id;
        formData.id = $rootScope.brand_id;
        formData.area = 'brand';

        vm.gridOptionsProgram = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            paginationPageSize: 10,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
        };

        vm.gridOptionsProgram.columnDefs = [
            { name: 'program', pinnedLeft:true, displayName:'Program'},
            { name: 'start_time', pinnedLeft:true, displayName:'Star Time (EST)'},
            { name: 'total_airings', pinnedLeft:true, displayName:'Total Airings'},
            { name: 'total_spend', pinnedLeft:true, displayName:'Total Spend'},
            { name: 'national_airings', pinnedLeft:true, displayName:'National Airings'},
            { name: 'national_percent', pinnedLeft:true, displayName:'National %'},
            { name: 'national_spend', pinnedLeft:true, displayName:'National Spend($)'},
            { name: 'local_airings', pinnedLeft:true, displayName:'DPI Airings'},
            { name: 'local_percent', pinnedLeft:true, displayName:'DPI %'},
            { name: 'local_spend', pinnedLeft:true, displayName:'DPI Spend($)'},
        ];
        apiService.post('/get_programs_by_network', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptionsProgram.data = data.data.rows;
        }, function (response) {
            // this function handlers error
            console.log("rejected with", response);
        });
    }

    $scope.show_Program_list = function () {
        //ui grid code
        $scope.uigridProgramModdal();
    }
    $scope.show_Program_list();
});