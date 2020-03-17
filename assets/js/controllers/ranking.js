angular.module("drmApp").controller("RankingController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal, $compile){
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }
    
    $scope.getDisplayDurationText = function () {
        $scope.duration_display_text = ($scope.selectedDurations.length === $scope.creative_short_duration.length) ? ' (All Duration)' : ($scope.selectedDurations.length == 1) ? ' (' + $scope.selectedDurations[0] + 's)' : $scope.selectedDurations.length > 1 ? ' (Multi Duration)' : '';
    }

     
    $scope.mapValueWithSession = function (data) {
        for (var i in data) {
            $scope[data[i]] = sessionStorage[data[i]];
        }
    }

    var displayDateList = [ 'media_start_date', 'media_end_date', 'media_month_date',
    'media_monthend_date', 'lifetime_year', 'lifetime_min_sd', 'lifetime_max_ed'];
    $scope.mapValueWithSession(displayDateList);

    var databaseFormatDate = [ 'media_start_db', 'media_end_db', 'current_start_db',
    'current_end_db', 'media_month_start_db', 'media_month_end_db', 'last_quarter_db_start_date', 'last_quarter_db_end_date', 'media_currentmonth_start_db',
    'media_currentmonth_end_db', 'current_quarter_db_start_date', 'current_quarter_db_end_date', 'last_year_db_start_date', 'last_year_db_end_date', 'lifetime_db_min_sd', 'lifetime_db_max_ed'];

    $scope.mapValueWithSession(databaseFormatDate);

        //date filter
        $scope.findDiff = function (end_date, val) {
            $rootScope.displayBtns = 0;
            var date1 = new Date(sessionStorage.today_date);
            var date2 = new Date(end_date);
            var timeDiff = Math.abs(date2.getTime() - date1.getTime());
            var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
            if (diffDays >= 30) {
                $rootScope.displayBtns = 1;
            }
        }
        $scope.date_filter = function (val) {
            console.log(val);
            if((val >= 6) && (val <= 11)){
                $scope.ytdOther = false;
                $scope.allOther = false;
                $scope.lifetimeOther = false;
            }
            $scopemask = 0;
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
            // val = $scope.selectDate;
            $scope.matching_criteria = 0;
            if (val == 1) { // Last Week
                $scope.freq_filter_options = { daily: false, weekly: true, monthly: false, quarterly: false };
                $scope.date_range = 'Media Week ' + sessionStorage.week_calendar_id + ' - ' + sessionStorage.media_start_date + ' thru ' + sessionStorage.media_end_date;
                $scope.sd = sessionStorage.media_start_date;
            }
            if (val == 2) { // Current Week
                $scope.freq_filter_options = { daily: true, weekly: false, monthly: false, quarterly: false };
                $scope.date_range = 'Current Week ' + sessionStorage.current_calendar_id + ' - ' + sessionStorage.current_start_date + ' thru ' + sessionStorage.current_end_date;
                $scope.sd = sessionStorage.current_start_db;
            }
            if (val == 3) {
                $scope.date_range = 'Quarter ' + sessionStorage.number_of_quarter + ' - ' + sessionStorage.last_quarter_start_date + ' to ' + sessionStorage.last_quarter_end_date;
                $scope.sd = sessionStorage.last_quarter_start_date;
            }
            if (val == 4) {
                $scope.date_range = 'Year Of ' + sessionStorage.last_media_year;
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
                $scope.date_range = 'Last Media Week ' + sessionStorage.week_calendar_id + ' - ' + sessionStorage.media_start_date + ' thru ' + sessionStorage.media_end_date;
                $scope.sd = sessionStorage.media_start_date;
            }
            if (val == 7) { // Last Month
                $scope.freq_filter_options = { daily: false, weekly: false, monthly: true, quarterly: false };
                $scope.date_range = 'Last Media Month ' + sessionStorage.month_calendar_id + ' - ' + sessionStorage.media_month_date + ' thru ' + sessionStorage.media_monthend_date;
                $scope.sd = sessionStorage.media_month_date;
            }
            if (val == 8) { // Last Quarter
                $scope.freq_filter_options = { daily: false, weekly: false, monthly: false, quarterly: true };
                $scope.date_range = 'Last Media Quarter ' + sessionStorage.number_of_quarter + ' - ' + sessionStorage.last_quarter_start_date + ' thru ' + sessionStorage.last_quarter_end_date;
                $scope.sd = sessionStorage.last_quarter_start_date;
            }
            if (val == 9) { // Current Week
                $scope.freq_filter_options = { daily: true, weekly: false, monthly: false, quarterly: false };
                $scope.date_range = 'Current Media Week ' + sessionStorage.current_calendar_id + ' - ' + sessionStorage.current_start_date + ' thru ' + sessionStorage.current_end_date;
                $scope.sd = sessionStorage.current_start_date;
            }
            if (val == 10) { // Current Month
                $scope.matching_criteria = val;
                $scope.date_range = 'Current Media Month ' + sessionStorage.currentmonth_calendar_id + ' - ' + sessionStorage.media_currentmonth_date + ' thru ' + sessionStorage.media_currentmonthend_date;
                $scope.sd = sessionStorage.media_currentmonth_date;
            }
            if (val == 11) { // Current Quarter
                $scope.matching_criteria = val;
                $scope.date_range = 'Current Media Quarter ' + sessionStorage.number_of_currentquarter + ' - ' + sessionStorage.current_quarter_start_date + ' thru ' + sessionStorage.current_quarter_end_date;
                $scope.sd = sessionStorage.current_quarter_db_start_date;
            }
    
            if (val == 'calender') {
                sessionStorage.is_apply_calendar = 1;
                $scope.findDiff(sessionStorage.start_date);
                // $('#datepicker_checkbox').attr('checked', 'checked');
                $scope.date_range = 'Date Range - ' + sessionStorage.disp_start_date + ' thru ' + sessionStorage.disp_end_date;
                // $rootScope.initialise_datepicker();
                // $('#datepicker_checkbox').prop('checked', true);
            }
          
            $scope.selectDate = sessionStorage.selectDate = val;
    }

    $scope.initialisation = function() {
        $scope.page_call = 'ranking';
        $scope.page = $state.current.name;
        $rootScope.networkDisplayName = '';
        $scope.editable = 0
        $rootScope.headerDisplay = 1;
        $rootScope.complete_name = localStorage.complete_name;

        //Classification
        $scope.shortFormClassification = [
            {
                "index": 1,
                "id": "all_short",
                'selected': true,
                "value": "All Short form"
            }, {
                "index": 2,
                "id": "sf_products",
                'selected': true,
                "value": "Short Form Products"
            }, {
                "index": 3,
                "id": "lead_gen",
                'selected': true,
                "value": "Lead Generation"
            }, {
                "index": 4,
                "id": "brand_dr",
                'selected': true,
                "value": "Brand/DR",
            }, {
                "index": 5,
                "id": "sf_retail_products",
                'selected': true,
                "value": "Retail Rankings",
                "display_text": "AsOnTV Retail Rankings",
            }
        ];

        
        $scope.longFormClassification = [
            {
                "index": 6,
                "id": "lf_creative1",
                'selected': true,
                "value": "28.5m Creative",
            }, {
                "index": 7,
                "id": "lf_retail_products",
                'selected': false,
                "value": "Retail Rankings",
                "display_text": "AsOnTV Retail Rankings (28.5m)",
            }
        ];

        $scope.creative_type = 'short';
        $scope.checkedShortClassification = [1, 2, 3, 4, 5];
        $scope.checkedLongClassification = [6];
        $scope.selectedDurations = [10, 15, 20, 30, 45, 60, 75, 90, 105, 120, 180, 240, 300];
        $scope.creative_short_duration = [10, 15, 20, 30, 45, 60, 75, 90, 105, 120, 180, 240, 300];
        $scope.getDisplayDurationText();

        //Language
        $scope.selectLang = $rootScope.selectLang ? $rootScope.selectLang : '0,1';
        $scope.languages = [
            {
                "id": "all",
                "value": "0,1",
                "display_text": "All"
            }, {
                "id": "english",
                "value": "0",
                "display_text": "English"
            }, {
                "id": "spanish",
                "value": "1",
                "display_text": "Spanish"
            }
        ];

        //response types
        $scope.response_header = 'or';
        $scope.responseTypeselected = ['URL', 'MAR', 'SMS', 'TFN'];
        $scope.responseTypes = [
            {
                "value": "URL",
                "id": "urlCheckbox",
                "custom-value": "response_url=1",
                "custom-null-value": "response_url=0"
            }, {
                "value": "SMS",
                "id": "smsCheckbox",
                "custom-value": "response_sms=1",
                "custom-null-value": "response_sms=0"
            }, {
                "value": "TFN",
                "id": "telephoneCheckbox",
                "custom-value": "response_tfn=1",
                "custom-null-value": "response_tfn=0"
            }, {
                "value": "MAR",
                "id": "mobRepsonseCheckbox",
                "custom-value": "response_mar=1",
                "custom-null-value": "response_mar=0"
            },
        ];

        //date filter
        $scope.otherDiv = 0;
        $scope.selectDate = sessionStorage.selectDate = 1;
        $scope.date_filter($scope.selectDate);
        
    }

    $scope.initialisation() ;

    feather.replace();

    /* Ranking Grid Start */

    // var formdata = {'sd': data['sd'], 'ed': data['ed'], 'startDate': $scope.ranking.selectDate, 'val': data['selectDateDropDown'], 'c': $scope.ranking.selectClassfication, 'type': data['type'], 'cat': data['cat_id'], 'flag': active_flag, spanish: $scope.ranking.selectLang, responseType: $scope.ranking.returnText,'unchecked_category': data['unchecked_cat'], 'length_unchecked': unchecked_len, 'creative_duration': duration, 'new_filter_opt': new_filter_opt, 'lifetime_flag': lifetime_flag, 'all_ytd_flag': all_ytd_flag, 'refine_filter_opt': refine_filter_opt, 'refine_filter_opt_text': refine_filter_opt_text, 'refine_apply_filter': refine_apply_filter, 'programs_ids': airings_data['all_programs_ids'],'applied_ids' : $scope.ranking.applied_list_ids , 'primary_tab' : $scope.ranking.applied_list_type}; 

    $scope.editableContent = function() {
        $scope.editable = 1;
    }
    
    $scope.cancelFilter = function() {
        $scope.editable = 0;
    }

    //Start - Classification

    $scope.selectClassification = function(creative_type) {
        $scope.creative_type = creative_type;
     
    }

    $scope.selectAllShortCreativeDuration = function() {
        if ($scope.selectedDurations.length === $scope.creative_short_duration.length) {
            $scope.selectedDurations = [];
        } else if ($scope.selectedDurations.length === 0 || $scope.selectedDurations.length > 0) {
            $scope.selectedDurations = $scope.creative_short_duration.slice(0);
        }
    }

    $scope.exists = function (item, list) {
        return list.indexOf(item) > -1;
    };


    $scope.isAllDurationChecked = function () {
        $scope.getDisplayDurationText();
        return $scope.selectedDurations.length === $scope.creative_short_duration.length;
    }

    $scope.setClassification = function(classification_scope) {
        var c = classification_scope.index;
        var selected = classification_scope.selected;
        if($scope.creative_type == 'short') {
            if (c == 1) {
                $scope.checkedShortClassification = [1, 2, 3, 4, 5];
                if (selected === false) {
                    angular.forEach($scope.shortFormClassification, function (value, key) {
                        value.selected = false;
                    });
                    $scope.checkedShortClassification = [];
                } else {
                    angular.forEach($scope.shortFormClassification, function (value, key) {
                        value.selected = true;
                    });
                }
            } else {
                $scope.shortFormClassification[0].selected = false;
                if (selected === true) {
                    $scope.checkedShortClassification.push(c);
                } else {
                    var i = $scope.checkedShortClassification.indexOf(c);
                    $scope.checkedShortClassification.splice(i, 1);
                }
            }
        } else {
            if (selected === true) {
                $scope.checkedLongClassification.push(c);
            } else {
                var i = $scope.checkedLongClassification.indexOf(c);
                $scope.checkedLongClassification.splice(i, 1);
            }
        }
        
    }
    

    $scope.checkCreativeDuration = function (item, list) {
        var idx = list.indexOf(item);
        if (idx > -1) {
            $scope.selectedDurations.splice(idx, 1);
        }
        else {
            $scope.selectedDurations.push(item);
        }
        $scope.creativeSelectDuration = $scope.selectedDurations;
        $scope.getDisplayDurationText();
    }
    //End - Classification
   
    $scope.setLang = function(lang) {
        $rootScope.selectLang = $scope.selectLang = lang;
    }

    $scope.setBreaktype = function (breaktype) {
        $rootScope.selectBreakType = $scope.selectBreakType = breaktype;
    }

    $scope.setResponseTypes = function (header, item) {
        $scope.response_header = header;
        $scope.returnText = '';
        if(item) {
            var idx = $scope.responseTypeselected.indexOf(item.value);
            if (idx > -1) {
                $scope.responseTypeselected.splice(idx, 1);
            }
            else {
                $scope.responseTypeselected.push(item.value);
            }
        }
        
        angular.forEach($scope.responseTypeselected, function(value, key) {
            $scope.returnText += 'response_' + angular.$$lowercase(value) + ' = 1 ' + $scope.response_header + ' ';
        });
        let lastIndex = $scope.returnText.lastIndexOf($scope.response_header);
        $scope.returnText = $scope.returnText.substring(0, lastIndex);

        $scope.responseTypeText = $scope.responseTypeselected.join(' '+ $scope.response_header+ ' ');
        
        $scope.returnText = '('+$scope.returnText+')';
        
    };

    $scope.selectBreakType = 'A';
    $scope.breaktypes = [
        {
            "id": "all_breaktype",
            "value": "A",
            "display_text": "All"
        }, {
            "id": "national",
            "value": "N",
            "display_text": "National"
        }, {
            "id": "local",
            "value": "L",
            "display_text": "DPI"
        }
    ];


    $scope.initializeWeeks = function() {
        if($scope.selectDate == 1 || $scope.selectDate == 2 ) {
            angular.forEach($scope.yearsArray, function(y, key) {
                angular.forEach(y.weeks, function(w, key) {
                    if(key == 0 && y.media_year == $scope.selectedYear) {
                        $scope.selectDate = 'week31_'+w.media_week+'_'+w.media_week_start+'_'+w.media_week_end;
                        console.log($scope.selectDate);
                    }
                });
            });
        }
    }

    $scope.showYearDropDownVariable = function () {
        $scope.showYearDropDown = 1;
    }

    $scope.showMediaCalender = function (year) {
        $scope.showYearDropDown = 0;
        $scope.mask = 0;
        $scope.selectedYear = year;
        $scope.selectDate = 1; // initialize to one to display deault 1 media week in all years other dropdwon section
        $scope.initializeWeeks();

    }
    $scope.setOtherDivVariable = function () {
        $scope.otherDiv = 1;
        $scope.showOtherDiv = !$scope.showOtherDiv;
        $scope.mask = 0;
        $scope.initializeWeeks();
        $('#othersDiv1').modal('show');
    }
    
    $scope.date_detail = function (date) {
        $scope.lifetimeOther = false;
        $scope.mask  = 0;
        if($scope.ytdOther && !$scope.allOther && typeof(date) == 'undefined') { // ytd checked
            $scope.selectDate = 'year34_'+$scope.selectedYear+'_'+$scope.years[$scope.selectedYear]["media_year_start"]+'_'+$scope.years[$scope.selectedYear]["media_year_end"];
            date = $scope.selectDate;
            $scope.allOther = false;
            sessionStorage.lifetime_flag = 0;
            sessionStorage.calender_flag = 0;
        } 
        if(!$scope.ytdOther && !$scope.allOther && typeof(date) == 'undefined') {// ytd unchecked
            $scope.selectDate = 'week31_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_start']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_end'];
            date = $scope.selectDate;
        }
        if($scope.allOther &&  !$scope.ytdOther && typeof(date) == 'undefined') { // all checked
            $scope.selectDate = 'year34_'+$scope.selectedYear+'_'+$scope.years[$scope.selectedYear]["media_year_start"]+'_'+$scope.years[$scope.selectedYear]["media_year_end"];
            date = $scope.selectDate;
            $scope.ytdOther = false;
            sessionStorage.lifetime_flag = 0;
            sessionStorage.calender_flag = 0;
        } 
        if(!$scope.allOther && !$scope.ytdOther && typeof(date) == 'undefined') {// all unchecked
            $scope.selectDate = 'week31_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_start']+'_'+$scope.years[$scope.selectedYear]['weeks'][0]['media_week_end'];
            date = $scope.selectDate;
        }
        if (date != 1) {
            $scope.matching_criteria = 0;
        }
        console.log($scope.selectDate);
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
            $scope.selectDate = sessionStorage.selectDate = $rootScope.selected_date = date;
            $scope.date_range = date_diaply + week + ' - ' + sd_2 + ' thru ' + ed_2;
            $scope.findDiff(sd_2);
            sessionStorage.calender_flag = 0;
            // $scope.checkForLifetimeSelection();
        }
    }

    $scope.setLifetimeVariables = function() {
        sessionStorage.lifetime_flag = 0;
        if($scope.lifetimeOther) {
            sessionStorage.lifetime_flag = 1;
        } 
        // $scope.checkForLifetimeSelection();

        if (sessionStorage.calender_flag == 1) {
            $scope.apply_filter = 0;
            $scope.lifetime_error = 1;
        }
    }

    $scope.uigridDataBrand = function() {
        var formData = $rootScope.formdata;
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
    console.log("formData "+formData.network_code);
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
                    formData.network_code = null;
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
                            { name: 'airings', displayName: 'Airings', cellTemplate:'<a href=""><span ng-if="row.entity.airings!=\'\'" class="ranking_airings" ng-click="grid.appScope.viewAiringSpendGraph({{row.entity.creative_name}},{{row.entity.id}},\'dow\','+formData.network_code+',\'all_day\',\'all_hour\',{{row.entity.networks}},{{row.entity.airings}},'+formData.c+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+','+formData.responseType+','+formData.spanish+',\'creative\',{{row.entity.creative_name}},'+formData.brand_name+','+formData.brand_id+');">{{COL_FIELD}} </span><span ng-if="row.entity.airings==\'\'"> - </span></a>',  width: '110' },

                            { name: 'spend_index', displayName: 'Spend ($)', cellTemplate:'<a href="#"><span ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" ng-click="grid.appScope.viewAiringSpendGraph(\'{{row.entity.creative_name}}\',\'{{row.entity.id}}\',\'dow\','+formData.network_code+',\'all_day\',\'all_hour\',\'{{row.entity.networks}}\',\'{{row.entity.spend_index}}\','+formData.c+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+','+formData.responseType+','+formData.spanish+',\'creative\',\'{{row.entity.creative_name}}\','+formData.brand_name+','+formData.brand_id+');">{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> 0 </span></a>', width: '110' },

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

        apiService.post('/filter_results', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptions.data = data.data.rows;

            vm.gridOptions.columnDefs = [
                // { name: 'id', pinnedLeft:true, width: '60' },
                { name: 'rank', displayName: 'Rank', width: '70' },
                { field: 'brand_name', displayName: 'Brand', headerCellClass: $scope.highlightFilteredHeader,
                cellTemplate: '<div class="grid-action-cell">'+ '<span ng-if="'+$rootScope.displayBtns+'==1" '+$rootScope.displayBtns+'><i class="fa fa-circle" id="{{data.rows.is_active_brand == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i></span><span><a href="#" ng-click="grid.appScope.view_adv_tab({{row.entity.advertiser_name}},{{row.entity.adv_id}},'+c_dir+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+',\'brand\',{{row.entity.id}},{{COL_FIELD}},\'ranking\',{{row.entity.need_help}});" title="{{COL_FIELD}}" href="#">{{COL_FIELD}}</a></span></div>', width: '250'},

                { name: 'creative_count', displayName: 'Creatives',
                cellTemplate: '<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subGridOptions.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>', width: '100' },

                { name: 'category_name', displayName: 'Category', cellTemplate:'<a href="#"><span ng-if="row.entity.category_name!=\'\'" class="tooltip-hover" ng-click="grid.appScope.fetchList(\'{{row.entity.id}}\','+formData.type+',{{COL_FIELD}});"><i class="fa fa-caret-down float-right"></i>{{COL_FIELD}} <div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_row.entity.id" style="display:none;"></div></span><span ng-if="row.entity.category_name==\'\'"> - </span></a>', width: '180' },

                { name: 'advertiser_name', displayName: 'Advertiser', cellTemplate:'<a href="#"><span ng-if="row.entity.advertiser_name!=\'\'" class="tooltip-hover" ng-click="grid.appScope.view_adv_tab(\'{{row.entity.advertiser_name}}\',\'{{row.entity.adv_id}}\','+c_dir+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+',\'adv\',\'\',\'\',\'ranking\',\'{{row.entity.need_help}}\');">{{COL_FIELD}} <div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_row.entity.id" style="display:none;"></div></span><span ng-if="row.entity.advertiser_name==\'\'"> - </span></a>', width: '230' },

                { name: 'airings', displayName: 'Airings', cellTemplate:'<a href=""><span ng-if="row.entity.airings!=\'\'" class="ranking_airings" ng-click="grid.appScope.viewAiringSpendGraph(row.entity.brand_name, row.entity.id, \'dow\',\''+formData.network_code+'\',\'all_day\',\'all_hour\',row.entity.networks,row.entity.spend_index,\''+formData.c+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\''+formData.responseType+'\',\''+formData.spanish+'\',\'brand\',\'\',\'\',\'\');">{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> - </span></a>', width: '110' },

                { name: 'spend_index', displayName: 'Spend ($)', cellTemplate:'<a href=""><span ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" ng-click="grid.appScope.viewAiringSpendGraph(row.entity.brand_name, row.entity.id, \'dow\',\''+formData.network_code+'\',\'all_day\',\'all_hour\',row.entity.networks,row.entity.spend_index,\''+formData.c+'\',\''+formData.type+'\',\''+formData.val+'\',\''+formData.sd+'\',\''+formData.ed+'\',\''+formData.responseType+'\',\''+formData.spanish+'\',\'brand\',\'\',\'\',\'\');">{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> - </span></a>', width: '106' },

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

    $scope.uigridDataAdv = function() {
        var correctTotalPaginationTemplate =
    "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-triangle\"><div class=\"first-bar\"></div></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-triangle prev-triangle\"></div></button> <input type=\"number\" ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\">/</abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"last-triangle next-triangle\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-triangle\"><div class=\"last-bar\"></div></div></button></div></div></div>";
        var formData = {"sd":"2020-02-24","ed":"2020-03-01","startDate":1,"val":1,"c":1,"type":0,"cat":"all","flag":2,"spanish":"0,1","responseType":"(response_url = 1 or response_mar = 1 or response_sms = 1 or response_tfn = 1 )","unchecked_category":"","length_unchecked":0,"creative_duration":"10,15,20,30,45,60,75,90,105,120,180,240,300","new_filter_opt":"none","lifetime_flag":false,"all_ytd_flag":false,"refine_filter_opt":"","refine_filter_opt_text":"","refine_apply_filter":0,"applied_ids":"","primary_tab":"", "_search": false, "nd":'1583484966962', "row":20, "page":1, "sidx": "spend_index", "sort":"desc", "totalrows":2000};

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
            enableSelectAll: true,
            enableSorting: true,
            showTreeExpandNoChildren: true,
            enableExpandableRowHeader: true,
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
                            { name: 'brand_name', displayName:'Brand Name', cellTemplate:'<i class="fa fa-circle" id="{{data.rows.is_brand_active == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i><span><a href="#" title="{{row.entity.advertiser_name}}" ng-click="view_adv_tab({{row.entity.advertiser_name}},{{row.entity.id}},'+c_dir+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+',\'brand\',{{row.entity.id}},{{COL_FIELD}},\'ranking\',{{row.entity.need_help}})" >{{COL_FIELD}}</a></span>' },

                            { name: 'creatives_count', displayName: 'Creatives', cellTemplate:'<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subGridOptions2.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>' },

                            { name: 'category_name', displayName:'Category', cellTemplate:'<a href="#"><span ng-if="row.entity.category_name!=\'\'" class="tooltip-hover" ng-click="fetchList(\'{{row.entity.id}}\','+formData.type+',{{COL_FIELD}});"><i class="fa fa-caret-down float-right"></i>{{COL_FIELD}} <div class="cat_col_dropdown select_cat_dropdown" id="cat_col_dropdown_row.entity.id" style="display:none;"></div></span><span ng-if="row.entity.category_name==\'\'"> - </span></a>' },
                            { name: 'airings', displayName:'Airings', cellTemplate:'<a href="#"><span ng-if="row.entity.airings!=\'\'" class="ranking_airings" ng-click="viewAiringGraph(\'{{row.entity.brand_name}}\',\'{{row.entity.id}}\',\'dow\','+formData.network_code+',\'all_day\',\'all_hour\',\'{{row.entity.networks}}\',\'{{row.entity.airings}}\','+formData.c+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+','+formData.responseType+','+formData.spanish+',\'brand\',\'\',\'\',\'\');">{{COL_FIELD}} </span><span ng-if="row.entity.airings==\'\'"> - </span></a>'  },
                            { name: 'spend_index', displayName: 'Spend',  cellTemplate: '<a href=""><span ng-if="row.entity.spend_index!=\'\'" class="ranking_airings" ng-click="viewAiringSpendGraph(\'{{row.entity.brand_name}}\',\'{{row.entity.id}}\',\'dow\','+formData.network_code+',\'all_day\',\'all_hour\',\'{{row.entity.networks}}\',\'{{row.entity.spend_index}}\','+formData.c+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+','+formData.responseType+','+formData.spanish+',\'brand\',\'\',\'\',\'\');">{{COL_FIELD}}</span><span ng-if="row.entity.spend_index==\'\'"> - </span></a>' },
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

            { name: 'advertiser_name', displayName: 'Advertiser', cellTemplate: '<span ng-if="'+$rootScope.displayBtns+'==1" '+$rootScope.displayBtns+'><i class="fa fa-circle" id="{{data.rows.is_active_adv == 1 ? \'active_btn\' : \'inactive_btn\'}}"></i></span><span><a href="#" title="{{row.entity.advertiser_name}}" ng-click="view_adv_tab(\'{{row.entity.advertiser_name}}\',\'{{row.entity.id}}\','+c_dir+','+formData.type+','+formData.val+','+formData.sd+','+formData.ed+',\'adv\',\'{{row.entity.id}}\',\'{{COL_FIELD}}\',\'ranking\',\'{{row.entity.need_help}}\')" >{{COL_FIELD}}</a></span>', pinnedLeft:true },

            { name: 'hidden_brand', displayName: 'Brand', cellTemplate: '<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subGridOptions.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>' },

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

    $scope.initializeRankingPage = function() {
    $rootScope.formdata =  {"sd":"2020-02-24","ed":"2020-03-01","startDate":1,"val":1,"c":1,"type":1,"cat":"all","flag":2,"spanish":"0,1","responseType":"(response_url = 1 or response_mar = 1 or response_sms = 1 or response_tfn = 1 )","unchecked_category":"","length_unchecked":0,"creative_duration":"10,15,20,30,45,60,75,90,105,120,180,240,300","new_filter_opt":"none","lifetime_flag":false,"all_ytd_flag":false,"refine_filter_opt":"","refine_filter_opt_text":"","refine_apply_filter":0,"applied_ids":"","primary_tab":""};

        ($rootScope.type == 'brands') ? $scope.uigridDataBrand() : $scope.uigridDataAdv();
    }
    // $scope.uigridDataBrand(formdata);

    $scope.openNewTypeModal = function() {
        $scope.modalInstance =  $uibModal.open({
            templateUrl: "./templates/modals/newTypeDialog.html",
            controller: "newCtrl",
            size: 'md modal-dialog-centered',
          });
    }

    $scope.openRefineModal = function() {
        $scope.modalInstance =  $uibModal.open({
            templateUrl: "./templates/modals/refineDialog.html",
            controller: "refineCtrl",
            size: 'md modal-dialog-centered',
          });
    }

    $scope.openNetworkLogModal = function() {
        $scope.modalInstance =  $uibModal.open({
            templateUrl: "./templates/modals/networkLogDialog.html",
            controller: "networkLogCtrl",
            size: 'xl modal-dialog-centered',
            backdrop  : false
          });
    }

    $scope.viewAiringSpendGraph = function(name, id, active_tab, all_network, all_day, all_hour, network_cnt, spend, c, tab, val, sd, ed, returnText, lang, area, adv_name, brand_name, brand_id, network_id, network_dpi) {
        $scope.page_call = 'airings_detail';
        $scope.brand_id = $scope.id = id;
        $scope.brand_name = name;
        $scope.active_tab = active_tab;
        $scope.all_network = all_network;
        // $scope.uigridAiringSpend(name, id, active_tab, all_network, all_day, all_hour, network_cnt, spend, c, tab, val, sd, ed, returnText, lang, area, adv_name, brand_name, brand_id, network_id, network_dpi);
    }

    $scope.backToRankingpage = function() {
       $scope.page_call = '/#!/ranking';
    }
    
});

angular.module('drmApp').controller('newCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService, $compile) {
    $scope.checkRadioButton = function() {
        $scope.newType = 'none';
        if($scope.newCheckBox) {
            $scope.newType = ($rootScope.type == 'brands')  ? 'brands' : 'advertisers';
        }
    }

    $scope.applyModal = function() {
        $uibModalInstance.dismiss();
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});

angular.module('drmApp').controller('refineCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.refine_by = '';
    $scope.search_by_tfn = '';
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});

angular.module('drmApp').controller('networkLogCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.selectedLetter = 'all';
    $scope.selectedNetwork = 'All';
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
            apiService.post('/get_networks_with_all_filters', params)
            .then(function (response) {
                var data = response.data;
                sessionStorage.setItem('active_networks_data', JSON.stringify(data));
                $scope.dataOfAllNetworks('ActiveNetwroksFunction');
            }),(function (data, status, headers, config) {
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
        $uibModalInstance.dismiss();
    }

  
   
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});
  