angular.module('drmApp').controller('MainController', function ($scope, $http, $state, $stateParams, $rootScope, apiService, $location, $uibModal) {
    $scope.date = new Date(); // Footer copyright display year
    $rootScope.eulaDisagreeFlag = 0; // this flag will show poup on login page if we disagree eula agreement and redreict to login message with popup message
    $scope.whats_new_toggle = false;
    $rootScope.headerDisplay = 0;
    /* Primary filter */
    $rootScope.complete_name = localStorage.complete_name;
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

    $scope.selectDate = sessionStorage.selectDate = 1;
    $scope.date_filter($scope.selectDate);
        //Classification
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
        $scope.getDisplayDurationText = function () {
            $scope.duration_display_text = ($scope.selectedDurations.length === $scope.creative_short_duration.length) ? ' (All Duration)' : ($scope.selectedDurations.length == 1) ? ' (' + $scope.selectedDurations[0] + 's)' : $scope.selectedDurations.length > 1 ? ' (Multi Duration)' : '';
        }
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
  /* Primary filter */
    $rootScope.options  = ['My','Shared','All'];
    $rootScope.searchTextValidation = '3 or more characters.';
    $rootScope.main_menu = [{
        liid: 'rank',
        nghide: 'superadmin',
        href: 'ranking',
        aclass: 'ranking',
        aid: 'ranking',
        title: 'Home',
        src: './assets/images/menuiconblue/menuiconset-1.svg',
    }, {
        liid: 'networks',
        nghide: 'superadmin || user_company == 0',
        href: 'network',
        aclass: 'my_networks',
        aid: 'my_networks',
        title: 'Networks',
        src: './assets/images/menuiconblue/menuiconset-2.svg',
    }, {
        liid: 'my_reports',
        nghide: 'superadmin',
        href: 'reports',
        aclass: 'my_reports',
        aid: 'reports',
        title: 'Reports',
        src: './assets/images/menuiconblue/menuiconset-3.svg',
    }, {
        liid: 'directories',
        nghide: 'superadmin',
        href: '#',
        aclass: 'directories',
        aid: '',
        title: 'Directories',
        src: './assets/images/menuiconblue/menuiconset-4.svg',
    }, {
        liid: '',
        nghide: 'superadmin',
        href: 'tracking',
        aclass: 'tracking',
        aid: 'tracking',
        title: 'Configure Emails',
        src: './assets/images/menuiconblue/menuiconset-5.svg',
    },];

    $rootScope.whatsNew_menu = [{
        href: 'https://adsphere.drmetrix.com/blog/2020/02/04/new-february-2020-build/',
        title: 'New October Build',
        newBuild: 1
    },
    {
        href: 'https://youtu.be/MVsYJ189Cws',
        title: 'Advertiser Page Update',
        newBuild: 0
    },
    {
        href: 'https://youtu.be/GDqJKAaloiw',
        title: 'Auto-Email Reports',
        newBuild: 0
    },
    {
        href: 'https://youtu.be/kH5B48ws4Y4',
        title: 'New Network Excel',
        newBuild: 0
    },
    {
        href: 'https://youtu.be/BmSgwd2qaAM',
        title: 'New Airing Detail Page',
        newBuild: 0
    },
    {
        href: 'https://www.youtube.com/playlist?list=PLFHwC5pgpTLeNuNCjelEzh7LGrXVvcvo_',
        title: 'Other Training Videos',
        newBuild: 0
    }];
    $rootScope.right_menu = [{
        alt: 'User',
        href: 'userAccount',
        aid: 'user_account',
        title: 'User',
        src: './assets/images/menuiconblue/menuiconset-7.svg',
        target: '',
    }, {
        alt: 'Network List',
        aid: 'network_list',
        title: 'Network List',
        src: './assets/images/menuiconblue/menuiconset-8.svg',
        target: '_blank',
    }, {
        alt: 'AdSphere Blog',
        href: $rootScope.adsphere_blog_url,
        aid: 'blog_status',
        title: 'Blog',
        src: './assets/images/menuiconblue/menuiconset-9.svg',
        target: '_blank',
    }, {
        alt: 'User Guide',
        href: 'https://drmetrix.com/public/AdSphere%20User%20Guide.pdf',
        aid: 'user_guide',
        title: 'User Guide',
        src: './assets/images/menuiconblue/menuiconset-10.svg',
        target: '_blank',
    }, {
        alt: 'Dark Theme',
        aid: 'app_theme',
        title: 'Dark Mode',
        src: './assets/images/menuiconblue/menuiconset-11.svg',
        theme_val: '0' // code remaining - needs to set once set, now set to dark mode off
    }, {
        alt: 'System Status',
        href: $rootScope.SYSTEM_STATUS_URL,
        aid: 'sys_status',
        title: 'System Status',
        src: './assets/images/menuiconblue/menuiconset-12.svg',
        target: '_blank',
    },
    {
        alt: 'Log Out',
        aid: 'log_out',
        title: 'Log Out',
        src: './assets/images/menuiconblue/menuiconset-13.svg',
    }]

    $scope.createYearsArray = function () {
        var ary = [];
        angular.forEach($scope.years, function (val, key) {
            ary.push(val);
        });
       
        $scope.yearsArray = ary.reverse();
    };

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1;//January is 0! 
    var yyyy = today.getFullYear();
    if (dd < 10) { dd = '0' + dd }
    if (mm < 10) { mm = '0' + mm }
    sessionStorage.today_date = mm + '/' + dd + '/' + yyyy;
    //get all media data
    var async = false;
    var currentdate = new Date();
    $scope.selectedYear = currentdate.getFullYear();

    apiService.get('/get_all_media_data', {})
    .then(function (response) {
        // Store response data
        var data = response.data;

        var sd = data.last_week.sd;
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec"];
       
        var ed = data.last_week.ed;
        var end_date = new Date(ed.replace(" ", "T"));
        var year = end_date.getUTCFullYear();
        $rootScope.year = year;
        $scope.selectedYear =  $scope.current_year = data.current_year;

        //life time
        sessionStorage.lifetime_year = data.lifetime.year;
        sessionStorage.lifetime_min_sd = data.lifetime.start_date;
        sessionStorage.lifetime_max_ed = data.lifetime.end_date;
        sessionStorage.lifetime_db_min_sd = data.lifetime.start_date_db;
        sessionStorage.lifetime_db_max_ed = data.lifetime.end_date_db;

        //last week
        sessionStorage.media_start_date = data.last_week.start_date;
        sessionStorage.media_end_date = data.last_week.end_date;
        sessionStorage.media_start_db = data.last_week.sd;
        sessionStorage.media_end_db = data.last_week.ed;
        sessionStorage.week_calendar_id = data.last_week.calendar_id;

       //last month
        sessionStorage.media_month_date = data.last_month.start_date;
        sessionStorage.media_monthend_date = data.last_month.end_date;
        sessionStorage.media_month_start_db = data.last_month.sd;
        sessionStorage.media_month_end_db = data.last_month.ed;
        sessionStorage.month_calendar_id = "(" + data.last_month.calendar_id + ")";

        //Current week data
        sessionStorage.current_start_date = data.current_week.start_date;
        sessionStorage.current_end_date = data.current_week.end_date;
        sessionStorage.current_calendar_id = data.current_week.calendar_id;
        $scope.current_week = data.current_week.calendar_id;

        //Current Month Data
        sessionStorage.media_currentmonth_date = data.current_month.start_date;
        sessionStorage.media_currentmonthend_date = data.current_month.end_date;
        sessionStorage.current_start_db = data.current_week.sd;
        sessionStorage.current_end_db = data.current_week.ed;
        sessionStorage.currentmonth_calendar_id = "(" + data.current_month.calendar_id + ")";
        $scope.current_month = data.current_month.media_month_id;
        sessionStorage.media_currentmonth_start_db = data.current_month.sd;
        sessionStorage.media_currentmonth_end_db = data.current_month.ed;

        //Last Quarter data
        sessionStorage.number_of_quarter = data.lst_quarter_no;
        sessionStorage.last_quarter_start_date = data.last_quarter[1];
        sessionStorage.last_quarter_end_date = data.last_quarter[3];
        sessionStorage.last_quarter_db_start_date = data.last_quarter[0];
        sessionStorage.last_quarter_db_end_date = data.last_quarter[2];

        //Current quarter data
        sessionStorage.number_of_currentquarter = data.quarter_no;
        sessionStorage.current_quarter_start_date = data.quarter[1];
        sessionStorage.current_quarter_end_date = data.quarter[3];
        sessionStorage.current_quarter_db_start_date = data.quarter[0];
        sessionStorage.current_quarter_db_end_date = data.quarter[2];
        $scope.current_qtr = data.quarter_no;
        $scope.years = data.years;
        $scope.createYearsArray();
     });

    $scope.click = function() {
        $scope.showme = true;
    }
    $scope.changeThemeStatus = function (item) {
        item.theme_val = item.theme_val == '0' ? '1' : '0'
        $rootScope.theme_val = item.theme_val;
        var theme;	
        if($rootScope.theme_val == 1) {	
            item.src = './assets/images/menuiconwhite/menuiconset-11.svg';
            themeName = 'black';	
        } else {
            item.src = './assets/images/menuiconblue/menuiconset-11.svg';
            themeName = 'original';	
        }	
       
        apiService.post('/update_user', {'theme': item.theme_val})	
        .then(function (data) {	
            if (data.status) {	
                // console.log(data);	
            }	
        })	
        ,(function (data, status, headers, config) {	
            // Error	
        });	
    }


    /* Primary filter*/
    $scope.editableContent = function() {
        $scope.editable = 1;
    }
    
    $scope.cancelFilter = function() {
        $scope.editable = 0;
    }


 
     /* Primary filter*/

    
    $rootScope.menuItemClick = function (item) {
        var page = item.aid;

        if(page == 'network_list'){
            $scope.create_network_pdf_page();
        }

        if(page == 'user_account'){
           $state.go('userAccount')
        }

        if(page == 'blog_status'){
            window.open($rootScope.adsphere_blog_url, '_blank'); 
            $scope.changeBlogStatus(this);
        }

        if(page == 'user_guide'){
            window.open(item.href, '_blank'); 
        }

        if(page == 'log_out'){
            $scope.logout();
        }

        if(page == 'sys_status') {
            window.open($rootScope.system_status_url, '_blank'); 
            $scope.changeSystemStatus(this);
        }

        if(page== 'app_theme'){
            $scope.changeThemeStatus(item); // 0 -> Off -> normal mode
        }

        if(page== 'reports'){
            $scope.reportsModal('reports'); // 0 -> Off -> normal mode
        }
    }

    $scope.create_network_pdf_page = function() {
        window.open('./api/index.php/create_network_pdf', '_blank'); // in new tab
    }

    $scope.changeBlogStatus = function () {
        var notifBlogStatusLink = $rootScope.adsphere_blog_url;
        if(notifBlogStatusLink) localStorage.notifBlogStatusLink = notifBlogStatusLink;
        apiService.post('/update_user', {'notifBlogStatusLink': notifBlogStatusLink})
        .then(function (data) {
            if (data.status) {
            }
        })
        , (function (data, status, headers, config) {
            // Error
        });
    }

    
    $scope.changeSystemStatus = function (orgObj) {
        var notifSystemStatusLink = $rootScope.system_status_url;
        if(notifSystemStatusLink) localStorage.notifSystemStatusLink = notifSystemStatusLink;
        apiService.post('/update_user', {'notifSystemStatusLink': notifSystemStatusLink})
        .then(function (data) {
            if (data.status) {
                // console.log(data);
            }
        })
        ,(function (data, status, headers, config) {
            // Error
        });
    }

    $scope.logout = function () {
        apiService.post('/user_logout', $scope.user)
            .then(function (data) {
                if (data.status) {
                    sessionStorage.clear();
                    localStorage.clear();
                    $state.go('home');
                }
                $state.go('home');
            })
            ,(function (data, status, headers, config) {
            })
    }


    $scope.init1 = function (data) {
        if(data.theme) {
            $rootScope.theme_val = data.theme;
            let obj = $rootScope.right_menu.find(obj => obj.aid == 'app_theme');
            obj.theme_val = data.theme;
            if($rootScope.theme_val == 1) {	
                obj.src = './assets/images/menuiconwhite/menuiconset-11.svg'; // dark theme on
            } else {
                obj.src = './assets/images/menuiconblue/menuiconset-11.svg'; // dark theme off
            }	
       
            // angular.element(document.querySelector('#app_theme'));
        }
        if(data.role == 'superadmin') {
            var notifBlogStatusLink = $scope.adsphere_blog_url;
            var notifSystemStatusLink = $scope.system_status_url;
            var lastIndexOfQue = notifBlogStatusLink.lastIndexOf('?');
            if(lastIndexOfQue != -1) {
                blogStatus = 'black';
                $('#blog_admin_status').find('.activate').show();
                $('#blog_admin_status').find('.deactivate').hide();
            }

            var lastIndexOfQue = notifSystemStatusLink.lastIndexOf('?');
            if(lastIndexOfQue != -1) {
                systemStatus = 'black';
                $('#system_admin_status').find('.activate').show();
                $('#system_admin_status').find('.deactivate').hide();
            }
            return;
        }

        // var sysStatusLink = angular.element(document.getElementById('sys_status'));
        var notificationUL      = angular.element(document.querySelector('.whatsnew-ul'));
        var notificationNewEl   = angular.element(document.querySelector('notification-new'));

        var notificationNewCount = notificationNewEl.text();
        var notificationBuildLink = $rootScope.whatsNew_menu[1].href;
        // var notificationBuildLink = $(notificationUL.children().eq(1)).attr('href');
        var notifBlogStatusLink = $scope.adsphere_blog_url;
        var notifSystemStatusLink = $scope.system_status_url;

        if(typeof(notifBlogStatusLink) != 'undefined' && notifBlogStatusLink.indexOf('?') != -1 ) $('.ads-blog-status').show();
        if(localStorage.notifBlogStatusLink) {
            if(localStorage.notifBlogStatusLink == notifBlogStatusLink) {
                // $('#ads-blog-status').remove();
            } else {
                delete localStorage.notifBlogStatusLink;
            }
        } else {
            delete localStorage.notifBlogStatusLink;
        }

        if(typeof(notifSystemStatusLink) != 'undefined' && notifSystemStatusLink.indexOf('?') != -1) $('.system-status').show();
        if(localStorage.notifSystemStatusLink) {
            if(localStorage.notifSystemStatusLink == notifSystemStatusLink) {
                // $('#system-status').remove();
            } else {
                delete localStorage.notifSystemStatusLink;
            }
        } else {
            delete localStorage.notifSystemStatusLink;
        }

        if(localStorage.notificationBuildLink) {
            if(localStorage.notificationBuildLink != notificationBuildLink) {
                delete localStorage.notificationNewCount;
                delete localStorage.notificationBuildLink;
                delete localStorage.notificationNewLiClicked;
            }
        } else {
            delete localStorage.notificationNewCount;
            delete localStorage.notificationBuildLink;
            delete localStorage.notificationNewLiClicked;
        }

        if(notificationNewCount) {
            if(localStorage.notificationNewCount) {
                if( localStorage.notificationNewCount == "0" ) {
                    notificationNewEl.remove();
                } else {
                    notificationNewEl.text( localStorage.notificationNewCount );
                }
            } else {
                localStorage.notificationNewCount = notificationNewCount;
            }
        } else {
            delete localStorage.notificationNewCount;
        }

        if(localStorage.notificationNewLiClicked) {
            var firstLIsIgnored = 1;
            var liArr = localStorage.notificationNewLiClicked.split(',');
            for(i=0; i<liArr.length; i++) {
                // $(notificationUL.children().eq( parseInt(liArr[i]) + firstLIsIgnored ).children()).addClass('watsnew-visited');
            }
        }

        apiService.post('/update_user', {'notificationBuildLink': notificationBuildLink})
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    // console.log(data);
                }
            })
            ,(function (data, status, headers, config) {
                // Error
            })
    }

    $scope.init = function () {
        $rootScope.catgeorySidebuar = 0; // hide category section on load
        delete localStorage.notificationNewCount;
        delete localStorage.notificationNewLiClicked;
        delete localStorage.notificationBuildLink;
        delete localStorage.notifSystemStatusLink;
        delete localStorage.notifBlogStatusLink;
        if(!sessionStorage.loggedIn) return;

        apiService.post('/get_user', $scope.user)
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    if(data.notification_new_count && ((data.notification_new_count == "0" && data.notification_new_clicked != '') || data.notification_new_count != "0")) {
                        localStorage.notificationNewCount = data.notification_new_count;
                    }
                    if(data.notification_new_clicked) {
                        localStorage.notificationNewLiClicked = data.notification_new_clicked;
                    }
                    if(data.notification_build_url) {
                        localStorage.notificationBuildLink = data.notification_build_url;
                    }
                    if(data.system_status_url) {
                        localStorage.notifSystemStatusLink = data.system_status_url;
                    }
                    if(data.adsphere_blog_url) {
                        localStorage.notifBlogStatusLink = data.adsphere_blog_url;
                    }
                    $scope.init1(data);
                    // $('.system-status,.ads-blog-status').show();
                }
            })
            ,(function (data, status, headers, config) {
                // Error
            })
    }

    $scope.showTab = function(tab) {
        $rootScope.type = tab;
    }

    /** Filters code -- Start */
    $scope.call_filter_list = function () {
        $scope.getAllFilters();
        $scope.getActiveSharedUsers('filters');
        $scope.openFilterModal();
    }

    $scope.openFilterModal = function() {
        $scope.modalInstance =  $uibModal.open({
          templateUrl: "./templates/modals/FilterDialog.html",
          controller: "FiltersCtrl",
          size: 'lg modal-dialog-centered',
        });
      };

  

    $scope.getAllFilters = function () {
        var page = $state.current.name;
        apiService.post('/get_all_filter_data',{'page': page})
        .then(function (response) {
          var data = response.data;
            if (data.status) {
                $rootScope.cachedFilterReportsData = data.return_arr;
            }
        }
        , function (response) {
          // this function handles error
          }); 
    }

   
    
     /** Filters code -- End */
    $scope.getActiveSharedUsers = function (page) {
        apiService.post('/show_active_shared_users', { 'page': page })
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    $rootScope.users = data.result;
                    $rootScope.users_count = data.count;
                }
            })
            ,(function (data, status, headers, config) {
                console.log("error inside");
            });
    }

    /***List code starts */

    $scope.getAllList = function () {
        var page = $state.current.name;
        var list_tab = $rootScope.list_tab.substr(0, ($rootScope.list_tab.length - 1));
        apiService.post('/get_all_list_data',{  "primary_tab": list_tab })
        .then(function (response) {
          var data = response.data;
            if (data.status) {
                $rootScope.cachedListsData = data.return_arr;
            }
        }
        , function (response) {
          // this function handles error
          }); 
    }

    $scope.openListModal = function() {
        $scope.modalInstance =  $uibModal.open({
            templateUrl: "./templates/modals/ListDialog.html",
            controller: "ListsCtrl",
            size: 'lg modal-dialog-centered',
          });
    }

    $scope.reportsModal = function(){
        $scope.modalInstance =  $uibModal.open({
            templateUrl: "./templates/modals/reportsdialog.html",
            controller: "ReportsModalCtrl",
            size: 'lg modal-dialog-centered',
        });
    }

    $scope.call_brand_tab_list = function(list_item) {
        // $.ajax({
        //     url: "/drmetrix/assets/js/jquery.dropdown.js?"+Math.random(),
        //     dataType: "script",
        //     cache: true,
        //     success: function() {
        //     }
        // });
        // $.ajax({
        //     url: '/drmetrix/assets/css/jquery.dropdown.css?'+Math.random(),
        //     dataType: 'text',
        //     success: function(data) {
        //         $('<style type="text/css">\n' + data + '</style>').appendTo("head");
        //     }
        // });
        $rootScope.my_list = list_item[0].toUpperCase() + list_item.slice(1);
        $rootScope.list_tab = $scope.type;
        $scope.getAllList();
        $scope.choose_list = true;
        $scope.getActiveSharedUsers('list');
        $scope.openListModal();
    }
    /***List code Ends */

    $scope.$watch('globalSearchInputText', function(nVal, oVal) {
        if (nVal !== oVal) {
            // $scope.global_search_ajax(nVal);
        }
    });

});

angular.module('drmApp').controller('FiltersCtrl', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
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
        };

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
            $scope.PostDataResponse = formData;
            vm.gridOptions.data = data.data.rows;
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.uigridFilterModal();
});


angular.module('drmApp').controller('ListsCtrl', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.sharedList = 'My';
    $scope.selected_user = '';
   
    
    $scope.show_user_list = function () {
        //ui grid code
        $scope.uigridBrandListModdal();
    }

    // $scope.show_user_list();
    
    $scope.showSharedLists = function(item) {
        $scope.sharedList = item;
            //with ui grid code, displayes grid data according to rules set
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    // Call brand List ui Grid
    $scope.uigridBrandListModdal = function() {
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
        formData.sidx = "created_date";
        formData.sord = 'desc';

        vm.gridOptionsList = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
        };

        vm.gridOptionsList.columnDefs = [
            { name: 'full_name', pinnedLeft:true, displayName:'User'},
            { name: 'name', pinnedLeft:true, displayName:'List Name'},
            { name: 'created_date', pinnedLeft:true, displayName:'Created On' },
            { name: 'shared_list_date', pinnedLeft:true, displayName:'Shared On' },

            { name: 'copy_list', pinnedLeft:true, displayName: 'Copy To My List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="copy_list checkbox-custom" id="copy_list_\'{{row.entity.id}}\'" name="copy_list" ng-click="copySharedList({{row.entity.id}})"  {{row.entity.checked_copy_list}} {{row.entity.disable_copy_list}} /><label for="copy_filter_{{row.entity.id}}" class="checkbox-custom-label {{row.entity.disabled_copy_list_class}}"></label></li></ul></nav>'},

            { name: 'shared_list', displayName: 'Share List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="share_filter checkbox-custom" id="share_list_\'{{row.entity.id}}\'" name="share_list" ng-click="updateShareListStatus(\'{{row.entity.id}}\')"  \'{{row.entity.checked_shared_list}}\' \'{{row.entity.disabled_shared_list}}\' /><label for="share_filter_\'{{row.entity.id}}\'" class="checkbox-custom-label \'{{row.entity.disabled_class}}\'"></label></li></ul></nav>'},

            { name: 'criteria_name', pinnedLeft:true, displayName:'Detail', cellTemplate:'<span title="{{COL_FIELD}}">{{row.entity.criteria_name == \'\' ? \'-\' : row.entity.criteria_name | limitTo: 60}}</span>' },

            { name: 'edit_list', pinnedLeft:true, displayName:'Edit', cellTemplate: '<span class="edit-list_\'{{row.entity.id}}\' dropdown-list edit-list-icon" id="edit_list_\'{{row.entity.id}}\'"  ng-click="edit_user_filter(\'{{row.entity.id}}\',\'{{row.entity.edit_list}}\');"  class="edit-list"><i class="fa fa-pencil" aria-hidden="true"></i></span><span class="edit-list-loader_\'{{row.entity.id}}\' edit-list-loader" id="excel_loader_\'{{row.entity.id}}\'"><img src="/drmetrix/assets/img/excel_spinner.gif" alt="Loading icon"></span>' },

            { name: 'apply', pinnedLeft:true, displayName:'Apply', cellTemplate: '<a href="javascript:void(0)" ng-click="apply_user_list(\'{{row.entity.id}}\', \'{{row.entity.apply}}\');" id="apply_filter_{{row.entity.id}}">Apply</a>' },
        ];
        apiService.post('/get_user_lists', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptionsList.data = data.data.rows;
        }, function (response) {
            // this function handlers error
        });
    }
});

angular.module('drmApp').controller('ReportsModalCtrl', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.sharedList = 'My';
    $scope.selected_user = '';

    var user_id = sessionStorage.loggedInUserId;
    var sharded_by = sessionStorage.loggedInUserId;

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
        };

        vm.gridOptionsReports.columnDefs = [
            { name: 'file_name', pinnedLeft:true, displayName:'File Name'},
            { name: 'filesize', pinnedLeft:true, displayName:'File Size'},
            { name: 'download_link', pinnedLeft:true, displayName:'Download Link' },
            { name: 'email_alert', pinnedLeft:true, displayName:'Email Alert' },

            { name: 'shared_report', displayName: 'Shared Report', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="share_filter checkbox-custom" id="share_list_\'{{row.entity.id}}\'" name="share_list" ng-click="updateShareListStatus(\'{{row.entity.id}}\')"  \'{{row.entity.checked_shared_list}}\' \'{{row.entity.disabled_shared_list}}\' /><label for="share_filter_\'{{row.entity.id}}\'" class="checkbox-custom-label \'{{row.entity.disabled_class}}\'"></label></li></ul></nav>'},

            { name: 'copy_list', pinnedLeft:true, displayName: 'Copy To My List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="copy_list checkbox-custom" id="copy_list_\'{{row.entity.id}}\'" name="copy_list" ng-click="copySharedList({{row.entity.id}})"  {{row.entity.checked_copy_list}} {{row.entity.disable_copy_list}} /><label for="copy_filter_{{row.entity.id}}" class="checkbox-custom-label {{row.entity.disabled_copy_list_class}}"></label></li></ul></nav>'},
        ];
        apiService.post('/get_my_reports_data', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptionsReports.data = data.data.rows;
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.show_reports_modal(user_id, sharded_by);
});