angular.module('drmApp').controller('MainController', function ($scope, $http, $state, $stateParams, $rootScope, apiService, $location, $uibModal, modalConfirmService, $timeout, $window, $location) {
    $scope.date = new Date(); // Footer copyright display year
    $rootScope.eulaDisagreeFlag = 0; // this flag will show poup on login page if we disagree eula agreement and redreict to login message with popup message
    $scope.whats_new_toggle = false;
    $rootScope.headerDisplay = 0;
    $scope.selectedNetwork = '';
    $scope.selectDate = 1;
    $scope.programs_id = ''
    /* Primary filter */
    $rootScope.complete_name = localStorage.complete_name;
    $scope.selectDate =  1;
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
    
    $scope.newWindow = function(url) {
        $window.open(url, '_blank','scrollbars=yes,status=yes');
    };

    $scope.downloadPdf = function() {
        var hostname = $location.host() + ':' + $location.port();
        window.open('http://' + hostname + '/drmetrix/api/download_pdf.php');
    }

    $scope.openModalDialog = function(name, targetScope = $scope) {
        var templateUrl, size;
        $scope.modal_name = name;
        switch(name) {
            case 'new_type': {
                templateUrl = './templates/modals/newTypeDialog.html';
                controllerName = 'NewModalController';
                break;
            }
            case 'refine_by': {
                templateUrl = './templates/modals/refineDialog.html';
                controllerName = 'RefineModalController';
                break;
            }
            case 'network_ranking': {
                templateUrl = './templates/modals/networkModalDialog.html';
                controllerName = 'NetworkModalController';
                size = 'xl modal-dialog-centered'
                break;
            }
            case 'programs': {
                templateUrl = './templates/modals/programModalDialog.html';
                controllerName = 'ProgramModalController';
                break;
            }
            case 'refine_by_report': {
                templateUrl = './templates/modals/RefineByReportDialog.html';
                controllerName = 'RefineByReportModalController';
                size = 'lg modal-dialog-centered'
                break;
            }
            case 'network': {
                templateUrl = './templates/modals/networkDialog.html';
                controllerName = 'NetworkDropdownModalController';
                break;
            }
            case 'dow': {
                templateUrl = './templates/modals/dowDialog.html';
                controllerName = 'DowModalController';
                break;
            }
            case 'hod' : {
                templateUrl = './templates/modals/hodDialog.html';
                controllerName = 'HodModalController';
                break;
            }
            case 'dayparts' : {
                templateUrl = './templates/modals/daypartDialog.html';
                controllerName = 'DaypartModalController';
                break;
            }
            case 'programs' : {
                templateUrl = './templates/modals/programsDialog.html';
                controllerName = 'NetworkDropdownModalController';
                break;
            }
            case 'filters': {
                templateUrl= "./templates/modals/FilterDialog.html";
                controllerName = "FiltersModalController";
                size= 'lg modal-dialog-centered';
                break;
            }
            case 'lists': {
                templateUrl= "./templates/modals/ListDialog.html";
                controllerName = "ListsModalController";
                size= 'lg modal-dialog-centered';
                break;
            }
            case 'reports': {
                templateUrl= "./templates/modals/reportsdialog.html";
                controllerName = "ReportsModalController";
                size= 'lg modal-dialog-centered';
                break;
            }
            case 'category_track': {
                templateUrl = "/templates/modals/categoryTrackModal.html";
                controllerName = "TrackModalController";
                break;
            }
            case 'other_track': {
                templateUrl = "/templates/modals/trackModalDialog.html";
                controllerName = "TrackModalController";
                break;
            }
            case 'save_filter_ranking': {
                templateUrl = "./templates/modals/saveFilterModal.html";
                controllerName = "SaveFilterRankingModalController";
                break;
            }
            case 'save_list': {
                templateUrl = "./templates/modals/saveListModal.html";
                controllerName = "saveListModalController";
                break;
            }
        }
        size = size ? size : 'md modal-dialog-centered';
        $scope.openModal(templateUrl, controllerName, size, targetScope );
    }
    
    $scope.verifyDuplicateMobile = function (mobile) {
        var user_id = $('#edit_data_user_id').val();
        var admin_id = sessionStorage.admin_id;
        var hidden_mobile_no = $("#mobile_edit_hidden").val();
        if ($('[id="advancedModalEdit"]').hasClass('is-active')) {
            hidden_mobile_no = $("#mobile_edit_company_hidden").val();
        }

        if ($rootScope.role == 'superadmin') {
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

   


    $scope.openModal = function(templateUrl, controller, size, targetScope = $scope ) {
        $scope.modalInstanceMain =  modalConfirmService.showModal({
            backdrop: false,
            keyboard: true,
            modalFade: true,
            templateUrl: templateUrl,
            controller: controller,
            scope: targetScope,
            size: size ? size : 'md modal-dialog-centered',
            resolve: {
                    data: function () {
                  return $scope;
                }
              }
          });

          $scope.modalInstanceMain.result.then(function(response){console.log(response);
              targetScope.result = `${response} button hitted`;
              targetScope = response;
          });

          $scope.modalInstanceMain.result.catch(function error(error) {
            if(error === "backdrop click") {
              // do nothing
            } else {
              // throw error;
            }
          });
    };

    $rootScope.openCategoryTrackModal = function() {
        $scope.openModalDialog('category_track');
    }

    $scope.selectCategory = function (item, value, type ,tabName) {
        value = !value;
        if (type == 'all') {
            angular.forEach(item, function (data) {
                tabName == 'brand' ? data.isBrandSelected = value : data.isCreativeSelected = value;
                angular.forEach(data.subcategory, function (cat) {
                    tabName == 'brand' ? cat.isBrandSelected = value : cat.isCreativeSelected = value;
                });
            })
        } else if (type == 'subcategory') {
            tabName == 'brand' ? $scope.all_brand_cat = value : $scope.all_creative_cat = value;
                var count = 0;
                angular.forEach(item, function (subcats) {
                    tabName == 'brand' ? subcats.isBrandSelected = value : subcats.isCreativeSelected = value;
                });
        } else {
            angular.forEach(item.subcategory, function (data) {
                tabName == 'brand' ? data.isBrandSelected = value : data.isCreativeSelected = value;
            });
            tabName == 'brand' ? $scope.all_brand_cat = value : $scope.all_creative_cat = value;
        }
    }

    $scope.getParameters = function () {
        var selectDateDropDown = $scope.selectDate;

        if (selectDateDropDown == 1) {
            sd = localStorage.media_start_db;
            ed = localStorage.media_end_db;
        }

        if (selectDateDropDown == 2) {
            sd = localStorage.current_start_db;
            ed = localStorage.current_end_db;
        }

        if (selectDateDropDown == 3) {
            sd = localStorage.last_quarter_db_start_date;
            ed = localStorage.last_quarter_db_end_date;
        }

        if (selectDateDropDown == 4) {
            sd = localStorage.last_year_db_start_date;
            ed = localStorage.last_year_db_end_date;
        }

        if (selectDateDropDown == 5) {
            sd = localStorage.lifetime_db_min_sd;
            ed = localStorage.lifetime_db_max_ed;
        }

        if (selectDateDropDown == 6) {
            sd = localStorage.media_start_db;
            ed = localStorage.media_end_db;
        }

        if (selectDateDropDown == 7) {
            sd = localStorage.media_month_start_db;
            ed = localStorage.media_month_end_db;
        }

        if (selectDateDropDown == 8) {
            sd = localStorage.last_quarter_db_start_date;
            ed = localStorage.last_quarter_db_end_date;
        }

        if (selectDateDropDown == 9) {
            sd = localStorage.current_start_db;
            ed = localStorage.current_end_db;
        }

        if (selectDateDropDown == 10) {
            sd = localStorage.media_currentmonth_start_db;
            ed = localStorage.media_currentmonth_end_db;
        }

        if (selectDateDropDown == 11) {
            sd = localStorage.current_quarter_db_start_date;
            ed = localStorage.current_quarter_db_end_date;
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
        $scope.applied_list_type    = data.applied_list_type;
        $scope.applied_list_ids     = data.applied_list_ids;
        $scope.list_id              = data.list_id;
        $scope.display_list_name    = data.display_list_name;
        $scope.applyFilter();
     });

     $scope.hideTrackingModal = function() {
        $timeout(function () {  
            $scope.success_alert_setup_msg = $scope.error_alert_setup_msg = '';
            $scope.modalInstanceMain.close(); 
        }, 2000);
       
     }

  
     $rootScope.openCategoryTrack = function(alert_type) {
        $scope.alert_type = alert_type;
        var formData =  {'alert_type' : alert_type }
        apiService.post('/get_categorylist_with_categorytracking', formData)
        .then(function (data) {
            var response = data.data;
            var cat_length = 0
            var brandSelectedIdArray = response.brand_data.split(',');
            var creativeSelectedIdArray = response.creative_data.split(',');
            if (response.status) {
                $scope.category_result = response.result;
                $scope.all_brand_cat  = false;
                $scope.all_creative_cat = false;
                angular.forEach($scope.category_result, function(cats) {
                    cats.isBrandSelected = false;
                    cats.isCreativeSelected = false;
                    var subCatBrand_length = subCatCreative_length = 0;
                    angular.forEach(cats.subcategory, function(subcats, key) {
                        cat_length++;
                        subcats.isBrandSelected = false;
                        subcats.isCreativeSelected = false;
                        if(brandSelectedIdArray.indexOf(subcats.sub_category_id) !== -1) {
                            subCatBrand_length++;
                            subcats.isBrandSelected = true;
                        }
                        if(creativeSelectedIdArray.indexOf(subcats.sub_category_id) !== -1) {
                            subCatCreative_length++;
                            subcats.isCreativeSelected = true;
                        }
                        console.log(subCatBrand_length+'****'+cats.subcategory.length);
                        if(subCatBrand_length == cats.subcategory.length) {
                            cats.isBrandSelected = true;
                        }

                        if(subCatCreative_length == cats.subcategory.length) {
                            cats.isCreativeSelected = true;
                        }
                        
                    });
                });

                if(brandSelectedIdArray.length == cat_length) {
                    $scope.all_brand_cat = true;
                }

                if(creativeSelectedIdArray.length == cat_length) {
                    $scope.all_creative_cat = true;
                }
                if (response.frequency != "") {
                    angular.forEach($scope.tracking_frequency, function(tracking) {
                        if(response.frequency.includes(tracking.id)) {
                            tracking.selected = true;
                        }
                    });
                }
               
                var classification = response.classification;
                if (classification != "") {
                    angular.forEach(classification, function (element) {
                        let obj = $scope.shortFormTrackingClassification.find(obj => obj.id == element);
                        obj.selected = true;
                    }); 
                }
            }
            $scope.openModalDialog('category_track');
        },function (error) {
            console.log('Error');
        });
    }

    $scope.show_subcategories = function(parent_id) {
        var parent_id = $(this).attr("custom-self-id");
        if ($('tr[custom-parent-id="' + parent_id + '"]').is(':visible')) {
            $('tr[custom-parent-id="' + parent_id + '"]').slideUp("fast");
            $(this).removeClass("fa-caret-right");
            $(this).removeClass("fa-caret-down");
            $(this).addClass("fa-caret-right");
        } else {
            $(this).removeClass("fa-caret-right");
            $(this).removeClass("fa-caret-down");
            $(this).addClass("fa-caret-down");
            $('tr[custom-parent-id="' + parent_id + '"]').slideDown("slow");
        }
    }
    $rootScope.viewTrackingDialogue = function(alert_type, type_id, name) {
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

           if (response.status) {
               $scope.result = response.result;
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
            $scope.openModalDialog('other_track');
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

    $rootScope.setTracking = function() {
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

        if (alert_type != 'filter' && alert_type != 'category' && elements == "") {
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
        var type_id = {};
        var length_of_elements = 0;
        angular.forEach($scope.category_result, function(cats) {
            angular.forEach(cats.subcategory, function(subcats) {
                if(subcats.isBrandSelected == true) {
                    length_of_elements++;
                    type_id[subcats.sub_category_id] = 'brand';
                    if(subcats.isCreativeSelected == true) {
                        length_of_elements++;
                        type_id[subcats.sub_category_id] = 'brand,creative';
                    }
                } else {
                    if(subcats.isCreativeSelected == true) {
                        length_of_elements++;
                        type_id[subcats.sub_category_id] = 'creative';
                    }
                }
            });
        });
     
        if(length_of_elements == 0) {
            $scope.cat_error_alert_setup_msg = '<span>Please select at least one category either for brand or creative.</span>';
            $timeout(function () { $scope.cat_error_alert_setup_msg = ''; }, 2000);
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

        var formData = { "alert_type": alert_type, "type_id": JSON.stringify(type_id),"frequency": frequency, "status": status, "brand_class": brand_class
        }
        var url = 'set_cat_tracking_detail';
        if(alert_type!= 'category') {
            formData.tracked_elements = elements;
            formData.name = name;
            url = 'set_tracking_detail';
        }
        apiService.post('/'+url, formData)
            .then(function (data) {
                var response = data.data
                if (response.status == true) {
                   $scope.success_alert_setup_msg = '<span>Alert tracking is set up successfully.</span>';
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
                        // if (status == 'active') {
                        //     $('[custom-attr="config_alert_' + custom_attr_id + '"]').addClass("fa-eye blue-eye");
                        //     $('[custom-attr="config_alert_' + custom_attr_id + '"]').removeClass("grey-eye fa-eye-slash");
                        // } else {
                        //     $('[custom-attr="config_alert_' + custom_attr_id + '"]').removeClass("fa-eye blue-eye");
                        //     $('[custom-attr="config_alert_' + custom_attr_id + '"]').addClass("grey-eye fa-eye-slash");
                        // }
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
        $rootScope.formdata         = {'cat' : $scope.categories_selected , 'startDate' : $scope.selectDate,  'val' : $scope.selectDate,  'sd' : $scope.sd, 'ed' : $scope.ed, 'c' : $scope.selectClassificationValues , 'spanish' : $scope.selectLang, 'responseType': $scope.returnText , 'type' : $scope.tab , 'creative_duration' : $scope.selectedDurations.join(), 'flag': $rootScope.active_flag,"refine_filter_opt": $scope.refineBy,"refine_filter_opt_text":$scope.search_by_tfn,"refine_apply_filter":0,"new_filter_opt":$scope.newType ,'network_id' : $scope.selectedNetwork ,'network_alias' : $scope.selectedNetworkAlias, 'refine_by' : $scope.refine_by, 'search_by_tfn' : $scope.search_by_tfn, 'programs_id' : $scope.programs_id,'list_id': $scope.list_id,'applied_ids' :$scope.applied_list_ids , 'primary_tab' :$scope.applied_list_type,  list_ranking_id : $scope.list_id}

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
                $rootScope.uigridRefineData();
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

    $scope.setLifetimeVariables = function() {
        $scope.lifetime_flag = 0;
        if($scope.lifetimeOther) {
            $scope.lifetime_flag = 1;
        } 

        if ($scope.calender_flag == 1) {
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
    $scope.today_date = mm + '/' + dd + '/' + yyyy;
    //get all media data
    var async = false;
    var currentdate = new Date();
    $scope.selectedYear = currentdate.getFullYear();

    apiService.get('/get_all_media_data', {})
    .then(function (response) {
        // Store response data
        var data = response.data;
        var sd = data.last_week.sd;
        var ed = data.last_week.ed;
        var end_date = new Date(ed.replace(" ", "T"));
        var year = end_date.getUTCFullYear();
        $rootScope.year = year;
        $scope.selectedYear =  localStorage.selectedYear = $scope.current_year = data.current_year;

        //life time
        $scope.lifetime_year = localStorage.lifetime_year = data.lifetime.year;
        $scope.lifetime_min_sd = localStorage.lifetime_min_sd = data.lifetime.start_date;
        $scope.lifetime_max_ed = localStorage.lifetime_max_ed = data.lifetime.end_date;
        $scope.lifetime_db_min_sd = localStorage.lifetime_db_min_sd = data.lifetime.start_date_db;
        $scope.lifetime_db_max_ed = localStorage.lifetime_db_max_ed = data.lifetime.end_date_db;

        //last week
        $scope.media_start_date = localStorage.media_start_date = data.last_week.start_date;
        $scope.media_end_date = localStorage.media_end_date = data.last_week.end_date;
        $scope.media_start_db = localStorage.media_start_db = data.last_week.sd;
        $scope.media_end_db = localStorage.media_end_db = data.last_week.ed;
        $scope.week_calendar_id = localStorage.week_calendar_id = data.last_week.calendar_id;

       //last month
        $scope.media_month_date = localStorage.media_month_date = data.last_month.start_date;
        $scope.media_monthend_date = localStorage.media_monthend_date = data.last_month.end_date;
        $scope.media_month_start_db = localStorage.media_month_start_db = data.last_month.sd;
        $scope.media_month_end_db = localStorage.media_month_end_db = data.last_month.ed;
        $scope.month_calendar_id = localStorage.month_calendar_id = "(" + data.last_month.calendar_id + ")";

        //Current week data
        $scope.current_start_date = localStorage.current_start_date = data.current_week.start_date;
        $scope.current_end_date = localStorage.current_end_date = data.current_week.end_date;
        $scope.current_calendar_id = localStorage.current_calendar_id = data.current_week.calendar_id;
        $scope.current_week = localStorage.current_week = data.current_week.calendar_id;

        //Current Month Data
        $scope.media_currentmonth_date = localStorage.media_currentmonth_date = data.current_month.start_date;
        $scope.media_currentmonthend_date = localStorage.media_currentmonthend_date = data.current_month.end_date;
        $scope.current_start_db = localStorage.current_start_db = data.current_week.sd;
        $scope.current_end_db = localStorage.current_end_db = data.current_week.ed;
        $scope.currentmonth_calendar_id = localStorage.currentmonth_calendar_id = "(" + data.current_month.calendar_id + ")";
        $scope.current_month = localStorage.current_month = data.current_month.media_month_id;
        $scope.media_currentmonth_start_db = localStorage.media_currentmonth_start_db = data.current_month.sd;
        $scope.media_currentmonth_end_db = localStorage.media_currentmonth_end_db = data.current_month.ed;

        //Last Quarter data
        $scope.number_of_quarter = localStorage.number_of_quarter = data.lst_quarter_no;
        $scope.last_quarter_start_date = localStorage.last_quarter_start_date = data.last_quarter[1];
        $scope.last_quarter_end_date = localStorage.last_quarter_end_date = data.last_quarter[3];
        $scope.last_quarter_db_start_date = localStorage.last_quarter_db_start_date = data.last_quarter[0];
        $scope.last_quarter_db_end_date = localStorage.last_quarter_db_end_date = data.last_quarter[2];

        //Current quarter data
        $scope.number_of_currentquarter = localStorage.number_of_currentquarter = data.quarter_no;
        $scope.current_quarter_start_date = localStorage.current_quarter_start_date = data.quarter[1];
        $scope.current_quarter_end_date = localStorage.current_quarter_end_date = data.quarter[3];
        $scope.current_quarter_db_start_date = localStorage.current_quarter_db_start_date = data.quarter[0];
        $scope.current_quarter_db_end_date = localStorage.current_quarter_db_end_date = data.quarter[2];
        $scope.current_qtr = localStorage.current_qtr = data.quarter_no;
        $scope.years = localStorage.years = data.years;
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


    $scope.editableContent = function() {
        $scope.editable = 1;
    }
    
    $scope.cancelFilter = function() {
        $scope.editable = 0;
    }

    $rootScope.menuSelected = 'ranking';
    $rootScope.menuItemClick = function (item) {
        var page = item.aid;
        $rootScope.menuSelected = page;
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
            $scope.openModalDialog('reports'); // 0 -> Off -> normal mode
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
                }
                $state.go('login');
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
        $scope.openModalDialog('filters');
    }

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


    $scope.reportsModal = function(){
        $scope.modalInstance =  $uibModal.open({
            templateUrl: "./templates/modals/reportsdialog.html",
            controller: "ReportsModalController",
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
        $scope.openModalDialog('lists');
        //$scope.openListModal();
    }
    /***List code Ends */

    $scope.$watch('globalSearchInputText', function(nVal, oVal) {
        if (nVal !== oVal) {
            // $scope.global_search_ajax(nVal);
        }
    });

    $rootScope.cleanFileName = function(changedFileName) {
    changedFileName = $rootScope.replaceFileNameForSQLAttack(changedFileName);
    if (/[&\/\\#$~%`@^'":;*?<>{}|]/.test(changedFileName)) {
        return 1;
    }
    }

    $rootScope.replaceFileNameForSQLAttack = function(filename) { 
        var mapObj = {'Union All':"Union_All"};
        var re = new RegExp(Object.keys(mapObj).join("|"),"gi");

        return filename.replace(re, function(matched){
            return mapObj[matched];
        });
    }

});







