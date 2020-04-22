angular.module('drmApp').controller('MainController', function ($scope, $http, $state, $stateParams, $rootScope, apiService, $location, $uibModal, modalConfirmService, $timeout) {
    $scope.date = new Date(); // Footer copyright display year
    $rootScope.eulaDisagreeFlag = 0; // this flag will show poup on login page if we disagree eula agreement and redreict to login message with popup message
    $scope.whats_new_toggle = false;
    $rootScope.headerDisplay = 0;
    $scope.selectedNetwork = '';
    $scope.programs_id = ''
    /* Primary filter */
    $rootScope.complete_name = localStorage.complete_name;
    $scope.selectDate = sessionStorage.selectDate = 1;
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

$scope.tracking_frequency = [   
    {
        "index": 1,
        "id": "daily",
        'selected': false,
        "value": "Daily"
    }, 
    {
        "index": 2,
        "id": "weekly",
        'selected': false,
        "value": "Wekly"
    }, 
    // {
    //     "index": 3,
    //     "id": "monthly",
    //     'selected': false,
    //     "value": "Monthly"
    // }
]
$scope.shortFormTrackingClassification = [
        {
            "index": 1,
            "id": "short_form_products",
            'selected': false,
            "value": "Short Form Products"
        }, {
            "index": 2,
            "id": "lead_generation",
            'selected': false,
            "value": "Lead Generation"
        }, {
            "index": 3,
            "id": "brand_direct",
            'selected': false,
            "value": "Brand/DR",
        }, {
            "index": 4,
            "id": "285_mins",
            'selected': false,
            "value": "28.5 Mins",
            "display_text": "28.5 Mins",
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

    
     //breaktypes
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
    $scope.response_header = 'or';
    $scope.creative_type = 'short';
    $scope.checkedShortClassification = [1, 2, 3, 4, 5];
    $scope.checkedLongClassification = [6];
    $scope.selectedDurations = [10, 15, 20, 30, 45, 60, 75, 90, 105, 120, 180, 240, 300];
    $scope.creative_short_duration = [10, 15, 20, 30, 45, 60, 75, 90, 105, 120, 180, 240, 300];
    $rootScope.active_flag = 2 // keep it until not integrated active inactive all

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

    $scope.verifyDuplicateMobile = function (mobile) {
        var user_id = $('#edit_data_user_id').val();
        var admin_id = sessionStorage.admin_id;
        var hidden_mobile_no = $("#mobile_edit_hidden").val();
        if ($('[id="advancedModalEdit"]').hasClass('is-active')) {
            hidden_mobile_no = $("#mobile_edit_company_hidden").val();
        }

        if (sessionStorage.role == 'superadmin') {
            admin_id = $('#edit_company_admin_id').val();
            if (admin_id == '') {
                admin_id = $('#edit_company_page_admin_id').val();
            }
            if ($('#admin_id').val() != '') {
                admin_id = $('#admin_id').val();
            }
        }

        apiService.post('/check_mobile', { 'mobile': mobile, 'admin_id': admin_id, 'user_id': user_id, 'hidden_mobile_no': hidden_mobile_no })
            .then(function (response) {
                let data = response.data;
                if (data.status) {
                    if (data.valid) {
                        $rootScope.errors = 1;
                        $rootScope.mobileValid = 1; //mobile invalid
                        $('#duplicate_mobile').css('display', 'block');
                    } else {
                        $rootScope.errors = 0;
                        $rootScope.mobileValid = 0; //mobile valid
                    }
                }
            }, function (response){
                // this function handlers error
            });
    }

    $scope.check_owner = function(id) {
        var account_owner = $('#' + id).val();
        if (account_owner.length == 0) {
            $('#err_' + id).show();
        } else {
            $('#err_' + id).hide();
        }
    }
    // validate mobile
    $scope.validate_mobile = function(id, v, e) {
        $('#add_mobile').hide();
        $('#edit_mobile').hide();
        $('#authy_add_mobile').hide();
        $('#authy_edit_mobile').hide();
        $('#add_mobile_add_user').hide();
        $('#authy_add_mobile_add_user').hide();

        v = v
            .match(/\d*/g).join('')
            .match(/(\d{0,3})(\d{0,3})(\d{0,12})/).slice(1).join('-')
            .replace(/-*$/g, '');

        $('#' + id).val(v);
    }

    $scope.check_owner = function (id) {
        var account_owner = $('#' + id).val();
        if (account_owner.length == 0) {
            $('#err_' + id).show();
        } else {
            $('#err_' + id).hide();
        }
    }

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
            $scope.sd = sessionStorage.media_start_db;
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
    $scope.date_filter($scope.selectDate);


    $scope.openModal = function(templateUrl, controller, size, backdrop) {
        $scope.modalInstanceMain =  modalConfirmService.showModal({
            backdrop: false,
            keyboard: true,
            modalFade: true,
            templateUrl: templateUrl,
            controller: controller,
            scope: $scope,
            size: size ? size : 'md modal-dialog-centered',
          });

          $scope.modalInstanceMain.result.then(function(response){
              $scope.result = `${response} button hitted`;
          });

          $scope.modalInstanceMain.result.catch(function error(error) {
            if(error === "backdrop click") {
              // do nothing
            } else {
              // throw error;
            }
          });
    };
    $scope.getParameters = function () {
        var selectDateDropDown = $scope.selectDate;

        if (selectDateDropDown == 1) {
            sd = $scope.media_start_db;
            ed = $scope.media_end_db;
        }

        if (selectDateDropDown == 2) {
            sd = $scope.current_start_db;
            ed = $scope.current_end_db;
        }

        if (selectDateDropDown == 3) {
            sd = $scope.last_quarter_db_start_date;
            ed = $scope.last_quarter_db_end_date;
        }

        if (selectDateDropDown == 4) {
            sd = $scope.last_year_db_start_date;
            ed = $scope.last_year_db_end_date;
        }

        if (selectDateDropDown == 5) {
            sd = $scope.lifetime_db_min_sd;
            ed = $scope.lifetime_db_max_ed;
        }

        if (selectDateDropDown == 6) {
            sd = $scope.media_start_db;
            ed = $scope.media_end_db;
        }

        if (selectDateDropDown == 7) {
            sd = $scope.media_month_start_db;
            ed = $scope.media_month_end_db;
        }

        if (selectDateDropDown == 8) {
            sd = $scope.last_quarter_db_start_date;
            ed = $scope.last_quarter_db_end_date;
        }

        if (selectDateDropDown == 9) {
            sd = $scope.current_start_db;
            ed = $scope.current_end_db;
        }

        if (selectDateDropDown == 10) {
            sd = $scope.media_currentmonth_start_db;
            ed = $scope.media_currentmonth_end_db;
        }

        if (selectDateDropDown == 11) {
            sd = $scope.current_quarter_db_start_date;
            ed = $scope.current_quarter_db_end_date;
        }

        if (selectDateDropDown.toString().indexOf('month32_') != -1) {
            var custom_date = selectDateDropDown.split("_");
            if (custom_date.length > 1) {
                sd = custom_date[2];
                ed = custom_date[3];
                selectDateDropDown = 2;
                data['selectDateDropDown'] = selectDateDropDown;
            }
        }

        if (selectDateDropDown.toString().indexOf('week31_') != -1) {
            var custom_date = selectDateDropDown.split("_");
            if (custom_date.length > 1) {
                sd = custom_date[2];
                ed = custom_date[3];
                selectDateDropDown = 1;
                data['selectDateDropDown'] = selectDateDropDown;
            }
        }

        if (selectDateDropDown.toString().indexOf('quarter33_') != -1) {
            var custom_date = selectDateDropDown.split("_");
            if (custom_date.length > 1) {
                sd = custom_date[2];
                ed = custom_date[3];
                selectDateDropDown = '3_' + custom_date[1] + '_' + ed;
                data['selectDateDropDown'] = selectDateDropDown;
            }
        }

        if (selectDateDropDown.toString().indexOf('year34_') != -1) {
            var custom_date = selectDateDropDown.split("_");
            if (custom_date.length > 1) {
                sd = custom_date[2];
                ed = custom_date[3];
                selectDateDropDown = '4_' + custom_date[1] + '_' + ed;
                data['selectDateDropDown'] = selectDateDropDown;
            }
        }

        $scope.sd = sd;
        $scope.ed = ed;
        // if (sessionStorage.selectDate == 'calender') {
        //     data['sd'] = airings_data['sd'] = sessionStorage.start_date;
        //     data['ed'] = airings_data['ed'] = sessionStorage.end_date;
        // } else {
        //     data['sd'] = airings_data['sd'] = sd;
        //     data['ed'] = airings_data['ed'] = ed;
        // }

    }

    $rootScope.$on("CallParentMethod", function(evt, data){
        $rootScope.headerDisplay = 1;
        $scope.selectedNetwork      = data.network_id;
        $scope.selectedNetworkAlias = data.network_alias;
        $scope.newType              = data.newType;
        $scope.newCheckBox          = data.newCheckBox;
        $scope.refine_by            = data.refine_by;
        $scope.search_by_tfn        = data.search_by_tfn;
        $scope.programs_id          = data.program_id;
        $scope.applyFilter();
     });

     $scope.hideTrackingModal = function() {
        $timeout(function () {  
            $scope.success_alert_setup_msg = $scope.error_alert_setup_msg = '';
            $scope.modalInstanceMain.close(); 
        }, 2000);
       
     }

     $scope.viewTrackingDialogue = function(alert_type, type_id, name) {
         $scope.alert_type = alert_type;
         var type_id, name;
         $scope.tracking_action = '';
         $scope.isAdvSelected = $scope.isBrandSelected = $scope.isCreativeSelected = false;
         $scope.track_advertiser = $scope.track_brand = $scope.track_creative = 0 
         if(alert_type == 'network') {
             type_id = $scope.selectedNetwork;
             name    = $scope.selectedNetworkAlias
         }
        $scope.type_id               = type_id;
        $scope.tracker_element_name  = name;
        var formData =  {'alert_type' : alert_type, 'type_id' : type_id, 'name' : name }
        apiService.post('/get_tracking_detail', formData)
        .then(function (data) {
            var response = data.data
            switch (alert_type) {
                case 'advertiser':
                    $scope.track_brand = $scope.track_cretive = 1;
                    break;
                case 'brand':
                    $scope.track_creative = 1;
                    break;
                case 'category':
                    $scope.track_brand = $scope.track_creative = 1;
                    break;
                case 'network':
                    $scope.track_advertiser = $scope.track_brand = $scope.track_creative=  1;
                    break;
            }
            if (response.status == 1) {
                if (response.data['frequency'] != "") {
                    angular.forEach($scope.tracking_frequency, function(tracking) {
                        if(response.data['frequency'].includes(tracking.id)) {
                            tracking.selected = true;
                        }
                    });
                }
                if ($state.current.name == 'tracking') { // configure email page
                    $scope.status           = response.data['status'] == 'active' ? 'inactive' : 'active';
                    $scope.tracking_action  = '<a ng-click="inactiveTracking();"><i ng-class="{status == "inactive" ?"blue-eye" : "slash grey-eye"}" class="fa fa-eye" title="Track"></i></a>';
                } 
                var elements = response.data['track_elements'].split(",");
                elements.forEach(function (element) {
                    if (element == 'advertiser') {
                        $scope.isAdvSelected = true;
                    }
                    if (element == 'brand') {
                        $scope.isBrandSelected = true;
                    }
                    if (element == 'creative') {
                        $scope.isCreativeSelected = true;
                    }
                });
                if (alert_type == 'network' || alert_type == 'category') {
                    var classification = response.data['classification'];
                    if (classification != "") {
                        angular.forEach(classification, function (element) {
                            let obj = $scope.shortFormTrackingClassification.find(obj => obj.id == element);
                            obj.selected = true;
                        }); 
                    }
                }
            }
            $scope.openModal('./templates/modals/trackModalDialog.html','trackCtrl','md modal-dialog-centered');
        },function (error) {
            console.log('Error');
        });
       
    }

    $scope.removeLastCommaFromString = function(text) {
        var lastChar = text.slice(-1);
        if (lastChar == ',') {
           text = text.slice(0, -1);
        }
         return text;
    }

    $scope.setTracking = function() {
        // setAlertClose(); // to hide or show mesage depends on email schedualabel. Do it later
        var alert_type      = $scope.alert_type;
        var type_id         = $scope.type_id;
        var name            = $scope.tracker_element_name;
        var status = "active";
        var elements = frequency = brand_class = "";
        if ($scope.isAdvSelected) {
            elements += "advertiser,";
        }
        if ($scope.isBrandSelected) {
            elements += "brand,";
        }
        if ($scope.isCreativeSelected) {
            elements += "creative";
        }

        elements = $scope.removeLastCommaFromString(elements);

        if (alert_type != 'filter' && elements == "") {
            $scope.error_alert_setup_msg = '<span>At least one type should be tracked.</span>';
            $timeout(function () { $scope.error_alert_setup_msg = ''; }, 2000);
            return false;
        }

        angular.forEach($scope.tracking_frequency, function(element) {
            if(element.selected) {
                frequency += element.id+',';
            }
        });
        
        frequency = $scope.removeLastCommaFromString(frequency);

        if (frequency == "") {
            $scope.error_alert_setup_msg ='<span>At least one frequency type should be select.</span>';
            $timeout(function () { $scope.error_alert_setup_msg = ''; }, 2000);
            return false;
        }

        if (alert_type == 'network' || alert_type == 'category') {
            angular.forEach($scope.shortFormTrackingClassification, function(element) {
                if(element.selected) {
                    brand_class += element.id+',';
                }
            });
        } else {
            brand_class = "NA";
        }

        brand_class = $scope.removeLastCommaFromString(brand_class);

        var formData = { "alert_type": alert_type, "type_id": type_id, "frequency": frequency, "tracked_elements": elements, "name": name, "status": status, "brand_class": brand_class
        }
        apiService.post('/set_tracking_detail', formData)
            .then(function (data) {
                var response = data.data
                if (response.status == true) {
                   $scope.success_alert_setup_msg ='<span>Alert tracking is set up successfully.</span>';
                    // var custom_attr_id = alert_type + "_" + type_id;
                    // $('[custom-attr="' + custom_attr_id + '"]').addClass("fa-eye");
                    // $('[custom-attr="' + custom_attr_id + '"]').addClass("blue-eye");
                    // $('[custom-attr="' + custom_attr_id + '"]').removeClass("grey-eye");
                    // $('[custom-attr="' + custom_attr_id + '"]').removeClass("fa-eye-slash");

                    // $('[custom-attr="config_alert_frequency_' + type_id + '"]').html(ucfirst(frequency));

                    // if (alert_type == 'advertiser') {
                    //     $("#adv_track_btn").removeClass('gray-button');
                    //     $("#adv_track_btn_zd").removeClass('gray-button');
                    // }
                    if (alert_type == 'network') {
                        $scope.tracking_on = (status == 'active')  ? 1 : 0;
                    }
                    $scope.hideTrackingModal();
                    if ($state.current.name == 'tracking') {
                        if (status == 'active') {
                            $('[custom-attr="config_alert_' + custom_attr_id + '"]').addClass("fa-eye blue-eye");
                            $('[custom-attr="config_alert_' + custom_attr_id + '"]').removeClass("grey-eye fa-eye-slash");
                        } else {
                            $('[custom-attr="config_alert_' + custom_attr_id + '"]').removeClass("fa-eye blue-eye");
                            $('[custom-attr="config_alert_' + custom_attr_id + '"]').addClass("grey-eye fa-eye-slash");
                        }
                    }
                    // if (alert_type == 'network' || alert_type == 'category') {
                    //     updated_class = updated_class.replace(/,\s*$/, "");
                    //     $('[custom-attr="config_alert_classification_' + type_id + '"]').html(updated_class);
                    // }
                }
            }, function ( error) {
               $scope.error_alert_setup_msg = '<span>Error while setting up Alert tracking.</span>';
               $scope.hideTrackingModal();
            });
    }

    $scope.applyFilter = function() {
        $scope.getParameters();
        $scope.categories_selected  = $scope.getSelectedCategories();
        $scope.classification       = $scope.getSelectedClassification();
        $scope.tab                  = $scope.type == 'brands' ? 1 : 0; 
        $rootScope.formdata         = {'cat' : $scope.categories_selected , 'startDate' : $scope.selectDate,  'val' : $scope.selectDate,  'sd' : $scope.sd, 'ed' : $scope.ed, 'c' : $scope.selectClassificationValues , 'spanish' : $scope.selectLang, 'responseType': $scope.returnText , 'type' : $scope.tab , 'creative_duration' : $scope.selectedDurations.join(), 'flag': $rootScope.active_flag,"refine_filter_opt": $scope.refineBy,"refine_filter_opt_text":$scope.search_by_tfn,"refine_apply_filter":0,"new_filter_opt":$scope.newType ,'network_id' : $scope.selectedNetwork ,'network_alias' : $scope.selectedNetworkAlias, 'refine_by' : $scope.refine_by, 'search_by_tfn' : $scope.search_by_tfn, 'programs_id' : $scope.programs_id}

        if (!angular.isUndefined($scope.selectedNetwork) && $scope.selectedNetwork != '') {
            apiService.post('/get_network_tracking_status', $rootScope.formdata )
            .then(function (response) {
                    var response = response.data;
                    if (response.status) {
                        $scope.tracking_on = 1;
                    } else {
                        $scope.tracking_on = 0;
                    }
                }, function (res) {

                });
        }

        if($state.current.name == 'ranking') {
            if (angular.isUndefined($scope.refine_by)) {
                ($rootScope.type == 'brands') ? $rootScope.uigridDataBrand() : $rootScope.uigridDataAdv();
            } else {
                console.log('refine');
                // $rootScope.uigridRefineData();
            }
        } else {
            //network page grid code
        }
        
        // ($rootScope.type == 'brands') ? $scope.uigridDataBrand() : $scope.uigridDataAdv();
        // $rootScope.formdata =  {"sd":"2020-02-24","ed":"2020-03-01","startDate":1,"val":1,"c":1,"type":1,"cat":"all","flag":2,"spanish":"0,1","responseType":"(response_url = 1 or response_mar = 1 or response_sms = 1 or response_tfn = 1 )","unchecked_category":"","length_unchecked":0,"creative_duration":"10,15,20,30,45,60,75,90,105,120,180,240,300","new_filter_opt":"none","lifetime_flag":false,"all_ytd_flag":false,"refine_filter_opt":"","refine_filter_opt_text":"","refine_apply_filter":0,"applied_ids":"","primary_tab":""};
    }

    $scope.getSelectedClassification = function() {
        if($scope.creative_type == 'short' && $scope.checkedShortClassification.indexOf(1) >= -1) {
            $scope.selectClassificationValues = 1;
        } else {
            $scope.selectClassificationValues = $scope.creative_type == 'short' ? $scope.checkedShortClassification  : $scope.checkedLongClassification;
            $scope.selectClassificationValues = $scope.selectClassificationValues.join();

        }

        return $scope.selectClassificationValues;
    }

    $scope.getSelectedCategories = function() {
        var selected_cateories = [];
        if($scope.allcategory) {
            return 'all'
        } else {
            angular.forEach($rootScope.category_list , function(categories, cat_key) {
                angular.forEach(categories.subcategory , function(subCategories, subcat_key) {
                    if(subCategories.isSelected) {
                        selected_cateories.push(subCategories.sub_category_id)
                    }
                });
            });
            return selected_cateories.join();
        }

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
    $scope.setResponseTypes('or');
        
    $scope.getDisplayDurationText = function () {
        $scope.duration_display_text = ($scope.selectedDurations.length === $scope.creative_short_duration.length) ? ' (All Duration)' : ($scope.selectedDurations.length == 1) ? ' (' + $scope.selectedDurations[0] + 's)' : $scope.selectedDurations.length > 1 ? ' (Multi Duration)' : '';
    }
    $scope.getDisplayDurationText();

    $scope.initializeWeeks = function() {
        if($scope.selectDate == 1 || $scope.selectDate == 2 ) {
            angular.forEach($scope.yearsArray, function(y, key) {
                angular.forEach(y.weeks, function(w, key) {
                    if(key == 0 && y.media_year == $scope.selectedYear) {
                        $scope.selectDate = 'week31_'+w.media_week+'_'+w.media_week_start+'_'+w.media_week_end;
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

        if (sessionStorage.calender_flag == 1) {
            $scope.apply_filter = 0;
            $scope.lifetime_error = 1;
        }
    }

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
        liid: 'tracking',
        nghide: 'superadmin',
        href: 'configureEmails',
        aclass: 'tracking',
        aid: 'tracking',
        title: 'Configure Emails',
        src: './assets/images/menuiconblue/menuiconset-5.svg',
    },];

    $rootScope.whatsNew_menu = [{
        href: 'https://adsphere.drmetrix.com/blog/2020/02/04/new-february-2020-build/',
        title: 'Latest Feature Updates',
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
        aclass: 'log-out d-block',
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
        $scope.mapValueWithSession(displayDateList);
        $scope.mapValueWithSession(databaseFormatDate);
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


    $scope.editableContent = function() {
        $scope.editable = 1;
    }
    
    $scope.cancelFilter = function() {
        $scope.editable = 0;
    }

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

        if(page == 'tracking') {
            $scope.changeSystemStatus(this);
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
        $scope.applyFilter();
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
        $.ajax({
            url: "./assets/js/jquery.dropdown.js?"+Math.random(),
            dataType: "script",
            cache: true,
            success: function() {
            }
        });
        $.ajax({
            url: './assets/css/jquery.dropdown.css?'+Math.random(),
            dataType: 'text',
            success: function(data) {
                $('<style type="text/css">\n' + data + '</style>').appendTo("head");
            }
        });
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


angular.module('drmApp').controller('ListsCtrl', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.sharedList = 'My';
    $scope.selected_user = '';
    $scope.loading = true;
    var airings_data = new Array();
    let cachedBrandListsData = [];
    let cachedAdvListsData = [];
    let list_brand_api = 0;
    let list_adv_api = 0;
    let $dropdown;
   
    
    $scope.showSharedLists = function(item) {
        $scope.sharedList = item;
            //with ui grid code, displayes grid data according to rules set
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
    
    $scope.createDropdown = function(id) {
        if($scope.lists) {
            angular.forEach($scope.lists, function(value, key) {
                if(jQuery.inArray( value.id, $scope.list_id_array) > -1) {
                    value.selected = true;
                } else {
                    value.selected = false;
                }
            });
            $dropdown = $('.dropdown-mul-1').dropdown({
                data: $scope.lists,
                multipleMode: 'label',
                searchTextLengthErrorMessage: '',
                limitCount: 100,
                limitCountErrorMessage: 'There is a 100 limit for '+$scope.ranking.list_tab+'s chosen. You have reached the limit for this list.',
                choice: function () {
                  console.log(arguments);
                },
                input: '<div class="search-input"> <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span><input type="text"  id="search_searchable_dropdown" placeholder="Please enter minimum 3 characters" onKeyUp="removeDisabled()"/><span id="clearIconList" class="search-icon cross-icon" style="display:none;"><i class="fa fa-times-circle" title="Clear Search"></i></span></span></div><button type="button" class="btn btn-blue applyBtn" id="search_list" disabled="disabled"><i class="fa fa-search"></i>Search</button>',
                // <span class="inline-label search-text-tip">Search tip: <span> Enter minimum 3 characters</span></span>
              });
        }
        setTimeout(function () {
            $('#edit_list_'+id).show();
            $('span#excel_loader_'+id).hide();  
        }, 100);
        // $('#edit-list-modal').show();
        $('#edit-list-modal').modal('show');
        $('#edit-list-modal').css("display","flex");
        var scroll=$('.dropdown-mul-1');
        scroll.animate({scrollTop: scroll.prop("scrollHeight")});
    }
        
    $scope.edit_user_filter = function(id, list_ids) {
        console.log($scope.listGridApi.selection.getSelectedRows());
        // angular.forEach(data, function(data, index) {
        //     data["index"] = index+1;
        //     //data.push({"index":index+1})
        // })
        var p = $("#tab_list").jqGrid("getGridParam");
        var iCol = p.iColByName["name"];
        $scope.headerListName =  $("#" + id).find('td').eq(iCol).text();

        if($dropdown)  $dropdown.data('dropdown').destroy();
        $scope.edit_list_id = id; // the list in edit mode
        let list_id_array = list_ids.split(',');
        $scope.list_id_array = list_id_array;
        if($scope.ranking.list_tab == 'brand') {
            list_brand_api = cachedBrandListsData.length == 0 ? 1 : 0;
        }

        if($scope.ranking.list_tab == 'advertiser') {
            list_adv_api = cachedAdvListsData.length == 0 ? 1 : 0;
        }

        if((list_brand_api == 1 || list_adv_api == 1)) {
            setTimeout(function () {
                $('#edit_list_'+id).hide();
                $('#excel_loader_'+id).show();
            }, 0);
            $.ajax({
                type: 'POST',
                url: '/drmetrix/api/index.php/get_all_brands_advertisers',
                // async: false,
                data: {
                    tab : $scope.ranking.list_tab == 'brand' ? 1 : 0
                }, success: function (data) {
                    let response = jQuery.parseJSON(data);
                    let items = [];
                    var selectedEle ;
                    angular.forEach(response.result, function(value, key) {
                        selectedEle = false;
                        if(jQuery.inArray( value.brand_id, list_id_array )) {
                            selectedEle = true;
                        }
                        items.push({
                            'id': value.id, 
                            'disabled': false,
                            'selected':false,
                            'name':  value.name,
                            
                        });
                    });
                    if($scope.ranking.list_tab == 'brand') {
                        cachedBrandListsData = items;
                    } else {
                        cachedAdvListsData = items;
                    }
                    $scope.lists = items;
                    $scope.createDropdown(id);
                }, error: function (xhr, status, error) {

                }
            });
        } else {
            // $.ajax({
            //     type: 'POST',
            // });
            // setTimeout(function () {
                $('#edit_list_'+id).hide();
                $('span#excel_loader_'+id).show();
            // }, 0);
            setTimeout(function () {
                $scope.createDropdown(id);
            }, 100);
        }
    }
    // Call brand List ui Grid
    $scope.uigridListModal = function() {
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
            onRegisterApi: (gridApi) => {
                $scope.listGridApi = gridApi;
            },
        };
        

        vm.gridOptionsList.columnDefs = [
            { name: 'full_name', pinnedLeft:true, displayName:'User'},
            { name: 'name', pinnedLeft:true, displayName:'List Name'},
            { name: 'created_date', pinnedLeft:true, displayName:'Created On' },
            { name: 'shared_list_date', pinnedLeft:true, displayName:'Shared On' },

            { name: 'copy_list', pinnedLeft:true, displayName: 'Copy To My List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="copy_list checkbox-custom" id="copy_list_\'{{row.entity.id}}\'" name="copy_list" ng-click="copySharedList({{row.entity.id}})"  {{row.entity.checked_copy_list}} {{row.entity.disable_copy_list}} /><label for="copy_filter_{{row.entity.id}}" class="checkbox-custom-label {{row.entity.disabled_copy_list_class}}"></label></li></ul></nav>'},

            { name: 'shared_list', displayName: 'Share List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="share_filter checkbox-custom" id="share_list_\'{{row.entity.id}}\'" name="share_list" ng-click="updateShareListStatus(\'{{row.entity.id}}\')"  \'{{row.entity.checked_shared_list}}\' \'{{row.entity.disabled_shared_list}}\' /><label for="share_filter_\'{{row.entity.id}}\'" class="checkbox-custom-label \'{{row.entity.disabled_class}}\'"></label></li></ul></nav>'},

            { name: 'criteria_name', pinnedLeft:true, displayName:'Detail', cellTemplate:'<span title="{{COL_FIELD}}">{{row.entity.criteria_name == \'\' ? \'-\' : row.entity.criteria_name | limitTo: 60}}</span>' },

            { name: 'edit_list', pinnedLeft:true, displayName:'Edit', cellTemplate: '<span class="edit-list_\'{{row.entity.id}}\' dropdown-list edit-list-icon" id="edit_list_\'{{row.entity.id}}\'"  ng-click="grid.appScope.edit_user_filter(\'{{row.entity.id}}\',\'{{row.entity.edit_list}}\');"  class="edit-list"><i class="fa fa-pencil" aria-hidden="true"></i></span><span class="edit-list-loader_\'{{row.entity.id}}\' edit-list-loader" id="excel_loader_\'{{row.entity.id}}\'"><img src="/drmetrix/assets/img/excel_spinner.gif" alt="Loading icon"></span>' },

            { name: 'apply', pinnedLeft:true, displayName:'Apply', cellTemplate: '<a href="javascript:void(0)" ng-click="apply_user_list(\'{{row.entity.id}}\', \'{{row.entity.apply}}\');" id="apply_filter_{{row.entity.id}}">Apply</a>' },
        ];
        apiService.post('/get_user_lists', formData, config)
        .then(function (data) {
            $scope.loading = false;
            $scope.PostDataResponse = formData;
            vm.gridOptionsList.data = data.data.rows;
        }, function (response) {
            // this function handlers error
        });
    }

    //ui grid code
    $scope.uigridListModal();
});

angular.module('drmApp').controller('ReportsModalCtrl', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
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