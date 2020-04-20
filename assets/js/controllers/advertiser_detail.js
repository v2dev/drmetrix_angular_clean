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

        var c_dir = '6';
        formData.resp_type = formData.responseType;
        formData.form_type = "short_form";
        formData.tab = "brand";
        formData._search = false;
        formData.rows = '10';
        formData.page = '1';
        formData.sidx = 'spend_index';
        formData.sord = 'desc';
        formData.adv_id = $scope.adv_id;
        formData.adv_name = $scope.adv_name;

        vm.gridAdvertiser = {
            expandableRowTemplate: '<div ui-grid="row.entity.subBrandGridOptions" ui-grid-exporter ui-grid-expandable ui-grid-selection ui-grid-pagination class="grid" style="height:385px;"></div>',
            expandableRowHeight: 285,
            showTreeExpandNoChildren: true,
            enableGridMenu: true,
            enableSorting: true,
            enableExpandable: true,
            enableExpandableRowHeader: false,
            //Pagination
            paginationPageSizes: [20],
            paginationPageSize: 20,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.grid.registerRowsProcessor( $scope.singleFilter, 200 );

                gridApi.expandable.on.rowExpandedStateChanged($scope, function (row) {
                    formData.is_adv_page  = 0;
                    formData.brand_id = row.entity.id;
                    formData.tab = 'brand';
                    if (row.isExpanded) {
                        row.entity.subBrandGridOptions = {
                            columnDefs: [
                            { name: 'id', displayName:'id', width:'50' },
                            { name: 'creative_name', displayName: 'Creatives', cellTemplate: '<span ng-if="'+$rootScope.displayBtns+'==1" '+$rootScope.displayBtns+'><i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i></span><span><a href="" ng-click="grid.appScope.view_adv_tab(row.entity.creative_name,row.entity.adv_id,\''+c_dir+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\'creatives\',row.entity.id,row.entity.creative_name,\'ranking\',row.entity.need_help)" >{{COL_FIELD}}</a></span>', width: '280' },
                            { name: 'language', displayName: 'Type', width: '80' },
                            { name: 'classification', displayName: 'Classification', width: '160'},
                            { name: 'duration', displayName: 'Duration', width: '94'},

                            { name: 'airings', displayName: 'Airings', cellTemplate:'<a href=""><span ng-if="row.entity.airings!=\'\'" class="ranking_airings" ng-click="grid.appScope.viewAiringSpendGraph(row.entity.creative_name,row.entity.id,\'dow\',\''+formData.network_code+'\',\'all_day\',\'all_hour\',row.entity.networks,row.entity.airings,\''+formData.c+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\''+formData.responseType+'\',\''+formData.spanish+'\',\'creative\',row.entity.creative_name,\''+formData.brand_name+'\',\''+formData.brand_id+'\');">{{COL_FIELD}} </span><span ng-if="row.entity.airings==\'\'"> - </span></a>',  width: '110' },

                            { name: 'spend_index', displayName: 'Spend ($)', cellTemplate:'<a href="/#!/ranking"><span ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" ng-click="grid.appScope.viewAiringSpendGraph(row.entity.creative_name,row.entity.id,\'dow\',\''+formData.network_code+'\',\'all_day\',\'all_hour\',row.entity.networks,row.entity.spend_index,\''+formData.c+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\''+formData.responseType+'\',\''+formData.spanish+'\',\'creative\',row.entity.creative_name,\''+formData.brand_name+'\',\''+formData.brand_id+'\');">{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> 0 </span></a>', width: '110' },

                            { name: 'response_type', displayName: 'Response Type', enableSorting: false, cellTemplate:'<span class="response_img"><a href="#" ng-if=row.entity.response_url == 1 title="URL" ><img src="/drmetrix/assets/img/url-icon.svg" alt="URL" /></a><a href="#" ng-if=row.entity.response_sms == 1 title="SMS"><img src="/drmetrix/assets/img/sms-icon.svg" alt="SMS" /></a><a href="#" ng-if=row.entity.response_tfn == 1 title="Telephone"><img src="/drmetrix/assets/img/telephone-icon.svg" alt="Telephone" /></a><a href="#" ng-if=row.entity.response_mar == 1 title="Mobile"><img src="/drmetrix/assets/img/mobile-icon.svg" alt="Mobile" /></a>'
                            , width: '140'},

                            { name: 'national', displayName:'National', width: '95', cellTemplate:'<span ng-if="row.entity.national !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.national ==\'\'">0</span>' },
                            { name: 'local', displayName: 'DPI', cellTemplate: '<span ng-if="row.entity.local !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.local ==\'\'">0</span>', width: '90' },
                            { name: 'first_detection', displayName: 'First Aired',cellTemplate: '<div class="ngCellText">{{row.entity.first_detection}}</div>', width: '150'},
                            { name: 'last_aired', displayName: 'Last Aired', cellTemplate: '<div class="ngCellText">{{row.entity.last_aired}}</div>', width: '150'},
                            { name: 'video', displayName: '', width: '50', enableSorting: false, cellTemplate:'<i class="fa fa-play-circle-o fa-2x" ng-if="row.entity.thumbnail==\'\'" style="color:#cbcccc;"></i><a href="#basicModalCamp" ng-if="row.entity.thumbail!=\'\'"><i class="fa fa-play-circle-o fa-2x" onclick="playvideo({{row.entity.thumbnail}},'+formData.sd+','+formData.ed+',0,\'none\')"></i></a>'},                            
                        ],enableGridMenu: true,
                          enableSelectAll: true,
                          paginationPageSize: 10,
                          paginationTemplate: $rootScope.correctTotalPaginationTemplate,
                      };

                        apiService.post('/brand_creatives', formData, config)
                        .then(function (data) {
                            row.entity.subBrandGridOptions.data = data.data.rows;
                        }, function (response) {
                            // this function handlers error
                        });
                    }
                });
            },
        };

        vm.gridAdvertiser.columnDefs = [
            { name: 'rank', displayName: 'Rank', width: '50' },
            { name: 'status', displayName: 'Status', cellTemplate: '<i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i>' },

            { name: 'brand_name', pinnedLeft: true, displayName: 'Brand Name', cellTemplate: '<span>{{COL_FIELD}}</span>' },

            { name: 'creative_count', pinnedLeft: true, displayName: 'Creatives', cellTemplate: '<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subBrandGridOptions.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>' },

            { name: 'category_name', pinnedLeft: true, displayName: 'Category', cellTemplate:'<div ng-click="grid.appScope.fetchList(row.entity.id,\''+formData.type+'\', row.entity.category);"><a href="javascript://"><span ng-if="row.entity.category_name!=\'\'" class="tooltip-hover"><i class="fa fa-caret-down float-right"></i>{{COL_FIELD}} - </span></a><div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_{{row.entity.id}}" style="display:none;"></div></span><span ng-if="row.entity.category_name==\'\'"></div>' },

            { name: 'advertiser_name', pinnedLeft: true, displayName: 'Advertiser' },

            { name: 'airings', pinnedLeft: true, displayName: 'Airings', cellTemplate: '<a href="" ng-click="viewAiringGraph(row.entity.brand_name, row.entity.id,\'dow\', row.entity.network_code,\'all_day\',\'all_hour\', row.entity.networks, row.entity.airings,\''+formData.c+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\''+formData.responseType+'\',\''+formData.spanish+'\',\'brand\',\'\',\'\',\'\')" >{{COL_FIELD}}</a>' },

            { name: 'spend_index', pinnedLeft: true, displayName: 'Spend ($)', cellTemplate: '<a href="" ng-click="viewAiringSpendGraph(row.entity.brand_name,row.entity.id,\'dow\',\''+formData.network_code+'\',\'all_day\',\'all_hour\',row.entity.networks,row.entity.spend_index,\''+formData.c+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\''+formData.responseType+'\',\''+formData.spanish+'\',\'brand\',\'\',\'\',\'\')" >{{COL_FIELD}}</a>' },
            { name: 'national', pinnedLeft: true, displayName: 'National' },
            { name: 'local', pinnedLeft: true, displayName: 'DPI % ' },
            { name: 'asd', pinnedLeft: true, displayName: 'ASD' },
            { name: 'total_weeks', pinnedLeft: true, displayName: 'total_weeks' },
            { name: 'tracking', displayName: "Tracking", cellTemplate: '<a href="#"><i custom-attr="brand_\'{{row.entity.id}}\'" class="fa fa-eye-slash grey-eye" title="Track"></i></a>', width: '110' }
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