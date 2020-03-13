angular.module('drmApp').controller('MainController', function ($scope, $http, $state, $stateParams, $rootScope, apiService, $location, $uibModal) {
    $scope.date = new Date(); // Footer copyright display year
    $rootScope.eulaDisagreeFlag = 0; // this flag will show poup on login page if we disagree eula agreement and redreict to login message with popup message
    $scope.whats_new_toggle = false;
    $rootScope.headerDisplay = 0;
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
        href: '#',
        aclass: 'my_reports',
        aid: '',
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
        console.log($scope.selectedYear);
        console.log($scope.current_year);
        //last week
        sessionStorage.media_start_date = data.last_week.start_date;
        sessionStorage.media_end_date = data.last_week.end_date;
        sessionStorage.week_calendar_id = data.last_week.calendar_id;

       //last month
        sessionStorage.media_month_date = data.last_month.start_date;
        sessionStorage.media_monthend_date = data.last_month.end_date;
        sessionStorage.month_calendar_id = "(" + data.last_month.calendar_id + ")";

        //Current week data
        sessionStorage.current_start_date = data.current_week.start_date;
        sessionStorage.current_end_date = data.current_week.end_date;
        sessionStorage.current_calendar_id = data.current_week.calendar_id;
        $scope.current_week = data.current_week.calendar_id;

        //Current Month Data
        sessionStorage.media_currentmonth_date = data.current_month.start_date;
        sessionStorage.media_currentmonthend_date = data.current_month.end_date;
        sessionStorage.currentmonth_calendar_id = "(" + data.current_month.calendar_id + ")";

        //Last Quarter data
        sessionStorage.number_of_quarter = data.lst_quarter_no;
        sessionStorage.last_quarter_start_date = data.last_quarter[1];
        sessionStorage.last_quarter_end_date = data.last_quarter[3];

        //Current quarter data
        sessionStorage.number_of_currentquarter = data.quarter_no;
        sessionStorage.current_quarter_start_date = data.quarter[1];
        sessionStorage.current_quarter_end_date = data.quarter[3];
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

    
    $rootScope.menuItemClick = function (item) {
        var page = item.aid
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
        $rootScope.catgeorySideBar = 0; // hide category section on load
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
        console.log($rootScope.list_tab.length);
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
        var tab = $scope.type;
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

    // Call filter Angular uiGrid
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

            { name: 'shared_filter', displayName: 'Share Filter', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="share_filter checkbox-custom" id="share_filter_{{row.entity.id}}" name="share_filter" ng-click="updateShareFilterStatus({{row.entity.id}})"  {{row.entity.checked_shared_filter}} {{row.entity.disabled_shared_filter}} /><label for="share_filter_{{row.entity.id}}" class="checkbox-custom-label {{row.entity.disabled_class}}"></label></li></ul></nav>'},

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


angular.module('drmApp').controller('ListsCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.sharedList = 'My';
    $scope.selected_user = '';
   
    
    $scope.show_user_list = function () {
        //ui grid code
    }

    $scope.show_user_list();
    
    $scope.showSharedLists = function(item) {
        $scope.sharedList = item;
            //with ui grid code, displayes grid data according to rules set
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
      }
  
});