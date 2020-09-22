"use strict";
angular.module("drmApp").controller("RankingController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal, $compile, modalConfirmService, uiGridConstants, uiGridExporterConstants){
    $scope.ranking = {};
    
    $scope.initialisation = function() {
        // $scope.page_call = 'ranking';
        $scope.page = $state.current.name;
        $rootScope.networkDisplayName = '';
        $rootScope.headerDisplay = 1;
        $scope.idsOfSelectedRows = 0;
        $scope.applied_list_ids = '';
        
        //date filter
        $scope.otherDiv = 0;
        $scope.ranking = {searchText: ''};
        $scope.ranking.complete_name = localStorage.complete_name;console.log($scope.ranking.complete_name);

    }

    $scope.apiHeaderConfig = {
        headers : {
            'Content-Type': 'application/json; charset=utf-8'
        }
    }

    $scope.initialisation() ;

    feather.replace();

    /* Ranking Grid Start */

    // var formdata = {'sd': data['sd'], 'ed': data['ed'], 'startDate': $scope.ranking.selectDate, 'val': data['selectDateDropDown'], 'c': $scope.ranking.selectClassfication, 'type': data['type'], 'cat': data['cat_id'], 'flag': active_flag, spanish: $scope.ranking.selectLang, responseType: $scope.ranking.returnText,'unchecked_category': data['unchecked_cat'], 'length_unchecked': unchecked_len, 'creative_duration': duration, 'new_filter_opt': new_filter_opt, 'lifetime_flag': lifetime_flag, 'all_ytd_flag': all_ytd_flag, 'refine_filter_opt': refine_filter_opt, 'refine_filter_opt_text': refine_filter_opt_text, 'refine_apply_filter': refine_apply_filter, 'programs_ids': airings_data['all_programs_ids'],'applied_ids' : $scope.ranking.applied_list_ids , 'primary_tab' : $scope.ranking.applied_list_type}; 
    $scope.downloadCSV = function(){
        $scope.gridApi.exporter.csvExport(uiGridExporterConstants.VISIBLE,uiGridExporterConstants.ALL);
    }
    $rootScope.uigridDataBrand = function() {
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var c_dir = '6';
        $scope.loading = true;
        var correctTotalPaginationTemplate =
        "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"prev-page\"></div></button> Page <input ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\"> of </abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"next-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-page\"></div></button></div></div><div class=\"ui-grid-pager-count-container\"></div></div>";
    // formData.network_code = '';
        vm.gridOptions = {
            expandableRowTemplate: '<div ui-grid="row.entity.subBrandGridOptions" ui-grid-exporter ui-grid-expandable ui-grid-selection ui-grid-pagination class="grid"></div>',
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            enableColumnMenus: false,
            showTreeExpandNoChildren: true,
            exporterCsvFilename: 'myFile.csv',
            exporterCsvLinkElement: angular.element(document.querySelectorAll(".custom-csv-link-location")),
            exporterExcelFilename: 'myFile.xlsx',
            exporterExcelSheetName: 'Sheet1',
            exporterMenuPdf: false,
            gridMenuShowHideColumns: false,
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
                $scope.gridApi =  gridApi;
                gridApi.selection.on.rowSelectionChanged($scope, function(row){ 
                    $scope.idsOfSelectedRows = $scope.gridApi.selection.getSelectedRows().length;
                });
        
                gridApi.selection.on.rowSelectionChangedBatch($scope, function(row){ 
                    $scope.idsOfSelectedRows = $scope.gridApi.selection.getSelectedRows().length;
                });
                // $scope.idsOfSelectedRows = $scope.gridApi.selection.getSelectedRows()
                $scope.gridApi.grid.registerRowsProcessor( $scope.singleFilter, 200 );

                gridApi.expandable.on.rowExpandedStateChanged($scope, function (row) {
                    // formData.network_code = null;
                    // formData.programs_ids = '';
                    formData.is_adv_page  = 0;
                    formData.brand_id = row.entity.brand_id;
                    formData.tab = 'brand';
                    if (row.isExpanded) {
                        row.entity.subBrandGridOptions = {
                            columnDefs: [
                            // { name: 'id', displayName:'id', width:'50' },
                            { name: 'creative_name', displayName: 'Creatives', cellTemplate: '<span ng-if="'+$rootScope.displayBtns+'==1" '+$rootScope.displayBtns+'><i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i></span><span><a href="" ui-sref="advertiser_detail({id: row.entity.id})">{{COL_FIELD}}</a></span>' },
                            { name: 'language', displayName: 'Type'},
                            { name: 'classification', displayName: 'Classification'},
                            { name: 'duration', displayName: 'Duration'},

                            { name: 'airings', displayName: 'Airings', cellTemplate:'<a href="" ng-if="row.entity.airings!=\'\'" class="ranking_airings"  ui-sref="airing_detail({id: row.entity.id, area : \'airings\',tab : \'brand\'})">{{COL_FIELD}} </a><span ng-if="row.entity.airings==\'\'"> - </span></a>'},

                            { name: 'spend_index', displayName: 'Spend ($)', cellTemplate:'<a href="" ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" ui-sref="airing_detail({id: row.entity.id, area : \'total_spend\',tab : \'brand\'})">{{COL_FIELD}}</a><span ng-if="row.entity.spend_index==\'\'"> 0 </span></a>'},

                            { name: 'response_type', displayName: 'Response Type', enableSorting: false, cellTemplate:'<span class="response_img"><a href="#" ng-if=row.entity.response_url == 1 title="URL" ><img src="/drmetrix/assets/img/url-icon.svg" alt="URL" /></a><a href="#" ng-if=row.entity.response_sms == 1 title="SMS"><img src="/drmetrix/assets/img/sms-icon.svg" alt="SMS" /></a><a href="#" ng-if=row.entity.response_tfn == 1 title="Telephone"><img src="/drmetrix/assets/img/telephone-icon.svg" alt="Telephone" /></a><a href="#" ng-if=row.entity.response_mar == 1 title="Mobile"><img src="/drmetrix/assets/img/mobile-icon.svg" alt="Mobile" /></a>'
                            },

                            { name: 'national', displayName:'National', cellTemplate:'<span ng-if="row.entity.national !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.national ==\'\'">0</span>' },
                            { name: 'local', displayName: 'DPI', cellTemplate: '<span ng-if="row.entity.local !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.local ==\'\'">0</span>'},
                            { name: 'first_detection', displayName: 'First Aired',cellTemplate: '<div class="ngCellText">{{row.entity.first_detection}}</div>',},
                            { name: 'last_aired', displayName: 'Last Aired', cellTemplate: '<div class="ngCellText">{{row.entity.last_aired}}</div>'},
                            { name: 'video', displayName: '', enableSorting: false, cellTemplate:'<i class="fa fa-play-circle-o fa-2x" ng-if="row.entity.thumbnail==\'\'" style="color:#cbcccc;"></i><a href="#basicModalCamp" ng-if="row.entity.thumbail!=\'\'"><i class="fa fa-play-circle-o fa-2x" onclick="playvideo({{row.entity.thumbnail}},'+formData.sd+','+formData.ed+',0,\'none\')"></i></a>'},
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
                            row.entity.subBrandGridOptions.data = data.data.rows;
                        }, function (response) {
                            // this function handlers error
                        });
                    }
                });
            },
        };

        apiService.post('/filter_results', formData, config)
        .then(function (response) {
            var data = response.data;
            $scope.PostDataResponse = formData;
            vm.gridOptions.data = data.rows;
            var checkedPrograms = [];
            if (data.programs.length != 0) {
                $rootScope.checkedRankingPrograms   =  checkedPrograms;
                $rootScope.ranking_programs         =  data.programs;
                 $scope.loading = false;
            }
            vm.gridOptions.columnDefs = [
                // { name: 'id', pinnedLeft:true, width: '60' },
                //ng-click="grid.appScope.view_adv_tab(row.entity.advertiser_name,row.entity.adv_id,\''+c_dir+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\'brand\',row.entity.id,row.entity.brand_name,\'ranking\',row.entity.need_help);"
                { name: 'rank', displayName: 'Rank', cellClass: 'text-c',  width: '100' },
                { field: 'brand_name', displayName: 'Brand', headerCellClass: $scope.highlightFilteredHeader, cellTemplate: '<div class="grid-action-cell"><span ng-if="'+$rootScope.displayBtns+'==1" '+$rootScope.displayBtns+'><i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i></span><span><a href="" ui-sref="advertiser_detail({id: row.entity.adv_id, tab: \'brand\'})" title="row.entity.brand_name">{{COL_FIELD}}</a></span></div>', width: '320', sort: { direction: uiGridConstants.ASC, priority: 1 }},

                { name: 'creative_count', displayName: 'Creatives',
                cellTemplate: '<a href=""><span><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subBrandGridOptions.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</span></a>', width: '100' },

                { name: 'category_name', displayName: 'Category', cellTemplate:'<div ng-click="grid.appScope.fetchList(row.entity,\''+formData.type+'\');"><a href="javascript://"><span ng-if="row.entity.category_name!=\'\'" class="tooltip-hover"><i class="fa fa-caret-down float-right"></i>{{COL_FIELD}} - </span></a><div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_{{row.entity.id}}" style="display:none;"></div></span><span ng-if="row.entity.category_name==\'\'"></div>', width: '320' },

                { name: 'advertiser_name', displayName: 'Advertiser', cellTemplate:'<a href="#" ng-if="row.entity.advertiser_name!=\'\'" class="tooltip-hover" ui-sref="advertiser_detail({id: row.entity.adv_id, tab: \'adv\'})">{{COL_FIELD}}</a><span ng-if="row.entity.advertiser_name==\'\'"> - </span></a>', width: '300' },

                { name: 'airings', displayName: 'Airings', cellClass: 'text-c', cellTemplate:'<a href="" ng-if="row.entity.airings!=\'\'" class="ranking_airings" ui-sref="airing_detail({id: row.entity.id, area : \'airings\',tab : \'brand\'})">{{COL_FIELD}}</a><span ng-if="row.entity.spend_index==\'\'"> - </span>', width: '100' },

                { name: 'spend_index', displayName: 'Spend ($)', cellClass: 'text-c', cellTemplate:'<a href="" ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" ui-sref="airing_detail({id: row.entity.id, area : \'total_spend\',tab : \'brand\'})">{{COL_FIELD}}</a><span ng-if="row.entity.spend_index==\'\'"> - </span></a>', width: '100', sort: { direction: uiGridConstants.DESC, priority: 0 }},

                { name: 'national', displayName:'National', cellClass: 'text-c', width: '100', cellTemplate:'<span ng-if="row.entity.national !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.national ==\'\'">0</span>' },

                { name: 'local', displayName: 'DPI', cellClass: 'text-c', cellTemplate: '<span ng-if="row.entity.local !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.local ==\'\'">0</span>', width: '100' },

                { name: 'asd', displayName:'ASD', width: '100', cellClass: 'text-c', cellTemplate: '<span ng-if="row.entity.asd !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.asd ==\'\'">0 sec</span>' },

                { name: 'total_weeks', displayName: 'Weeks', cellClass: 'text-c', cellTemplate: '<span ng-if="row.entity.total_weeks !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.total_weeks ==\'\'">0</span>', width: '100' },

                { name: 'tracking', displayName: "", cellClass: 'text-c', cellTemplate: '<a href="#"><i custom-attr="brand_\'{{row.entity.id}}\'" class="fa fa-eye-slash grey-eye" title="Track"></i></a>', width: '60' }
            ];
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.fetchList = function(rowEntity, tab) {
        let flag = '';
        let id = row_id = rowEntity.id;
        var data = {
            id: id,
            tab: tab,
            unchecked_category: '',
            is_adv_page : 1
        }
        var html = '';
        apiService.post('/display_categories', data, $scope.apiHeaderConfig)
        .then(function (response) {
            let data = response.data;
            if (data.status) {
                console.log(flag);
                if (flag == 'my_report') {
                    $('#cat_col_dropdown_' + row_id).parent('td').addClass('table-overflow');
                    $('#cat_col_dropdown_' + row_id).parent('td').find('a').addClass('link-overflow');
                    global_id = row_id;
                } else if (localStorage.is_adv_page == 1) {
                    $('#advshort_cat_col_dropdown_' + id).parent('td').addClass('table-overflow');
                    $('#advshort_cat_col_dropdown_' + id).parent('td').find('a').addClass('link-overflow');
                    $('#advlong_cat_col_dropdown_' + id).parent('td').addClass('table-overflow');
                    $('#advlong_cat_col_dropdown_' + id).parent('td').find('a').addClass('link-overflow');
                } else {
                    $('#cat_col_dropdown_' + id).parent('td').addClass('table-overflow');
                    $('#cat_col_dropdown_' + id).parent('td').find('a').addClass('link-overflow');
                    global_id = id;
                }

                html = '';
                $.each(data.result, function (key, value) {
                    html = html + '<ul class="no-bullet cat_col_subcat" style="display: flex;"><li>' + value.category + '</li><li>' + value.sub_category + '</li></ul>';
                });
                if (flag == 'my_report') {
                    $('#cat_col_dropdown_' + row_id).html(html);
                    $('#cat_col_dropdown_' + row_id).css('display', 'block');
                } else if (localStorage.is_adv_page == 1) {
                    $('#advshort_cat_col_dropdown_' + id).html(html);
                    $('#advshort_cat_col_dropdown_' + id).css('display', 'block');
                    $('#advlong_cat_col_dropdown_' + id).html(html);
                    $('#advlong_cat_col_dropdown_' + id).css('display', 'block');
                } else {
                    $('#cat_col_dropdown_' + id).html(html);
                    $('#cat_col_dropdown_' + id).css('display', 'block');
                }
            }
        }, function (response) {
            // this function handlers error
        });
    }

    $rootScope.uigridDataAdv = function() {
          var formData = $rootScope.formdata;
        var correctTotalPaginationTemplate =
    "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-triangle\"><div class=\"first-bar\"></div></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-triangle prev-triangle\"></div></button> <input type=\"number\" ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\">/</abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"last-triangle next-triangle\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-triangle\"><div class=\"last-bar\"></div></div></button></div></div></div>";
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        // var c_dir = $scope.creative_type == 'short' ? '6':'1';
        var c_dir = '6';

        vm.gridOptions = {
            expandableRowTemplate: '/drmetrix_angular_clean/templates/expandableRowTemplate.html',
            // expandableRowHeight: 385,
            enableGridMenu: true,
            enableSelectAll: false,
            enableSorting: true,
            showTreeExpandNoChildren: true,
            enableExpandableRowHeader: false,
            enableColumnMenus: false,
            //Pagination
            paginationPageSizes: [20],
            paginationPageSize: 10,
            paginationTemplate: correctTotalPaginationTemplate,
            onRegisterApi: function (gridApi) {
                gridApi.expandable.on.rowExpandedStateChanged($scope, function (row) {
                    formData.network_code = '';
                    formData.programs_ids = '';
                    formData.is_adv_page  = 0;
                    formData.adv_id = row.entity.id;
                    formData.tab = 'adv';
                    if (row.isExpanded) {
                        row.entity.subGridOptions = {
                            columnDefs: [
                            // { name: 'id', displayName:'id', width:'50' },
                            { name: 'brand_name', displayName:'Brand Name', cellTemplate:'<i class="fa fa-circle" id="{{data.rows.is_brand_active == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i><span><a href="#" title="{{row.entity.advertiser_name}}" ng-click="grid.appScope.view_adv_tab(row.entity.advertiser_name,row.entity.id,\''+c_dir+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\'brand\',row.entity.id,row.entity.brand_name,\'ranking\',row.entity.need_help)" >{{COL_FIELD}}</a></span>' },

                            { name: 'creatives_count', displayName: 'Creatives', cellTemplate:'<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subGridOptions2.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>' },

                            { name: 'category_name', displayName:'Category', cellTemplate:'<a href="#"><span ng-if="row.entity.category_name!=\'\'" class="tooltip-hover" ng-click="grid.appScope.fetchList(row.entity.id,\''+formData.type+'\',row.entity.category_name);"><i class="fa fa-caret-down float-right"></i>{{COL_FIELD}} <div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_row.entity.id" style="display:none;"></div></span><span ng-if="row.entity.category_name==\'\'"> - </span></a>' },
                            { name: 'airings', displayName:'Airings', cellTemplate:'<span ng-if="row.entity.airings!=\'\'" class="ranking_airings">{{COL_FIELD}} </span><span ng-if="row.entity.airings==\'\'"> - </span>'  },
                            { name: 'spend_index', displayName: 'Spend',  cellTemplate: '<span ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" >{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> - </span>' },

                            { name: 'national', displayName: 'National %'},
                            { name: 'local', displayName:'DPI %'},
                            { name: 'asd', displayName:'ADS'},
                            { name: 'total_weeks', displayName: 'Weeks' },
                            { name: 'tracking', displayName: "Tracking", cellTemplate: '<a href="#"><i custom-attr="brand_{{row.entity.id}}" class="fa fa-eye-slash grey-eye" title="Track"></i></a>' }
                        ],expandableRowTemplate: '/drmetrix_angular_clean/templates/expandableRowTemplate.html',
                            enableGridMenu: true,
                            enableSelectAll: true,
                            paginationPageSize: 5,
                            enableSorting: true,
                            showTreeExpandNoChildren: false,
                            enableExpandableRowHeader: false,
                            enableRowSelection: true,
                            paginationTemplate: correctTotalPaginationTemplate,

                            onRegisterApi: function (gridApi2) {
                                gridApi2.expandable.on.rowExpandedStateChanged($scope, function (Secondrow) {
                                    formData.network_code = '';
                                    formData.programs_ids = '';
                                    formData.is_adv_page  = 0;
                                    formData.brand_id = Secondrow.entity.id;
                                    formData.tab = 'adv_brand';
                                    if (Secondrow.isExpanded) {
                                        Secondrow.entity.subGridOptions2 = {
                                            columnDefs: [
                                                { name: 'id', displayName:'id' },
                                            ]
                                        };
                                        apiService.post('/brand_creatives', formData, config)
                                        .then(function (data) {
                                            Secondrow.entity.subGridOptions2.data = data.data.rows;
                                        }, function (response) {
                                            // this function handlers error
                                        });
                                    }
                                });
                            },
                        };

                        apiService.post('/adv_brand', formData, config)
                        .then(function (data) {
                            row.entity.subGridOptions.data = data.data.rows;
                        }, function (response) {
                            // this function handlers error
                        });
                    }
                });
            },
        };

        vm.gridOptions.columnDefs = [
            // { name: 'id', displayName: 'AVD_ID' },
            { name: 'rank', displayName: 'Rank' },

            { name: 'advertiser_name', displayName: 'Advertiser', cellTemplate: '<a href=""><span title="{{row.entity.advertiser_name}}" ng-click="grid.appScope.view_adv_tab(row.entity.advertiser_name,row.entity.id,\''+c_dir+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\'adv\',row.entity.id,row.entity.advertiser_name,\'ranking\',row.entity.need_help)" >{{COL_FIELD}}</span></a>', pinnedLeft:true },

            { name: 'hidden_brand', displayName: 'Brand', cellTemplate: '<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.entity.groupHeader==true || row.entity.subGridOptions.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>' },

            { name: 'airings', displayName: 'Airings', pinnedLeft:true },
            { name: 'spend_index', displayName: 'Spend' },
            { name: 'national', displayName: 'National %', pinnedLeft:true },
            { name: 'local', displayName: 'Local' },
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

    $rootScope.uigridRefineData = function() {
        var formData = $rootScope.formdata;
        formData.refine_filter_opt = 800;
        formData.refine_filter_opt_text = 800;
        formData.refine_apply_filter=1;
        formData.type = 1;
        formData.sd = "2020-02-24";
        formData.ed = "2020-03-01";
        formData.length_unchecked = 0;
        formData.primary_tab = "";
        formData.unchecked_category = "";
        formData.sidx = "airings";
        formData.sord = "sord";
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var c_dir = '6';
        var correctTotalPaginationTemplate =
        "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"prev-page\"></div></button> Page <input ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\"> of </abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"next-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-page\"></div></button></div></div><div class=\"ui-grid-pager-count-container\"></div></div>";
        formData.network_code = '';
        vm.gridOptions = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            showTreeExpandNoChildren: false,
            enableExpandableRowHeader: true,
            //Pagination
            paginationPageSizes: [20],
            paginationPageSize: 20,
            paginationTemplate: correctTotalPaginationTemplate,
        };

        apiService.post('/apply_refine_filters', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptions.data = data.data.rows;

            vm.gridOptions.columnDefs = [
                // { name: 'id', pinnedLeft:true, width: '60' },
                { name: 'creative_name', pinnedLeft:true, displayName: 'Creative', cellTemplate: '<span><a href=""  title="row.entity.creative_name" ng-click="grid.appScope.view_adv_tab(row.entity.advertiser_name,row.entity.adv_id,\''+c_dir+'\',\''+formData.type+'\',\''+formData.c+'\',\''+formData.sd+'\',\''+formData.ed+'\',\'creatives\',row.entity.creative_id,row.entity.creative_name,\'ranking\',row.entity.need_help)" >{{COL_FIELD | limitTo: 35}}</a></span>' },

                { name: 'brand_name', pinnedLeft:true, displayName: 'Brand', cellTemplate: '<i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i><span><a href="#"  title="row.entity.brand_name" ng-click="grid.appScope.view_adv_tab(row.entity.advertiser_name,row.entity.adv_id,\''+c_dir+'\',\''+formData.type+'\',\''+formData.c+'\',\''+formData.sd+'\',\''+formData.ed+'\',\'brand\',row.entity.ID,row.entity.brand_name,\'ranking\',row.entity.need_help)" >{{COL_FIELD}}</a></span>' },

                { name: 'advertiser_name', pinnedLeft:true, displayName: 'Advertiser', cellTemplate: '<span ng-if=row.entity.advertiser_name!=\'\'><i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i><span><a href="#" ng-click="grid.appScope.view_adv_tab(row.entity.advertiser_name,row.entity.adv_id,\''+c_dir+'\',\''+formData.type+'\',\''+formData.c+'\',\''+formData.sd+'\',\''+formData.ed+'\',\'adv\',\'\',\'\',\'ranking\',row.entity.need_help)" >{{COL_FIELD}}</a></span></span><span ng-if="row.entity.advertiser_name==\'\'"> - </span>' },

                { name: 'airings', pinnedLeft:true, displayName: 'Airings' },

                { name: 'display_tfn_column', pinnedLeft:true, displayName: 'TNF', cellTemplate: '<span ng-if=row.entity.tfn_comma_found ==1><a href=""><i class="fa fa-caret-down float-right"></i><span>{{COL_FIELD | limitTo: 12}}</span></a><div class="refine_tfn_dropdown select_refine_tfn_dropdown" id="refine_tfn_dropdown_row.entity.creative_id" style="display:none;"></div></span><span ng-if=row.entity.tfn_comma_found!=1>{{COL_FIELD | limitTo: 12}}</span>' },

                { name: 'display_url_column', pinnedLeft:true, displayName: 'URL' },
                { name: 'first_aired', pinnedLeft:true, displayName: 'First Aired' },
                { name: 'last_aired', pinnedLeft:true, displayName: 'Last_Aired' },
                { name: 'thumbnail', displayName: 'Report', cellTemplate: '<a href="javascript:void(0);" ><i class="fa fa-file-text-o fa-2x" style="font-size: 1.6em;" ng-click="grid.appScope.overlayForAirings(row.entity.ID,row.entity.creative_id,row.entity.brand_name)"></i></a>' , width: '50'}
            ];
        }, function (response) {
            // this function handlers error
        });
    }

     //date filter
     $scope.date_detail = function (date) {
        $scope.lifetimeOther = false;
        $scope.mask  = 0;
        if($scope.ytdOther && !$scope.allOther && typeof(date) == 'undefined') { // ytd checked
            $scope.selectDate = 'year34_'+$scope.selectedYear+'_'+$scope.years[$scope.selectedYear]["media_year_start"]+'_'+$scope.years[$scope.selectedYear]["media_year_end"];
            date = $scope.selectDate;
            $scope.allOther = false;
            $scope.lifetime_flag = 0;
            $scope.calender_flag = 0;
        } 
        if(!$scope.ytdOther && !$scope.allOther && typeof(date) == 'undefined') {// ytd unchecked
            $scope.selectDate = 'week31_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_start']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_end'];
            date = $scope.selectDate;
        }
        if($scope.allOther &&  !$scope.ytdOther && typeof(date) == 'undefined') { // all checked
            $scope.selectDate = 'year34_'+$scope.selectedYear+'_'+$scope.years[$scope.selectedYear]["media_year_start"]+'_'+$scope.years[$scope.selectedYear]["media_year_end"];
            date = $scope.selectDate;
            $scope.ytdOther = false;
            $scope.lifetime_flag = 0;
            $scope.calender_flag = 0;
        } 
        if(!$scope.allOther && !$scope.ytdOther && typeof(date) == 'undefined') {// all unchecked
            $scope.selectDate = 'week31_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_start']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_end'];
            date = $scope.selectDate;
        }
        if (date != 1) {
            $scope.matching_criteria = 0;
        }
        if($scope.selectDate.indexOf("year34") > -1) {
            $scope.mask = 1;
        }
        var date_detail = date.split('_');
        if (date_detail[1] !== undefined) {
            var week = date_detail[1];
            var sd_1 = date_detail[2].split('-');
            var sd_2 = sd_1[1] + '/' + sd_1[2] + '/' + sd_1[0];
            var ed_1 = date_detail[3].split('-');
            var ed_2 = ed_1[1] + '/' + ed_1[2] + '/' + ed_1[0];
            var date_diaply = '';
            if (date_detail[0] == 'week31') {
                date_diaply = "Media Week ";
            } else if (date_detail[0] == 'month32') {
                date_diaply = "Media Month ";
                week = "(" + $scope.monthArray[date_detail[1]]['data'] + ")";
            } else if (date_detail[0] == 'quarter33') {
                date_diaply = "Media Quarter ";
            }
            $scope.selectDate = $scope.selectDate = $rootScope.selected_date = date;
            $scope.date_range = date_diaply + week + ' - ' + sd_2 + ' thru ' + ed_2;
            $scope.findDiff(sd_2);
            $scope.calender_flag = 0;
            // $scope.checkForLifetimeSelection();
        }
    }

     $scope.findDiff = function (end_date, val) {
        $rootScope.displayBtns = 0;
        var date1 = new Date($scope.today_date);
        var date2 = new Date(end_date);
        var timeDiff = Math.abs(date2.getTime() - date1.getTime());
        var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
        if (diffDays >= 30) {
            $rootScope.displayBtns = 1;
        }
    }
    
    $scope.date_filter = function (val) {
        if((val >= 6) && (val <= 11)){
            $scope.ytdOther = false;
            $scope.allOther = false;
            $scope.lifetimeOther = false;
        }
        $scope.mask = 0;
        if($scope.lifetimeOther && typeof(val) != 'undefined') { // lifetime checked
            $scope.mask = 1;
            val = $scope.selectDate = 5;
        }
        
        if(!$scope.lifetimeOther && val == 5 && $scope.showOtherDiv) { // lifetime unchecked
            $scope.selectDate = 'week31_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_start']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_end'];
            var date_detail = $scope.selectDate.split('_');
            if (date_detail[1] !== undefined) {
                var week = date_detail[1];
                var sd_1 = date_detail[2].split('-');
                var sd_2 = sd_1[1] + '/' + sd_1[2] + '/' + sd_1[0];
                var ed_1 = date_detail[3].split('-');
                var ed_2 = ed_1[1] + '/' + ed_1[2] + '/' + ed_1[0];
                var date_diaply = '';
                if (date_detail[0] == 'week31') {
                    date_diaply = "Media Week ";
                } else if (date_detail[0] == 'month32') {
                    date_diaply = "Media Month ";
                    week = "(" + $scope.monthArray[date_detail[1]]['data'] + ")";
                } else if (date_detail[0] == 'quarter33') {
                    date_diaply = "Media Quarter ";
                }
                $scope.date_range = date_diaply + week + ' - ' + sd_2 + ' thru ' + ed_2;
            }
        }
        console.log(val);
        // val = $scope.selectDate;
        $scope.matching_criteria = 0;
        if (val == 1) { // Last Week
            $scope.freq_filter_options = { daily: false, weekly: true, monthly: false, quarterly: false };
            $scope.date_range = 'Media Week ' + $scope.week_calendar_id + ' - ' + $scope.media_start_date + ' thru ' + $scope.media_end_date;
            $scope.sd = $scope.media_start_db;
        }
        if (val == 2) { // Current Week
            $scope.freq_filter_options = { daily: true, weekly: false, monthly: false, quarterly: false };
            $scope.date_range = 'Current Week ' + $scope.current_calendar_id + ' - ' + $scope.current_start_date + ' thru ' + $scope.current_end_date;
            $scope.sd = $scope.current_start_db;
        }
        if (val == 3) {
            $scope.date_range = 'Quarter ' + $scope.number_of_quarter + ' - ' + $scope.last_quarter_start_date + ' to ' + $scope.last_quarter_end_date;
            $scope.sd = $scope.last_quarter_start_date;
        }
        if (val == 4) {
            $scope.date_range = 'Year Of ' + $scope.last_media_year;
        }
        if (val == 5) {
            $scope.allOther = false;
            $scope.ytdOther = false;
            $scope.matching_criteria = val;
            $scope.date_range = $scope.lifetime_year + ' - ' + $scope.lifetime_min_sd + ' thru ' + $scope.lifetime_max_ed;
            $scope.sd = $scope.lifetime_min_sd;
            $scope.findDiff($scope.sd, val);
        }
        if (val == 6) { // Last Week
            $scope.freq_filter_options = { daily: false, weekly: true, monthly: false, quarterly: false };
            $scope.date_range = 'Last Media Week ' + $scope.week_calendar_id + ' - ' + $scope.media_start_date + ' thru ' + $scope.media_end_date;
            $scope.sd = $scope.media_start_date;
        }
        if (val == 7) { // Last Month
            $scope.freq_filter_options = { daily: false, weekly: false, monthly: true, quarterly: false };
            $scope.date_range = 'Last Media Month ' + $scope.month_calendar_id + ' - ' + $scope.media_month_date + ' thru ' + $scope.media_monthend_date;
            $scope.sd = $scope.media_month_date;
        }
        if (val == 8) { // Last Quarter
            $scope.freq_filter_options = { daily: false, weekly: false, monthly: false, quarterly: true };
            $scope.date_range = 'Last Media Quarter ' + $scope.number_of_quarter + ' - ' + $scope.last_quarter_start_date + ' thru ' + $scope.last_quarter_end_date;
            $scope.sd = $scope.last_quarter_start_date;
        }
        if (val == 9) { // Current Week
            $scope.freq_filter_options = { daily: true, weekly: false, monthly: false, quarterly: false };
            $scope.date_range = 'Current Media Week ' + $scope.current_calendar_id + ' - ' + $scope.current_start_date + ' thru ' + $scope.current_end_date;
            $scope.sd = $scope.current_start_date;
        }
        if (val == 10) { // Current Month
            $scope.matching_criteria = val;
            $scope.date_range = 'Current Media Month ' + $scope.currentmonth_calendar_id + ' - ' + $scope.media_currentmonth_date + ' thru ' + $scope.media_currentmonthend_date;
            $scope.sd = $scope.media_currentmonth_date;
        }
        if (val == 11) { // Current Quarter
            $scope.matching_criteria = val;
            $scope.date_range = 'Current Media Quarter ' + $scope.number_of_currentquarter + ' - ' + $scope.current_quarter_start_date + ' thru ' + $scope.current_quarter_end_date;
            $scope.sd = $scope.current_quarter_db_start_date;
        }

        if (val == 'calender') {
            $scope.findDiff($scope.start_date);
            // $('#datepicker_checkbox').attr('checked', 'checked');
            // $scope.date_range = 'Date Range - ' + sessionStorage.disp_start_date + ' thru ' + sessionStorage.disp_end_date;
            // $rootScope.initialise_datepicker();
            // $('#datepicker_checkbox').prop('checked', true);
        }
        
        $scope.selectDate =  val;
    }
   

    $scope.initializeRankingPage = function() {
        $scope.date_filter(1);
        $scope.applyFilter();
    }
    
    $scope.filterGridWithSearchText = function() {
        $scope.gridApi.grid.refresh();
    }

    $scope.singleFilter = function( renderableRows ){
        var matcher = new RegExp($scope.ranking.searchText);
        renderableRows.forEach( function( row ) {
          var match = false;
          [ $rootScope.type == 'brands' ? "brand_name" : "advertiser_name" ].forEach(function( field ){
            if ( row.entity[field].match(matcher) ){
              match = true;
            }
          });
          if ( !match ){
            row.visible = false;
          }
        });
        return renderableRows;
      };

    $scope.overlayForAirings = function (record_id, creative_id, header_name) {
        $scope.record_id = record_id;
        $scope.creative_id = creative_id;
        $scope.header_name = header_name;
        $scope.openModalDialog('refine_by_report');
    }
    
});


