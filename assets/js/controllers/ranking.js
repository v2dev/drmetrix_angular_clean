angular.module("drmApp").controller("RankingController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal){
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }

    $scope.initialisation = function() {
        $rootScope.headerDisplay = 1;
        $rootScope.complete_name = localStorage.complete_name;
       
    }
    $scope.initialisation() ;

  
    $scope.call_filter_list = function(menu) {
        var modalInstance = $uibModal.open({
            templateUrl: './templates/ranking-modals.html',
            controller: "FilterCtrl",
            backdrop:'static',
            size :'lg',
            keyboard:false,
        });
    }
    
    feather.replace();

    /* Ranking Grid Start */

    // var formdata = {'sd': data['sd'], 'ed': data['ed'], 'startDate': $scope.ranking.selectDate, 'val': data['selectDateDropDown'], 'c': $scope.ranking.selectClassfication, 'type': data['type'], 'cat': data['cat_id'], 'flag': active_flag, spanish: $scope.ranking.selectLang, responseType: $scope.ranking.returnText,'unchecked_category': data['unchecked_cat'], 'length_unchecked': unchecked_len, 'creative_duration': duration, 'new_filter_opt': new_filter_opt, 'lifetime_flag': lifetime_flag, 'all_ytd_flag': all_ytd_flag, 'refine_filter_opt': refine_filter_opt, 'refine_filter_opt_text': refine_filter_opt_text, 'refine_apply_filter': refine_apply_filter, 'programs_ids': airings_data['all_programs_ids'],'applied_ids' : $scope.ranking.applied_list_ids , 'primary_tab' : $scope.ranking.applied_list_type}; 
    var formdata =  {"sd":"2020-02-24","ed":"2020-03-01","startDate":1,"val":1,"c":1,"type":1,"cat":"all","flag":2,"spanish":"0,1","responseType":"(response_url = 1 or response_mar = 1 or response_sms = 1 or response_tfn = 1 )","unchecked_category":"","length_unchecked":0,"creative_duration":"10,15,20,30,45,60,75,90,105,120,180,240,300","new_filter_opt":"none","lifetime_flag":false,"all_ytd_flag":false,"refine_filter_opt":"","refine_filter_opt_text":"","refine_apply_filter":0,"applied_ids":"","primary_tab":""}

    $scope.uigridDataBrand = function(formData) {
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        // var c_dir = $scope.ranking.creative_type == 'short' ? '6':'1';
        var c_dir = '6';
        var correctTotalPaginationTemplate =
    "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-triangle\"><div class=\"first-bar\"></div></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-triangle prev-triangle\"></div></button> <input type=\"number\" ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\">/</abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"last-triangle next-triangle\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-triangle\"><div class=\"last-bar\"></div></div></button></div></div><div class=\"ui-grid-pager-count-container\"><div class=\"ui-grid-pager-count\"><span ng-show=\"grid.options.totalItems > 0\">{{(((grid.options.paginationCurrentPage-1)*grid.options.paginationPageSize)+1)}} <abbr ui-grid-one-bind-title=\"paginationThrough\">-</abbr> {{(grid.options.paginationCurrentPage*grid.options.paginationPageSize>grid.options.totalItems?grid.options.totalItems:grid.options.paginationCurrentPage*grid.options.paginationPageSize)}} {{paginationOf}} {{grid.options.totalItems}} {{totalItemsLabel}}</span></div></div></div>";
        vm.gridOptions = {
            expandableRowTemplate: '/drmetrix/templates/expandableRowTemplate.html',
            expandableRowHeight: 385,
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            showTreeExpandNoChildren: true,
            exporterCsvFilename: 'myFile.csv',
            exporterCsvLinkElement: angular.element(document.querySelectorAll(".custom-csv-link-location")),
            exporterExcelFilename: 'myFile.xlsx',
            exporterExcelSheetName: 'Sheet1',
            enableExpandableRowHeader: false,
            //Pagination
            paginationPageSizes: [20],
            paginationPageSize: 20,
            paginationTemplate: correctTotalPaginationTemplate,
            exporterExcelCustomFormatters: function ( grid, workbook, docDefinition ) {
              var stylesheet = workbook.getStyleSheet();
              var stdStyle = stylesheet.createFontStyle({
                size: 11, fontName: 'Calibri'
              });
              var boldStyle = stylesheet.createFontStyle({
                size: 9, fontName: 'Calibri', bold: true
              });
              var aFormatDefn = {
                "font": boldStyle.id,
                "alignment": { "wrapText": true }
              };
              var formatter = stylesheet.createFormat(aFormatDefn);
              // save the formatter
              $scope.formatters['bold'] = formatter;
              var dateFormatter = stylesheet.createSimpleFormatter('date');
              $scope.formatters['date'] = dateFormatter;

              aFormatDefn = {
                "font": stdStyle.id,
                "fill": { "type": "pattern", "patternType": "solid", "fgColor": "FFFFC7CE" },
                "alignment": { "wrapText": true }
              };
              var singleDefn = {
                font: stdStyle.id,
                format: '#,##0.0'
              };
              formatter = stylesheet.createFormat(aFormatDefn);
              $scope.formatters['red'] = formatter;
              // save the formatter
              Object.assign(docDefinition.styles , $scope.formatters);
              return docDefinition;
            },
            exporterExcelHeader: function (grid, workbook, sheet, docDefinition) {
                // this can be defined outside this method
                var stylesheet = workbook.getStyleSheet();
                var aFormatDefn = {
                  "font": { "size": 11, "fontName": "Calibri", "bold": true },
                  "alignment": { "wrapText": true }
                };
                var formatterId = stylesheet.createFormat(aFormatDefn);
                // excel cells start with A1 which is upper left corner
                sheet.mergeCells('B1', 'C1', 'D1', 'E1', 'F1');
                var cols = [];
                // push empty data
                cols.push({ value: '' });
                // push data in B1 cell with metadata formatter
                cols.push({ value: 'DRM Brand Excel Export - ', field: 'brand_name', metadata: {style: formatterId.id} });
                sheet.data.push(cols);
            },
            onRegisterApi: function (gridApi) {
                gridApi.expandable.on.rowExpandedStateChanged($scope, function (row) {
                    formData.network_code = '';
                    formData.programs_ids = '';
                    formData.is_adv_page  = 0;
                    formData.brand_id = row.entity.brand_id;
                    formData.tab = 'brand';
                    if (row.isExpanded) {
                        row.entity.subGridOptions = {
                            columnDefs: [
                            // { name: 'id', displayName:'id', width:'50' },
                            { name: 'creative_name', displayName: 'Creatives', cellTemplate: '<span ng-if="'+$rootScope.displayBtns+'==1" '+$rootScope.displayBtns+'><i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i></span><span><a href="#" ng-click="view_adv_tab({{row.entity.creative_name}},{{row.entity.adv_id}},'+c_dir+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+',\'creatives\',{{row.entity.id}},{{COL_FIELD}},\'ranking\',{{row.entity.need_help}})" >{{COL_FIELD}}</a></span>', width: '280' },
                            { name: 'language', displayName: 'Type', width: '80' },
                            { name: 'classification', displayName: 'Classification', width: '160'},
                            { name: 'duration', displayName: 'Duration', width: '94'},
                            { name: 'airings', displayName: 'Airings', cellTemplate:'<a href="javascript:void();"><span ng-if="row.entity.airings!=\'\'" class="ranking_airings" ng-click="viewAiringGraph({{row.entity.creative_name}},{{row.entity.id}},\'dow\','+formData.network_code+',\'all_day\',\'all_hour\',{{row.entity.networks}},{{row.entity.airings}},'+formData.c+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+','+formData.responseType+','+formData.spanish+',\'creative\',{{row.entity.creative_name}},'+formData.brand_name+','+formData.brand_id+');">{{COL_FIELD}} </span><span ng-if="row.entity.airings==\'\'"> - </span></a>',  width: '110' },

                            { name: 'spend_index', displayName: 'Spend ($)', cellTemplate:'<a href="#"><span ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" ng-click="viewAiringSpendGraph({{row.entity.creative_name}},{{row.entity.id}},\'dow\','+formData.network_code+',\'all_day\',\'all_hour\',{{row.entity.networks}},{{row.entity.spend_index}},'+formData.c+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+','+formData.responseType+','+formData.spanish+',\'creative\',{{row.entity.creative_name}},'+formData.brand_name+','+formData.brand_id+');">{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> 0 </span></a>', width: '110' },

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
                          paginationTemplate: correctTotalPaginationTemplate,
                          exporterCsvFilename: 'mySubFile.csv',
                          exporterCsvLinkElement: angular.element(document.querySelectorAll(".custom-csv-link-location")),
                          exporterExcelFilename: 'mySubFile.xlsx',
                          exporterExcelSheetName: 'Sheet11'
                      };

                        apiService.post('/brand_creatives', formData, config)
                        .then(function (data) {
                            console.log(data);
                            console.log(data.data);
                            row.entity.subGridOptions.data = data.data.rows;
                        }, function (response) {
                            // this function handlers error
                        });
                    }
                });
            },
            columnDefs: [
                { field: 'spend_index', sort: { direction: 'desc', priority: 0 } }
            ]
        };

        vm.gridOptions.columnDefs = [
            // { name: 'id', pinnedLeft:true, width: '60' },
            { name: 'rank', displayName: 'Rank', width: '70' },
            { field: 'brand_name', displayName: 'Brand', headerCellClass: $scope.highlightFilteredHeader,
            cellTemplate: '<div class="grid-action-cell">'+ '<span ng-if="'+$rootScope.displayBtns+'==1" '+$rootScope.displayBtns+'><i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i></span><span><a href="#" ng-click="view_adv_tab({{row.entity.advertiser_name}},{{row.entity.adv_id}},'+c_dir+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+',\'brand\',{{row.entity.id}},{{COL_FIELD}},\'ranking\',{{row.entity.need_help}});" title="{{COL_FIELD}}" href="#">{{COL_FIELD}}</a></span></div>', width: '250'},

            { name: 'creative_count', displayName: 'Creatives',
            cellTemplate: '<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subGridOptions.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>', width: '100' },

            { name: 'category_name', displayName: 'Category', cellTemplate:'<a href="#"><span ng-if="row.entity.category_name!=\'\'" class="tooltip-hover" ng-click="fetchList({{row.entity.id}},'+formData.type+',{{COL_FIELD}});"><i class="fa fa-caret-down float-right"></i>{{COL_FIELD}} <div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_row.entity.id" style="display:none;"></div></span><span ng-if="row.entity.category_name==\'\'"> - </span></a>', width: '180' },

            { name: 'advertiser_name', displayName: 'Advertiser', cellTemplate:'<a href="#"><span ng-if="row.entity.advertiser_name!=\'\'" class="tooltip-hover" ng-click="view_adv_tab({{row.entity.advertiser_name}},{{row.entity.adv_id}},'+c_dir+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+',\'adv\',\'\',\'\',\'ranking\',{{row.entity.need_help}});">{{COL_FIELD}} <div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_row.entity.id" style="display:none;"></div></span><span ng-if="row.entity.advertiser_name==\'\'"> - </span></a>', width: '230' },

            { name: 'airings', displayName: 'Airings', cellTemplate:'<a href="#"><span ng-if="row.entity.airings!=\'\'" class="ranking_airings" ng-click="viewAiringGraph({{row.entity.brand_name}},{{row.entity.id}},\'dow\','+formData.network_code+',\'all_day\',\'all_hour\',{{row.entity.networks}},{{row.entity.airings}},'+formData.c+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+','+formData.responseType+','+formData.spanish+',\'brand\',\'\',\'\',\'\');">{{COL_FIELD}} </span><span ng-if="row.entity.airings==\'\'"> - </span></a>', width: '110' },

            { name: 'spend_index', displayName: 'Spend ($)', cellTemplate:'<a href="#"><span ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" ng-click="viewAiringSpendGraph({{row.entity.brand_name}},{{row.entity.id}},\'dow\','+formData.network_code+',\'all_day\',\'all_hour\',{{row.entity.networks}},{{row.entity.spend_index}},'+formData.c+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+','+formData.responseType+','+formData.spanish+',\'brand\',\'\',\'\',\'\');">{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> - </span></a>', width: '106' },

            { name: 'national', displayName:'National', width: '96', cellTemplate:'<span ng-if="row.entity.national !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.national ==\'\'">0</span>' },

            { name: 'local', displayName: 'DPI', cellTemplate: '<span ng-if="row.entity.local !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.local ==\'\'">0</span>', width: '90' },

            { name: 'asd', displayName:'ASD', width: '90', cellTemplate: '<span ng-if="row.entity.asd !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.asd ==\'\'">0 sec</span>' },

            { name: 'total_weeks', displayName: 'Weeks', cellTemplate: '<span ng-if="row.entity.total_weeks !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.total_weeks ==\'\'">0</span>', width: '100' },

            { name: 'tracking', displayName: "Tracking", cellTemplate: '<a href="#"><i custom-attr="brand_{{row.entity.id}}" class="fa fa-eye-slash grey-eye" title="Track"></i></a>', width: '110' }
        ];
        apiService.post('/filter_results', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptions.data = data.data.rows;
        }, function (response) {
            // this function handlers error
        });

    }
    $scope.uigridDataBrand(formdata);
});

angular.module('drmApp').controller('FilterCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    console.log('filter called');
  });
  