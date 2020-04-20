angular.module("drmApp").controller("RankingController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal, $compile, modalConfirmService, uiGridConstants, uiGridExporterConstants){
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }

    $scope.initialisation = function() {
        $scope.page_call = 'ranking';
        $scope.page = $state.current.name;
        $rootScope.networkDisplayName = '';
        $rootScope.headerDisplay = 1;
        
        //date filter
        $scope.otherDiv = 0;
        $scope.ranking = {searchText: ''};
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
            expandableRowTemplate: '<div ui-grid="row.entity.subBrandGridOptions" ui-grid-exporter ui-grid-expandable ui-grid-selection ui-grid-pagination class="grid" style="height:385px;"></div>',
            expandableRowHeight: 385,
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
                $scope.gridApi = gridApi;
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
                { name: 'rank', displayName: 'Rank', width: '70' },
                { field: 'brand_name', displayName: 'Brand', headerCellClass: $scope.highlightFilteredHeader, cellTemplate: '<div class="grid-action-cell"><span ng-if="'+$rootScope.displayBtns+'==1" '+$rootScope.displayBtns+'><i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i></span><span><a href="" ng-click="grid.appScope.view_adv_tab(row.entity.advertiser_name,row.entity.adv_id,\''+c_dir+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\'brand\',row.entity.id,row.entity.brand_name,\'ranking\',row.entity.need_help);" title="row.entity.brand_name">{{COL_FIELD}}</a></span></div>', width: '250', sort: { direction: uiGridConstants.ASC, priority: 1 }},

                { name: 'creative_count', displayName: 'Creatives',
                cellTemplate: '<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subBrandGridOptions.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>', width: '100' },

                { name: 'category_name', displayName: 'Category', cellTemplate:'<div ng-click="grid.appScope.fetchList(row.entity,\''+formData.type+'\');"><a href="javascript://"><span ng-if="row.entity.category_name!=\'\'" class="tooltip-hover"><i class="fa fa-caret-down float-right"></i>{{COL_FIELD}} - </span></a><div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_{{row.entity.id}}" style="display:none;"></div></span><span ng-if="row.entity.category_name==\'\'"></div>', width: '180' },

                { name: 'advertiser_name', displayName: 'Advertiser', cellTemplate:'<a href="#"><span ng-if="row.entity.advertiser_name!=\'\'" class="tooltip-hover" ng-click="grid.appScope.view_adv_tab(row.entity.advertiser_name,row.entity.adv_id,\''+c_dir+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\'adv\',\'\',\'\',\'ranking\',row.entity.need_help);">{{COL_FIELD}} <div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_row.entity.id" style="display:none;"></div></span><span ng-if="row.entity.advertiser_name==\'\'"> - </span></a>', width: '230' },

                { name: 'airings', displayName: 'Airings', cellTemplate:'<span ng-if="row.entity.airings!=\'\'" class="ranking_airings" ng-click="grid.appScope.viewAiringSpendGraph(row.entity.brand_name, row.entity.id, \'airings\',\'brand\');">{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> - </span>', width: '110' },

                { name: 'spend_index', displayName: 'Spend ($)', cellTemplate:'<a href=""><span ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" ng-click="grid.appScope.viewAiringSpendGraph(row.entity.brand_name, row.entity.id,\'total_spend\',\'brand\');">{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> - </span></a>', width: '106', sort: { direction: uiGridConstants.DESC, priority: 0 }},

                { name: 'national', displayName:'National', width: '96', cellTemplate:'<span ng-if="row.entity.national !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.national ==\'\'">0</span>' },

                { name: 'local', displayName: 'DPI', cellTemplate: '<span ng-if="row.entity.local !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.local ==\'\'">0</span>', width: '90' },

                { name: 'asd', displayName:'ASD', width: '90', cellTemplate: '<span ng-if="row.entity.asd !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.asd ==\'\'">0 sec</span>' },

                { name: 'total_weeks', displayName: 'Weeks', cellTemplate: '<span ng-if="row.entity.total_weeks !=\'\'">{{COL_FIELD}}</span><span ng-if="row.entity.total_weeks ==\'\'">0</span>', width: '100' },

                { name: 'tracking', displayName: "Tracking", cellTemplate: '<a href="#"><i custom-attr="brand_\'{{row.entity.id}}\'" class="fa fa-eye-slash grey-eye" title="Track"></i></a>', width: '110' }
            ];
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.fetchList = function(rowEntity, tab) {
        let flag = '';
        let id = row_id = rowEntity.id;
        let category_name = rowEntity.category_name;
        var data = {
            id: id,
            tab: tab,
            unchecked_category: '',
            is_adv_page : sessionStorage.is_adv_page
        }
        var html = '';
        apiService.post('/display_categories', data, $scope.apiHeaderConfig)
        .then(function (response) {
            let data = response.data;
            if (data.status) {
                if (flag == 'my_report') {
                    $('#cat_col_dropdown_' + row_id).parent('td').addClass('table-overflow');
                    $('#cat_col_dropdown_' + row_id).parent('td').find('a').addClass('link-overflow');
                    global_id = row_id;
                } else if (sessionStorage.is_adv_page == 1) {
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
                } else if (sessionStorage.is_adv_page == 1) {
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
        // var formData = {"sd":"2020-02-24","ed":"2020-03-01","startDate":1,"val":1,"c":1,"type":0,"cat":"all","flag":2,"spanish":"0,1","responseType":"(response_url = 1 or response_mar = 1 or response_sms = 1 or response_tfn = 1 )","unchecked_category":"","length_unchecked":0,"creative_duration":"10,15,20,30,45,60,75,90,105,120,180,240,300","new_filter_opt":"none","lifetime_flag":false,"all_ytd_flag":false,"refine_filter_opt":"","refine_filter_opt_text":"","refine_apply_filter":0,"applied_ids":"","primary_tab":"", "_search": false, "nd":'1583484966962', "row":20, "page":1, "sidx": "spend_index", "sort":"desc", "totalrows":2000};

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
            expandableRowHeight: 385,
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
                            expandableRowHeight: 200,
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

    $scope.uigridRefineByBrand = function() {
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

    $scope.initializeRankingPage = function() {
        $scope.applyFilter();
    }
    // $scope.uigridDataBrand(formdata);

   

    $scope.openNewTypeModal = function() {
        $scope.openModal('./templates/modals/newTypeDialog.html','newCtrl','md modal-dialog-centered');
    }

    $scope.openRefineModal = function() {
        $scope.openModal('./templates/modals/refineDialog.html','refineCtrl','md modal-dialog-centered');
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

    $scope.openNetworkLogModal = function() {
        $scope.openModal('./templates/modals/networkLogDialog.html','networkLogCtrl','xl modal-dialog-centered');
    }

    $scope.openProgramModal = function() {
        $scope.openModal('./templates/modals/programModalDialog.html','programCtrl','md modal-dialog-centered');
    }
    $scope.viewAiringSpendGraph = function(name, id, column, tab ) {
        $scope.page_call = 'airings_detail';
        $scope.brand_id = $scope.id = id;
        $scope.brand_name = name;
        $scope.active_tab = 'dow';
        $scope.area_clicked = column;
        $scope.tab  =tab; // according to brand or creative brand_networks will be called
        // $scope.uigridAiringSpend(name, id, active_tab, all_network, all_day, all_hour, network_cnt, spend, c, tab, val, sd, ed, returnText, lang, area, adv_name, brand_name, brand_id, network_id, network_dpi);
    }

    $scope.view_adv_tab = function(adv_name, adv_id, c, tab, val, sd, ed, call_from, call_id, call_name, call_page, need_help) {
        debugger;
        $scope.adv_id = adv_id;
        $scope.adv_name = adv_name;
        $scope.call_from = call_from;
        $scope.call_id = call_id;
        $scope.call_name = call_name;
        $scope.othr_grid_for = 'adv';
        $scope.active_tab = tab;
        $scope.sidx = "airings";
        $scope.page_call = 'advertiser_detail';
    }

    $scope.backToRankingpage = function() {
       $scope.page_call = 'ranking';
    }

    $scope.overlayForAirings = function (record_id, creative_id, header_name) {
        $scope.record_id = record_id;
        $scope.creative_id = creative_id;
        $scope.header_name = header_name;
        $scope.modalInstance =  $uibModal.open({
            templateUrl: "./templates/modals/RefineByTFNDialog.html",
            controller: "RefineByTFNModalCtrl",
            size: 'lg modal-dialog-centered',
        });
    }
    
});

angular.module('drmApp').controller('trackCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService, $compile) {
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});

angular.module('drmApp').controller('newCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService, $compile) {
    $scope.checkRadioButton = function() {
        $scope.newType = 'none';
        if($scope.newCheckBox) {
            $scope.newType = ($rootScope.type == 'brands')  ? 'brands' : 'advertisers';
        } else {
            
        }
    }

    $scope.newCheckBox = function() {
        $scope.newCheckBox = true;
    }

    $scope.applyModal = function() {
        $uibModalInstance.dismiss();
    }

    $scope.applyModal = function() {
        $rootScope.$broadcast("CallParentMethod", {'newType' : $scope.newType, 'newCheckBox' : $scope.newCheckBox });
        $uibModalInstance.dismiss();
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});

angular.module('drmApp').controller('programCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService, $compile) {
    $scope.all_ranking_program  = $scope.programs_ids != '' ? false : true;
    $scope.checkedPrograms      = angular.copy($rootScope.checkedRankingPrograms);

    $scope.applyModal = function() {
        $uibModalInstance.dismiss();
    }

    $scope.getCheckedPrograms = function() {
        var checked_programs = [];
        if(!$scope.all_ranking_program) {
            angular.forEach($rootScope.ranking_programs, function(item, key) {
                angular.forEach(item, function(v, k) {
                    if(v.isSelected) {
                        checked_programs.push(k);
                    }
                });
            });
            $scope.length_of_programs  = checked_programs.length;
            $scope.checked_programs_id = checked_programs.join(',');
        }
    }

    $scope.selectAllProgram = function() {
        angular.forEach($rootScope.ranking_programs, function(item, key) {
            angular.forEach(item, function(v, k) {
                v.isSelected = $scope.all_ranking_program;
            });
        });
        $scope.getCheckedPrograms();
    }

    $scope.checkAllProgram = function(program) {
        $scope.getCheckedPrograms();
        $scope.all_ranking_program = true;
       if($scope.length_of_programs != $rootScope.ranking_programs.length ) {
           $scope.all_ranking_program = false;
       }
    }

    $scope.applyModal = function() {
        console.log($scope.checked_programs_id);
        $rootScope.$broadcast("CallParentMethod", {'network_id' : $scope.selectedNetwork,'network_alias' : $scope.selectedNetworkAlias,'program_id' : $scope.checked_programs_id});
        $uibModalInstance.dismiss();
    }

    $scope.resetPrograms = function() {
        $scope.all_ranking_program = true;
        angular.forEach($rootScope.ranking_programs, function(item, key) {
            angular.forEach(item, function(v, k) {
                v.isSelected = true;
            });
        });
        $rootScope.$broadcast("CallParentMethod", {'network_id' : $scope.selectedNetwork,'network_alias' : $scope.selectedNetworkAlias});
        $uibModalInstance.dismiss();
    }


    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});


angular.module('drmApp').controller('refineCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.refine_by = '';
    $scope.search_by_tfn = '';

    $scope.applyModal = function() {
        $rootScope.$broadcast("CallParentMethod", {'refine_by' : $scope.refineBy, 'search_by_tfn' : $scope.search_by_tfn });
        $uibModalInstance.dismiss();
    }

    $scope.resetModal = function() {
        $rootScope.$broadcast("CallParentMethod", {'refine_by' : ''});
        $uibModalInstance.dismiss();
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    $scope.applyRefineByFilter = function () {
        refine_apply_filter = 1;
        export_refine_apply_filter = 1;
        $scope.refine_by = 'All';
        $scope.search_by_tfn = '';
        var lbl_name = $scope.refine_by ? $scope.refine_by == '800' ? 'TFN' : $scope.refine_by == 'url' ? 'URL' : 'All' : 'All';
        // $("#lbl_outter_tfn_url_new").html(lbl_name);
        // $('#refine_by').html(lbl_name);
        // $('#refine_by_text').html($scope.search_by_tfn);
        $scope.filter($scope.type, $rootScope.active_flag);
        // $("#tnf-url").modal('hide');
        // $('#uiview').css('overflow', '');
    }

    $scope.resetRefineFilter = function () {
        $scope.ranking.refine_by = 'All';
        $scope.ranking.search_by_tfn = '';
        $scope.closeModal();
    }
});

angular.module('drmApp').controller('networkLogCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
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
            ndata = JSON.parse(sessionStorage.all_networks_data);
        } else {
            ndata = JSON.parse(sessionStorage.active_networks_data);
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
        if (sessionStorage.activeNetwroksParams != undefined && sessionStorage.active_networks_data != undefined) {
            if (JSON.stringify(params) == sessionStorage.activeNetwroksParams) {
                $scope.dataOfAllNetworks('ActiveNetwroksFunction');
                call_api = 0;
            } else {
                sessionStorage.activeNetwroksParams = JSON.stringify(params);
            }
        } else {
            sessionStorage.activeNetwroksParams = JSON.stringify(params);
        }

        if(call_api) {
            $scope.networkNotLoaded = 1;
            apiService.post('/get_networks_with_all_filters', params)
            .then(function (response) {
                $scope.networkNotLoaded = 0;
                var data = response.data;
                sessionStorage.setItem('active_networks_data', JSON.stringify(data));
                $scope.dataOfAllNetworks('ActiveNetwroksFunction');
            }),(function (data, status, headers, config) {
            });
        }
    }

    $scope.getAllActiveInactiveNetworks = function () {
        var new_filter_opt = 'none';
        if (sessionStorage.all_networks_data != undefined) {
            $scope.dataOfAllNetworks('AllNetwroksFunction', new_filter_opt);
        } else {
            $scope.networkNotLoaded = 1;
            apiService.post('/get_all_active_inactive_networks', {})
            .then(function (response) {
                var ndata = response.data;
                $scope.networkNotLoaded = 0;
                sessionStorage.setItem('all_networks_data', JSON.stringify(ndata));
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

angular.module('drmApp').controller('RefineByTFNModalCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService){
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    // Call brand List ui Grid
    $scope.uigridRefineByTFNModdal = function() {
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var c_dir = '6';
        formData.sidx = "last_aired";
        formData.sord = "desc";
        formData.network_id = $rootScope.network_id;
        formData.id = $rootScope.brand_id;
        formData.area = 'brand';
        formData.brand_id= $scope.record_id;
        formData.record_id= $scope.record_id;
        formData.creative_id = $scope.creative_id;
        formData.header_name = $scope.header_name;

        vm.gridOptionsRefineModal = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            paginationPageSize: 10,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
        };

        vm.gridOptionsRefineModal.columnDefs = [
            { name: 'network_name', pinnedLeft:true, displayName:'Network Name', cellTemplate: "<span title='row.entity.network_name'>{{COL_FIELD | limitTo: 12}}</span>"},
            { name: 'last_aired', pinnedLeft:true, displayName:'Date/Time'},
            { name: 'breaktype', pinnedLeft:true, displayName:'Breaktype'},
            { name: 'phone_number', pinnedLeft:true, displayName:'Phone Number'},
            { name: 'web_address', pinnedLeft:true, displayName:'Web Address'},
            { name: 'program_name', pinnedLeft:true, displayName:'Program Name', cellTemplate: "<span title='row.entity.program_name'>{{COL_FIELD | limitTo: 18}}</span>"},
            { name: 'video', pinnedLeft:true, displayName:'Play', cellTemplate: '<a href="javascript:void(0);"><i class="fa fa-play-circle-o fa-2x" onclick="displayThumbnail(row.entity.airing_id,row.entity.network_code,\'network_log_video\')"></i></a>'},
            { name: 'thumbnail', pinnedLeft:true, displayName:'View', cellTemplate: '<a href="javascript:void(0);"><i class="fa fa-picture-o fa-2x col-00beff" ng-click="displayThumbnail(row.entity.airing_id,row.entity.network_code,\'network_log_thumbnil\')"></i></a>'},
        ];
        apiService.post('/view_airings_layout_tfn', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptionsRefineModal.data = data.data.rows;
        }, function (response) {
            // this function handlers error
            console.log("rejected with", response);
        });
    }
    $scope.uigridRefineByTFNModdal();

});