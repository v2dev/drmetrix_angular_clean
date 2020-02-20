'use strict';
var my_report_grid_data = 0;

angular.module('drmApp').factory('PathInterceptor', function ($location, FoundationApi) {
    var path = {
        request: function (config) {
            return config;
        },
        response: function (response) {
            return response;
        },
        'responseError': function (response) {
            if (response.statusText != "from network export" && response.statusText != "from refine export" && response.statusText != "networks_excel") {
                if (response.status == 401) {
                    $('#browser_timeout').modal('show');
                } else if (response.status == 403) {
                    $('#session_timeout').modal('show');
                } else if (response.status == 406) {
                    $('#access_error').modal('show');
                } else if (response.status == 409) {
                    $('#update_version').modal('show');
                } else if (response.status == 307) {
                    window.location.href = "/drmetrix";
                } else {
                    $('#request_timeout').modal('show');
                    // FoundationApi.publish('request_timeout', 'show');
                }
                $("#r_loader").hide();
                $("#dialogModal").hide();
            }
        }
    };
    return path;
});

angular.module('drmApp').config(['$httpProvider', function ($httpProvider) {
    $httpProvider.interceptors.push('PathInterceptor');
}]);

angular.module('drmApp').controller('MainController', function ($scope, $http, $state, $stateParams, $rootScope, apiService, $location) {
    var result = {};
    var result1 = {};
    $scope.state = 0;
    var lastSel, lastSelectedName, extension;
    var checked_row_id_array = new Array();
    var categoryId = '';
    var url_path = window.location.pathname;
    var path = location.search;
    sessionStorage.ranking = 0;
    sessionStorage.pdf = 0;
    delete sessionStorage.stored_data;
    sessionStorage.active_category_flag = 0;
    $rootScope.email_user_id = '';
    $rootScope.sharedFilter = 'My';
    $rootScope.sharedReport = 'My';
    $rootScope.displayUrlError = 0;
    $rootScope.redirectPage = 0;
    $rootScope.rankingScope;
    $rootScope.primaryNetworkFilterApplied = 0;
    $rootScope.searchTextValidation = '3 or more characters. Use "quotes" to narrow results.';
    $rootScope.menu_states = {};
    $rootScope.menu_states.activeItem = 'ranking';
    $rootScope.main_menu = [{
        liid: 'rank',
        nghide: 'superadmin',
        liclass: 'd-none d-xl-block',
        href: 'ranking',
        aclass: 'ranking',
        angclass: '(my_network_called == 0) ? \'top-menu-active\' : \'\'',
        aid: 'ranking',
        title: 'Home',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-01.svg',
        toggle: '',
        target: '',
    }, {
        liid: 'networks',
        nghide: 'superadmin || user_company == 0',
        liclass: 'd-none d-xl-block',
        href: 'ranking',
        aclass: 'my_networks',
        angclass: '(my_network_called == 1) ? \'top-menu-active\' : \'\'',
        aid: 'my_networks',
        title: 'Networks',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-12.svg',
        toggle: '',
        target: '',
    }, {
        liid: 'my_reports',
        nghide: 'superadmin',
        liclass: 'd-none d-xl-block',
        href: '#',
        aclass: 'my_reports',
        angclass: '',
        aid: '',
        title: 'Reports',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-03.svg',
        toggle: 'modal',
        target: '#advancedModalReport',
    }, {
        liid: 'directories',
        nghide: 'superadmin',
        liclass: 'desktop-nav',
        href: '#',
        aclass: 'directories',
        angclass: '',
        aid: '',
        title: 'Directories',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-13.svg',
        toggle: '',
        target: '',
    }, {
        liid: '',
        nghide: 'superadmin',
        liclass: 'menu-tooltip d-none d-xl-block',
        href: '/drmetrix/tracking',
        aclass: 'tracking',
        angclass: '{\'top-menu-active\': isActive(\'tracking\')}',
        aid: 'tracking',
        title: 'Configure Emails',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-06.svg',
        toggle: '',
        target: '',
    },];
    $rootScope.right_main_menu = [{
        liid: 'mob_rank',
        nghide: 'superadmin',
        href: 'ranking',
        aclass: 'ranking',
        angclass: '(my_network_called == 0) ? \'top-menu-active\' : \'\'',
        aid: 'mob_ranking',
        title: 'Home',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-01.svg',
        toggle: '',
        target: '',
    }, {
        liid: 'mob_networks',
        nghide: 'superadmin || user_company == 0',
        href: 'ranking',
        aclass: 'my_networks',
        angclass: '(my_network_called == 1) ? \'top-menu-active\' : \'\'',
        aid: 'mob_my_networks',
        title: 'Networks',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-12.svg',
        toggle: '',
        target: '',
    }, {
        liid: 'mob_reports',
        nghide: 'superadmin',
        href: '#',
        aclass: 'my_reports',
        angclass: '',
        aid: '',
        title: 'Reports',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-03.svg',
        toggle: 'modal',
        target: '#reportlisting',
    }, {
        liid: 'mob_directories',
        nghide: 'superadmin',
        href: '#',
        aclass: 'directories',
        angclass: '',
        aid: '',
        title: 'Directories',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-13.svg',
        toggle: '',
        target: '',
    }, {
        liid: 'mob_config_email',
        nghide: 'superadmin',
        href: '/drmetrix/tracking',
        aclass: 'tracking',
        angclass: '{\'top-menu-active\': isActive(\'tracking\')}',
        aid: 'tracking',
        title: 'Configure Emails',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-06.svg',
        toggle: '',
        target: '',
    },];
    $rootScope.sub_menu = [{
        ngshow: '',
        nghide: 'superadmin || ((!assistant_admin || assistant_admin != 1) && !admin)',
        liclass: 'd-none d-xl-block',
        alt: 'User',
        href: '/drmetrix/userAccount',
        aclass: 'userAccount',
        angclass: '{\'top-menu-active\': isActive(\'userAccount\')}',
        aid: 'user_account',
        title: 'User',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-11.svg',
        target: '',
    }, {
        ngshow: '',
        nghide: 'superadmin',
        liclass: 'd-none d-xl-block',
        alt: 'Network List',
        href: '',
        aclass: '',
        angclass: '',
        aid: '',
        title: 'Network List',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-04.svg',
        target: '_blank',
    }, {
        ngshow: '',
        nghide: 'superadmin',
        liclass: 'd-none d-xl-block',
        alt: 'AdSphere Blog',
        href: $rootScope.ADSPHERE_BLOG_URL,
        aclass: '',
        angclass: '',
        aid: 'blog_status',
        title: 'Blog',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-02.svg',
        target: '_blank',
    }, {
        ngshow: '',
        nghide: 'superadmin',
        liclass: 'd-none d-xl-block',
        alt: 'User Guide',
        href: 'https://drmetrix.com/public/AdSphere%20User%20Guide.pdf',
        aclass: 'userguide-link',
        angclass: '',
        aid: '',
        title: 'User Guide',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-05.svg',
        target: '_blank',
    }, {
        ngshow: '',
        nghide: '',
        liclass: 'd-none d-xl-block',
        alt: 'Dark Theme',
        href: 'javascript:',
        aclass: 'darktheme-off',
        angclass: '',
        aid: 'app_theme',
        title: 'Theme',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-08.svg',
        target: '',
    }, {
        ngshow: '',
        nghide: 'superadmin',
        liclass: 'd-none d-xl-block',
        alt: 'System Status',
        href: $rootScope.SYSTEM_STATUS_URL,
        aclass: '',
        angclass: '',
        aid: 'sys_status',
        title: 'System Status',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-15.svg',
        target: '_blank',
    },]
    $rootScope.right_sub_menu = [{
        ngshow: '',
        nghide: 'superadmin || ((!assistant_admin || assistant_admin != 1) && !admin)',
        alt: 'User',
        href: '/drmetrix/userAccount',
        aclass: 'userAccount',
        angclass: '{\'top-menu-active\': isActive(\'userAccount\')}',
        aid: 'mob_user_account',
        title: 'User',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-11.svg',
        target: '',
    }, {
        ngshow: '',
        nghide: 'superadmin',
        alt: 'Network List',
        href: '',
        aclass: '',
        angclass: '',
        aid: '',
        title: 'Network List',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-04.svg',
        target: '_blank',
    }, {
        ngshow: '',
        nghide: 'superadmin',
        alt: 'AdSphere Blog',
        href: $rootScope.ADSPHERE_BLOG_URL,
        aclass: '',
        angclass: '',
        aid: 'blog_status',
        title: 'Blog',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-02.svg',
        target: '_blank',
    }, {
        ngshow: '',
        nghide: 'superadmin',
        alt: 'User Guide',
        href: 'https://drmetrix.com/public/AdSphere%20User%20Guide.pdf',
        aclass: 'userguide-link',
        angclass: '',
        aid: '',
        title: 'User Guide',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-05.svg',
        target: '_blank',
    }, {
        ngshow: '',
        nghide: '',
        alt: 'Dark Theme',
        href: 'javascript:',
        aclass: 'darktheme-off',
        angclass: '',
        aid: 'mob_app_theme',
        title: 'Theme',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-08.svg',
        target: '',
    }, {
        ngshow: '',
        nghide: 'superadmin',
        alt: 'System Status',
        href: $rootScope.SYSTEM_STATUS_URL,
        aclass: '',
        angclass: '',
        aid: 'sys_status',
        title: 'System Status',
        src: '/drmetrix/assets/img/menuiconblue/menuiconset-15.svg',
        target: '_blank',
    },]

    $rootScope.menu_items = [{
        id: 'ranking',
        title: 'Home',
        icon: 'menuiconset-01.svg',
        click: 'redirect_ranking_page()',
        href: 'javascipt:void();',
    }, {
        id: 'my_networks',
        title: 'My Networks',
        icon: 'menuiconset-12.svg',
        click: 'setParameter()',
        href: 'javascipt:void();',
    }, {
        id: 'my_reports',
        title: 'Reports',
        icon: 'menuiconset-03.svg',
        click: 'callMyReports()',
        href: 'javascipt:void();',
    },
    {
        id: 'directories',
        title: 'Directories',
        icon: 'menuiconset-13.svg',
        click: '',
        href: 'javascipt:void();',
    },
    {
        id: 'tracking',
        title: 'Configure Emails',
        icon: 'menuiconset-06.svg',
        click: '',
        href: '/drmetrix/tracking',
    }];

    if ($.cookie('theme') == null || $.cookie("theme") == 'original') {
        $.cookie("theme", "original", { expires: 30 });
        $("#theme").prop("checked", "true");
    } else {
        $("#theme").prop("checked", "false");
        $("#theme").removeAttr("checked");
    }

    if ($.cookie('video') == null || $.cookie("video") == '1') {
        $.cookie("video", "1", { expires: 30 });
    }
    //get querystring

    $scope.init = function () {
        delete localStorage.notificationNewCount;
        delete localStorage.notificationNewLiClicked;
        delete localStorage.notificationBuildLink;
        delete localStorage.notifSystemStatusLink;
        delete localStorage.notifBlogStatusLink;

        if(!sessionStorage.loggedIn) return;

        apiService.post('/get_user', $scope.user)
            .success(function (data) {
                if (data.status) {
                    // console.log(data);
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
            .error(function (data, status, headers, config) {
                // Error
            })
    }

    $scope.init1 = function (data) {
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
        var notificationUL = angular.element(document.getElementById('whatsnew-ul'));
        var notificationNewEl = angular.element(document.getElementById('notification-new'));

        var notificationNewCount = notificationNewEl.text();
        var notificationBuildLink = $(notificationUL.children().eq(1)).attr('href');
        var notifBlogStatusLink = $('#blog_status').attr('href');
        var notifSystemStatusLink = $('#sys_status').attr('href');

        if(notifBlogStatusLink.indexOf('?') != -1) $('.ads-blog-status').show();
        if(localStorage.notifBlogStatusLink) {
            if(localStorage.notifBlogStatusLink == notifBlogStatusLink) {
                $('#ads-blog-status').remove();
            } else {
                delete localStorage.notifBlogStatusLink;
            }
        } else {
            delete localStorage.notifBlogStatusLink;
        }

        if(notifSystemStatusLink.indexOf('?') != -1) $('.system-status').show();
        if(localStorage.notifSystemStatusLink) {
            if(localStorage.notifSystemStatusLink == notifSystemStatusLink) {
                $('#system-status').remove();
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
                $(notificationUL.children().eq( parseInt(liArr[i]) + firstLIsIgnored ).children()).addClass('watsnew-visited');
            }
        }

        // if(notificationBuildLink) localStorage.notificationBuildLink = notificationBuildLink;
        apiService.post('/update_user', {'notificationBuildLink': notificationBuildLink})
            .success(function (data) {
                if (data.status) {
                    // console.log(data);
                }
            })
            .error(function (data, status, headers, config) {
                // Error
            })
    }

    $scope.init();

    $scope.setVisited = function (event, idx) {
        if(localStorage.notificationNewCount) {
            if( !$(event.target).hasClass('watsnew-visited') ) {
                if($(event.target).hasClass('fa-play-circle-o')) {
                    if( $(event.target).parent().parent().hasClass('watsnew-visited') ) {
                        return;
                    } else {
                        $(event.target).parent().parent().addClass('watsnew-visited');
                    }
                } else if( event.target.nodeName == 'IMG') {
                    if( $(event.target).parent().hasClass('watsnew-visited') ) {
                        return;
                    } else {
                        $(event.target).parent().addClass('watsnew-visited');
                    }
                } else if( event.target.nodeName == 'SPAN') {
                    if( $(event.target).parent().hasClass('watsnew-visited') ) {
                        return;
                    } else {
                        $(event.target).parent().addClass('watsnew-visited');
                    }
                } else $(event.target).addClass('watsnew-visited');

                localStorage.notificationNewCount = parseInt(localStorage.notificationNewCount) - 1;
                var notificationNewEl = angular.element(document.getElementById('notification-new'));

                if( localStorage.notificationNewCount == "0" ) {
                    notificationNewEl.remove();
                } else {
                    notificationNewEl.text( localStorage.notificationNewCount );
                }

                if(localStorage.notificationNewLiClicked) {
                    localStorage.notificationNewLiClicked = localStorage.notificationNewLiClicked + ',' + idx;
                } else {
                    localStorage.notificationNewLiClicked = idx;
                }

                apiService.post('/update_user', {'notificationNewCount': localStorage.notificationNewCount, 'notificationNewLiClicked': localStorage.notificationNewLiClicked})
                .success(function (data) {
                    if (data.status) {
                        // console.log(data);
                    }
                })
                .error(function (data, status, headers, config) {
                    // Error
                })
            }
        }
    }

    $scope.changeBlogStatus = function (orgObj) {
        var notifBlogStatusLink = $('#blog_status').attr('href');
        if(notifBlogStatusLink) localStorage.notifBlogStatusLink = notifBlogStatusLink;
        $('#ads-blog-status').remove();
        apiService.post('/update_user', {'notifBlogStatusLink': notifBlogStatusLink})
        .success(function (data) {
            if (data.status) {
                // console.log(data);
            }
        })
        .error(function (data, status, headers, config) {
            // Error
        });
    }

    $scope.changeSystemStatus = function (orgObj) {
        var notifSystemStatusLink = $('#sys_status').attr('href');
        if(notifSystemStatusLink) localStorage.notifSystemStatusLink = notifSystemStatusLink;
        $('#system-status').remove();
        apiService.post('/update_user', {'notifSystemStatusLink': notifSystemStatusLink})
        .success(function (data) {
            if (data.status) {
                // console.log(data);
            }
        })
        .error(function (data, status, headers, config) {
            // Error
        });
    }

    $scope.changeThemeStatus = function (orgObj) {	
        var activate = $("#app_theme").find('.activate').css("display") , themeName;;	
        var theme;	
        if(activate == 'block') {	
            $("#app_theme").find('.activate').css("display",'none');	
            $("#app_theme").find('.deactivate').css("display",'block');	
            themeName = 'original';	
            theme = 0;	
            $('body').removeClass('darktheme');	
            changeColor('original');	
        } else {	
            theme = 1;	
            $("#app_theme").find('.activate').css("display",'block');	
            $("#app_theme").find('.deactivate').css("display",'none');	
            themeName = 'black';	
            $('body').addClass('darktheme');        	
        }	
        if (themeName == 'black') {	
            changeColor('black');	
        } else {	
            changeColor('original');	
        }	
        apiService.post('/update_user', {'theme': theme})	
        .success(function (data) {	
            if (data.status) {	
                // console.log(data);	
            }	
        })	
        .error(function (data, status, headers, config) {	
            // Error	
        });	
    }

    $scope.changeBlogAdminStatus = function (orgObj) {
        var notifBlogStatusLink = $('#blog_status').attr('href');

        if(blogStatus == 'original') {
            blogStatus = 'black';
            $('#blog_admin_status').find('.activate').show();
            $('#blog_admin_status').find('.deactivate').hide();
            notifBlogStatusLink += '?' + new Date().getTime();
        } else {
            blogStatus = 'original';
            $('#blog_admin_status').find('.activate').hide();
            $('#blog_admin_status').find('.deactivate').show();
            var lastIndexOfQue = notifBlogStatusLink.lastIndexOf('?');
            if(lastIndexOfQue != -1) {
                notifBlogStatusLink = notifBlogStatusLink.substring(0, lastIndexOfQue);
                $('#blog_status').attr('href', notifBlogStatusLink);
            }
        }
        apiService.post('/update_config', {'notifBlogStatusLink': notifBlogStatusLink})
        .success(function (data) {
            if (data.status) {
                // console.log(data);
            }
        })
        .error(function (data, status, headers, config) {
            // Error
        });
    }

    $scope.changeSystemAdminStatus = function (orgObj) {
        var notifSystemStatusLink = $('#sys_status').attr('href');

        if(systemStatus == 'original') {
            systemStatus = 'black';
            $('#system_admin_status').find('.activate').show();
            $('#system_admin_status').find('.deactivate').hide();
            notifSystemStatusLink += '?' + new Date().getTime();
        } else {
            systemStatus = 'original';
            $('#system_admin_status').find('.activate').hide();
            $('#system_admin_status').find('.deactivate').show();
            var lastIndexOfQue = notifSystemStatusLink.lastIndexOf('?');
            if(lastIndexOfQue != -1) {
                notifSystemStatusLink = notifSystemStatusLink.substring(0, lastIndexOfQue);
                $('#sys_status').attr('href', notifSystemStatusLink);
            }
        }
        apiService.post('/update_config', {'notifSystemStatusLink': notifSystemStatusLink})
        .success(function (data) {
            if (data.status) {
                // console.log(data);
            }
        })
        .error(function (data, status, headers, config) {
            // Error
        });
    }

    $scope.getParameterByName = function (name, url) {
        url = !url ? window.location.href : url
        var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(url);
        return results ? results[1] : undefined;
    }

    var params = path.split("?");
    params.forEach(function (it) {
        if (it) {
            var param = it.split("=");
            result[param[0]] = param[1];
        }
    });


    var params1 = url_path.split("/");
    var i = 0;

    sessionStorage.checkIn = 0;
    params1.forEach(function (t) {
        if (t) {
            var param1 = t.split("=");
            if (i == 2) {
                sessionStorage.brandId = t;
            }
            if (i == 1) {
                sessionStorage.type = t;
                sessionStorage.checkIn = 1;
            }
            if (i == 3) {
                sessionStorage.form = t;
            }
            result1[param1[0]] = param1[1];
            i++;
        }
    });

    var _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    $rootScope.utf8_decode = function (utftext) {	
        var string = "";	
        var i = 0;	
        var c = 0;	
        var c1 = 0;	
        var c2 = 0;	
        var c3 = 0;	
        while (i < utftext.length) {	
            c = utftext.charCodeAt(i);	
            if (c < 128) {	
                string += String.fromCharCode(c);	
                i++;	
            }	
            else if ((c > 191) && (c < 224)) {	
                c2 = utftext.charCodeAt(i + 1);	
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));	
                i += 2;	
            }	
            else {	
                c2 = utftext.charCodeAt(i + 1);	
                c3 = utftext.charCodeAt(i + 2);	
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));	
                i += 3;	
            }	
        }	
        return string;	
    }

    $scope.testFirstCharacter = function () {
        // var rootScope = angular.element($("#ranking_page")).scope().$root;
        $rootScope.displayUrlError = 0;
        $('#display_domain_error').hide();
        var lbl_outter_tfn_url_new  = $("input[name='refine-filter-options']:checked"). val();
        var search_text             = $('#searchByTfnUrl').val();
        if(lbl_outter_tfn_url_new == 'url'){
            if (search_text.substr(0, 1) == "." && search_text == '.com') {
                $rootScope.displayUrlError = 1;
                $('#display_domain_error').show();
            } else {
                if(search_text == 'com') {
                    $rootScope.displayUrlError = 1;
                    $('#display_domain_error').show();
                } else {
                    $rootScope.displayUrlError = 0;
                    $('#display_domain_error').hide();
                }
            }
        }
    }

    $scope.show_user_account = function() {
        // var my_network_called = $rootScope.my_network_called = sessionStorage.my_network_called = 0;

        $("#rank a").removeClass('top-menu-active');
        $("#networks a").removeClass('top-menu-active');
        $("#my_reports a").removeClass('top-menu-active');
        $("#track a").removeClass('top-menu-active');
    }

    $rootScope.redirect_ranking_page = function (scope) {
        var my_network_called = $rootScope.my_network_called = sessionStorage.my_network_called = 0;
        sessionStorage.selectDate = 1;
        sessionStorage.on_network = 0;
        $rootScope.set_header_once = 0;
        $('#uiview').css('overflow', '');
        $('#save_filter_ranking').removeClass('disabled');
        $('#save_filter_network').removeClass('disabled');
        sessionStorage.is_adv_page = 0;
        $("#rank a").addClass('top-menu-active');
        // $('#choose-networks-modal').hide();
        $('#breadcrumb_bafe').show();
        $('#main-filter').show();
        $('#ranking_page').show();
      
        // $('#categories_list').show();
        $('#network_grid_div').hide();
        $('#nw_grid_section').hide();
        $('.not_req_net').show();
        if (!$("#media_lifetime").is(':checked')) {
            $("#network_date_reset_error").hide();
        }
        $rootScope.showTab($rootScope.type, 0, 1);
        $('.main-section').show();
        if($scope.showme) {
            $('.directories').removeClass('mob-menu-active');
            $("#mob_directories").hide();
        } else {
            $("#directories").hide();
            $('.directories').removeClass('top-menu-active');
        }
        $("#adv_page").hide();
        $('.apply-filter-btn').css('padding-left', '1em');
        //window.location.href = "/drmetrix/ranking";

        //set empty text for global search and hide page
        $("#globalSearchInputText").val('');
        $("#global_search_list").hide();

        //show lifetime option and remove 365 days limit from filters for ranking
        $('.lifetime').css('visibility', 'visible');
        $rootScope.initialise_datepicker();
        $("#cal_header").text("");
        //$('#apply-filter').removeClass('disabled');
        $('.breakTypeFilter .filters-default').removeClass('break-type-class');
        gridUnload();
        $("#date_reset_error").hide();
        // $("#lifetime_error").hide();
        $('.ranking-grid').css('margin-top', '0');

    }

    $rootScope.menuItemClick = function (page, item) {
        if(page == 'Home'){
            $rootScope.redirect_ranking_page();
        }

        if(page == 'Networks'){
            $rootScope.setParameter();
        }

        if(page == 'Reports'){
            var scope = angular.element($("#ranking_page")).scope();
            scope.callMyReports();
        }
        if(page == 'User'){
            $scope.show_user_account();
        }
        if(page == 'Network List'){
            create_network_pdf_page();
        }
        if(page == 'Logout'){
            $scope.logout();
        }
        if(page == 'Theme'){
            changeTheme(item);
        }
    }

    $rootScope.setParameter = function (scope) {
        $('#ranking_page').hide();
        $rootScope.network_modal_called = 1;
        $rootScope.network_category_section = 0; // hide
        var my_network_called = $rootScope.my_network_called = sessionStorage.my_network_called = 1;
        sessionStorage.on_network = 1;
        if (sessionStorage.tracking == 1) {
            //$scope.network_redirect();
            $state.go('ranking');
            return;
        }
        if ($rootScope.hit_adv_page) {
            $rootScope.manage_header_reset_values();
            $rootScope.hit_adv_page = 0;
        }

        $('#save_filter_ranking').removeClass('disabled');
        $('#save_filter_network').removeClass('disabled');
        sessionStorage.is_adv_page = 0;
        if($scope.showme) {
            $('.my_networks').addClass('mob-menu-active');
            $('.directories').removeClass('mob-menu-active');
            $("#mob_directories").hide();
        } else {
            $('.my_networks').addClass('top-menu-active');
            $('.directories').removeClass('top-menu-active');
            $("#directories").hide();
        }
        $('.my_reports .deactivate').css('display', 'block');
        $('.my_reports .activate').css('display', 'none');
        $('#network_grid_div').hide();
        $('#nw_grid_section').hide();
        $('#breadcrumb_bafe').hide();
        $('#main-filter').hide();
        $("#network_main_div").addClass("network-page-wrapper");
        $("#my_network_war_mssg").css('display', 'block');
       
        // $('#categories_list').hide();
        $('.not_req_net').hide();
        $("#airing_network").hide();
        $("#bar_graph").hide();
        $('#detail_daypart').hide();
        $("#main_container").show();
        $('.apply-filter-btn').css('padding-left', '1em');
        // $('.main-section').show();
        $("#adv_page").hide();
        $scope.lifetime_error = 0;
        // $("#lifetime_error").hide();
        $("#date_reset_error").hide();
        $scope.network_apply_filter = 0;
        // $('#network_apply_filter').removeClass('disabled');
        $("#btn_exp_brand").removeClass("disabled");
        $("#brand_selection_limit_error").hide();
        //set flag to know call for choose-networks from where
        $rootScope.call_from = sessionStorage.call_from = "ranking";

        //set empty text for global search and hide page
        $("#globalSearchInputText").val('');
        $("#global_search_list").hide();
        var currentButton = angular.element(document.getElementById('choose-networks'));
        setTimeout(function () {
            currentButton.triggerHandler("click");
        }, 500);
        //$('#choose-networks').trigger( "click" );

        //hide lifetime option and added 365 days limit for filters for Network
        $('.lifetime').css('visibility', 'hidden');
        $rootScope.initialise_365datepicker();
        $("#cal_header").text("Note: Please select date range within 365 days");
        gridUnload();
    }

    $scope.network_redirect = function () {
        //sessionStorage.tracking = 0;
        $state.go('ranking');
        setTimeout(function () {
            $rootScope.setParameter(); // (1)
        }, 500);
    }
    /***Start - My report code starts here By Ashwini**/

    function removeExtension(filename) {
        var lastDotPosition = filename.lastIndexOf(".");
        if (lastDotPosition === -1) return filename;
        else return filename.substr(0, lastDotPosition);
    }

    $scope.showSharedReports = function (sharedReport) {
        $("#list4").jqGrid("editCell", 0, 0, false);
        $rootScope.sharedReport = sharedReport;
        if (copyFlag == 1 && sharedReport == 'My') {
            $scope.jqgridMyReportsData(sessionStorage.login_user_id);
            copyFlag = 0;
            // return;
        }
        var selected_user = $('#my_report_users option:selected').attr('id');
        var user_id = sessionStorage.login_user_id;
        var manageOr = $("#list4");
        var rules = [], i, cm, postData = manageOr.jqGrid("getGridParam", "postData"),
            colModel = manageOr.jqGrid("getGridParam", "colModel"),
            l = colModel.length;

        // var shared_by_val = '';;
        var operator = 'eq';
        var group_operator = 'AND';
        var sortColumnName = 'created';
        var cellEditColumn = true;
        if ($rootScope.sharedReport == 'Shared') {
            cellEditColumn = false;
            if (selected_user == sessionStorage.login_user_id) {
                cellEditColumn = true;
            }
            sortColumnName = 'shared_date';

            if (selected_user != undefined) {
                rules.push(
                    { field: 'shared_by', op: "eq", data: selected_user }
                );
            } else {
                rules.push(
                    { field: 'shared_by', op: "ne", data: ' ' }
                );
            }

        } else if ($rootScope.sharedReport == 'My') {
            group_operator = "OR";
            rules.push(
                { field: "shared_by", op: "eq", data: " " },
                { field: "shared_by", op: "eq", data: sessionStorage.login_user_id }
            );
        } else {

            if (selected_user != undefined && selected_user != sessionStorage.login_user_id) {
                cellEditColumn = false;
                rules.push(
                    { field: 'shared_by', op: "eq", data: selected_user }
                );
            } else {
                if (selected_user == undefined) {
                    rules = [];
                    cellEditColumn = false;
                } else {
                    cellEditColumn = true;
                    group_operator = "OR";
                    rules.push(
                        { field: "shared_by", op: "eq", data: " " },
                        { field: "shared_by", op: "eq", data: sessionStorage.login_user_id }
                    );
                }
            }
        }

        postData.filters = JSON.stringify({
            groupOp: group_operator,
            rules: rules
        });

        // manageOr.jqGrid("setGridParam", { search: true,sortname: sortColumnName, sortorder: 
        // 'desc' });

        manageOr.jqGrid("setGridParam", {
            search: true, sortname: sortColumnName, sortorder:
                'desc', cellEdit: cellEditColumn
        }).trigger("reloadGrid", [{
            groupOp: group_operator,
            rules: rules,
            page: 1
        }]);
        changeColor($.cookie("theme"));
        return false;
    };

    $scope.myreports = {};
    var listTable = $("#list4");
    $scope.jqgridMyReportsData = function (userId) {
        var sharedReportColumn = {
            name: 'shared_report',
            index: 'shared_report',
            sortable: false,
            width: 120,
            resizable: false,
            autoencode: true,
            edittype:'checkbox',
            formatter: function(cellvalue, options) {
                var id = options.rowId;
                return id ?
                       '<span class="editable" data-id="' + id  + '">' + cellvalue + '</span>' :
                       cellvalue;
            },
            align: 'center'
        };

        var createdColumn = {
            name: 'created',
            index: 'created',
            sorttype: 'date',
            width: 120,
            sortable: true,
            resizable: false,
            align: 'center',
            formatter: 'date',
            formatoptions: { srcformat: "m/d/Y", newformat: "m/d/Y" }
        };

        var validTillColumn = {
            name: 'valid_till',
            index: 'valid_till',
            sorttype: 'date',
            width: 120,
            sortable: true,
            resizable: false,
            align: 'center',
            formatter: 'date',
            formatoptions: { srcformat: "m/d/Y", newformat: "m/d/Y" }
        };

        var sharedValidTillColumn = {
            name: 'shared_valid_till',
            index: 'shared_valid_till',
            sorttype: 'date',
            width: 120,
            sortable: true,
            resizable: false,
            align: 'center',
            formatter: 'date',
            formatoptions: { srcformat: "m/d/Y", newformat: "m/d/Y" }
        };

        var filePathColumn = {
            name: 'file_path',
            index: 'file_path',
            width: 150,
            formatter:'select',
            hidden: true
        }

        var sharedByColumn = {
            name: 'shared_by',
            index: 'shared_by',
            hidden: true,
            sortable: false,
            width: 150,
            resizable: false,
            formatter:'select',
            align: 'center'
        };

        var sharedDateColumn = {
            name: 'shared_date',
            index: 'shared_date',
            sorttype: 'date',
            width: 120,
            sortable: true,
            resizable: false,
            align: 'center',
            formatter: 'date',
            formatoptions: { srcformat: "m/d/Y", newformat: "m/d/Y" }
        };

        var copyToMyReports = {
            name: 'copy_to_my_reports',
            index: 'copy_to_my_reports',
            width: 160,
            sortable: false,
            resizable: false,
            formatter:'select', 
            align: 'center'
        };

        var userIdColumn = {
            name: 'user_id',
            index: 'user_id',
            width: 150,
            hidden: true,
            sortable: false,
            resizable: false,
            align: 'center'
        };

        var colNames = ['status', 'User', 'File Name', 'File Size', 'Download link', 'Email Alert', 'Copy to My Reports', 'Share Report', 'Created', 'Shared Date', 'Valid Till', 'Valid Till', 'File Path', '', ''];

        var colModel = [
            // {
            //     name: 'id',
            //     index: 'id',
            //     hidden: true
            // },
            {
                name: 'status',
                index: 'status',
                hidden: true,
                // width: 50,
            },
            {
                name: 'full_name',
                index: 'full_name',
                width: 150,
                sortable: true,
                formatter:'select',
                resizable: false,
            },
            {
                name: 'file_name',
                index: 'file_name',
                width: 600,
                sortable: false,
                resizable: false,
                formatter: function(cellvalue, options) {
                    var id = options.rowId;
                    return id ?
                           cellvalue :
                           cellvalue;
                },
                align: 'left',
                editable: true,
                editoptions: { maxlength: 200 },
                editrules: {
                    custom: true,
                    custom_func: checkForDuplicates
                }
            }, {
                name: 'filesize',
                index: 'filesize',
                width: 120,
                sortable: false,
                resizable: false,
                align: 'center'
            }, {
                name: 'download_link',
                index: 'download_link',
                width: 180,
                sortable: false,
                resizable: false,
                formatter: function(cellvalue, options) {
                    var id = options.rowId;
                    return id ?
                           cellvalue :
                           cellvalue;
                },
                align: 'center'
            }, {
                name: 'email_alert',
                index: 'email_alert',
                width: 150,
                sortable: false,
                resizable: false,
                formatter:'select',
                align: 'center',
                autoencode: true,	
                edittype:'checkbox',	
                formatter: function(cellvalue, options) {	
                    var id = options.rowId;	
                    return id ?	
                        '<span class="editable" data-id="' + id  + '">' + cellvalue + '</span>' :	
                        cellvalue;	
                },
            },
        ];

        colModel.push(copyToMyReports);
        colModel.push(sharedReportColumn);
        colModel.push(createdColumn);
        colModel.push(sharedDateColumn);
        colModel.push(validTillColumn);
        colModel.push(sharedValidTillColumn);
        colModel.push(filePathColumn);
        colModel.push(sharedByColumn);
        colModel.push(userIdColumn);


        $("#list4").GridUnload();
        var listTable = $("#list4");
        var email_alert = $("#email_alert").val();
        var sortColumnName;
        var cellEditColumn;
        if ($rootScope.sharedReport == 'My') {
            sortColumnName = 'created';
            cellEditColumn = true;
        } else {
            sortColumnName = 'shared_date';
            cellEditColumn = false;
        }
        listTable.jqGrid({
            url: '/drmetrix/api/index.php/get_my_reports_data',
            datatype: "json",
            loadonce:true,
            search:true,
            formatter:'select',
            // forceClientSorting: true,
            postData: { 'userId': userId, 'email_alert': email_alert, 'shared_by': sessionStorage.login_user_id },
            // filters: JSON.stringify({
            //     groupOp: "OR",
            //     rules: [
            //         { field: "shared_by", op: "eq", data: " " },
            //         { field: "shared_by", op: "eq", data: sessionStorage.login_user_id }
            //     ]
            // })
            // },


            mtype: "POST", // method  , by default get          
            colNames: colNames,
            colModel: colModel,
            prmNames: {
                oper: 'Action', deloper: 'Delete', id: 'userId'
            },
            height: 'auto',
            autowidth: true,
            viewrecords: false,

            sortorder: "desc",
            caption: 'List of excel reports',
            toppager: false,
            multiselect: true,
            rowNum: 10,
            subGrid: false,
            sortname: sortColumnName,
            editable:true,
            pager: '#pager9',
            cellEdit: cellEditColumn,
            cellsubmit: 'clientArray',
            afterSaveCell: function (rowid, name, val, iRow, iCol) {
                if (name == 'file_name') {
                    var changedFileName = $("#list4").jqGrid('getCell', rowid, 'file_name');
                    // var changedFileName = jQuery("#list4").jqGrid('getCell', rowid, 'File Name').attr("val");
                    changedFileName   = val.replace(/%/g, '');
                    if (duplicate == 0) {
                        $("#list4").jqGrid('setCell', rowid, 'file_name', changedFileName + "." + extension);
                    } else {
                        $("#list4").jqGrid('setCell', rowid, 'file_name', lastSelectedName + "." + extension);
                    }
                    var rowData = $("#list4").jqGrid("getRowData", rowid);
                }
            },
            afterEditCell: function (rowid,name,val,iRow,iCol){
            },
            formatCell: function (rowid, name, val, iRow, iCol) {
                lastSel = rowid;
                extension = val.substr(val.lastIndexOf('.') + 1);
                lastSelectedName = val.split('.').slice(0, -1).join('.');
                return removeExtension(val);
            },
            success: function (data) {
                sessionStorage.report_data = data;
                setTimeout(function () {
                    $scope.modal.deactivate();
                }, 2000);
            },
            loadComplete: function () {
                var gridDOM = this; // save $("#list")[0] in a variable
                if ($(this).jqGrid('getGridParam', 'datatype') === 'json') {
                    // the first load from the server
                    setTimeout(function () {
                        $scope.showSharedReports($rootScope.sharedReport);
                    }, 100);
                }
                var selected_user = $('#my_report_users option:selected').attr('id');
                my_report_grid_data = 1;
                if ($('.delete_file').length == 0) {
                    var html = '<a title="delete" class="delete_file ptr_event" id="delete_file"><img class="header-img" src="/drmetrix/assets/img/delete-icon-grey.svg"></a>';
                    $("#cb_list4").after(html);
                } else {
                    $('.delete_file').find('img').attr('src', '/drmetrix/assets/img/delete-icon-grey.svg');
                }
                if (listTable.getGridParam('records') === 0) {
                    $('#cb_list4').attr('disabled', 'disabled');
                    // $('#list4 tbody tr td:nth-child(4)').html("<div class='no_records' style='text-align: center;color:#dc202a;padding : 10px; font-size: 12px;'>No records found</div>");
                    $('#advancedModalReport .ui-jqgrid-bdiv > div > div').html("<div class='no_record_report' style='padding: 1em 0; text-align: center;color:#dc202a; background-color: #fff; font-size: 12px;'>No records found.</div>");
                    $('.ui-pg-table').addClass('hide-pagination');
                } else if ($rootScope.sharedReport == 'All' || $rootScope.sharedReport == 'Shared') {
                    $('.no_record_report').remove();
                    $('#list4').trigger("reloadGrid");
                    if (selected_user != sessionStorage.login_user_id) {
                        // $('#list4_cb').css('visibility','hidden');
                        // $('td[aria-describedby="list4_cb"]').css('visibility','hidden');
                        $('#list4').hideCol('cb');
                        // $("[id^='jqg_list4_']").attr('disabled', 'disabled');
                        $('#cb_list4').attr('disabled', 'disabled');
                        $('#delete_file').hide();
                    } else {
                        // $('#list4_cb').css('visibility','visible');
                        // $('td[aria-describedby="list4_cb"]').css('visibility','visible');
                        $('#list4').showCol('cb');
                        // $("[id^='jqg_list4_']").attr('disabled', false);
                        $('#cb_list4').attr('disabled', false);
                        $('#delete_file').show();
                        $('.no_records').remove();
                        $('.ui-pg-table').removeClass('hide-pagination');
                    }
                } else {
                    $('.no_record_report').remove();
                    // $('#list4_cb').css('visibility','visible');
                    // $('td[aria-describedby="list4_cb"]').css('visibility','visible');
                    $('#list4').showCol('cb');
                    // $('#list4_cb').show()
                    // $("[id^='jqg_list4_']").attr('disabled', false);
                    $('#cb_list4').attr('disabled', false);
                    $('#delete_file').show();
                }
                var $grid = $("#list4"),
                    newWidth = $grid.closest(".ui-jqgrid").parent().width();
                $grid.jqGrid("setGridWidth", (newWidth - 80), true);
            },
            onSelectRow: function (id) {
                var s_row = $("#list4").jqGrid('getGridParam', 'selarrrow');
                var a_row = $('#list4').jqGrid('getDataIDs');

                if (s_row.sort(function (a, b) { return a - b }).join() ==
                    a_row.sort(function (a, b) { return a - b }).join()) {
                    $('#cb_list4').prop('checked', true);
                    $('.delete_file').removeClass('ptr_event');
                }
            },
            onPaging: function (pgButton) {
                $('.delete_file').attr('onClick', '');
                $('.delete_file').addClass('ptr_event');
            },
            gridComplete: function () {
                var recs = $('#list4').getGridParam("records");
                var $pager = {'pager_id' : 'pager9' ,'limit': 10 , 'current_page_count':recs};
                $scope.setPaginationBar('list4', $pager);
                var inprogress_rowid = new Array();
                $("#my_report_page").show();
                $("#uiview").animate({ scrollTop: 0 }, 500);
                $("#list4_cb").css("width", "60px");
                $("#list4 tbody tr").children().first("td").css("width", "60px");
                if ($rootScope.sharedReport == 'Shared') {
                    listTable.hideCol("created");
                    listTable.showCol("shared_date");
                    listTable.hideCol("valid_till");
                    listTable.showCol("shared_valid_till");
                } else {
                    listTable.showCol("created");
                    listTable.hideCol("shared_date");
                    listTable.showCol("valid_till");
                    listTable.hideCol("shared_valid_till");
                }

                if ($rootScope.sharedReport == 'All') {
                    $('.my-report-section').show();
                    listTable.showCol("email_alert");
                    listTable.showCol("copy_to_my_reports");
                    listTable.showCol("full_name");
                } else if ($rootScope.sharedReport == 'Shared') {
                    $('.my-report-section').show();
                    listTable.hideCol("email_alert");
                    listTable.showCol("copy_to_my_reports");
                    listTable.showCol("full_name");
                } else {
                    $('.my-report-section').hide();
                    listTable.showCol("email_alert");
                    listTable.hideCol("copy_to_my_reports");
                    listTable.hideCol("full_name");
                }
                inprogress_rowid = checkCellLoader();
                // reload progress cell value
                if (inprogress_rowid.length != 0) {
                    refresh_my_report = setInterval(function () {
                        if ($('[custom-div-id="my_report_modal"]').hasClass('is-active')) {
                            callProgressBar = 1;
                            $.ajax({
                                type: 'POST',
                                url: '/drmetrix/api/index.php/get_inprogress_data',
                                dataType: "json",
                                data: {
                                    id: inprogress_rowid,
                                },
                                success: function (data) {
                                    if (data.status) {
                                        var html_percentage, html_email, email_alert_checked;
                                        $.each(data.return_arr, function (key, value) {
                                            inprogress_rowid = checkCellLoader();

                                            if (value.email_alert == 1) {
                                                email_alert_checked = 'checked = checked';
                                            } else {
                                                email_alert_checked = '';
                                            }
                                            if (value.status == 'completed') {
                                                html_percentage = value.download_link;

                                                html_email = '<span><input type="checkbox"  disabled=disabled  id="email_alert_' + key + '" name="email_alert" class="email_alert checkbox-custom" onclick="updateEmailAlerts(' + key + ');" ' + email_alert_checked + '><label class="checkbox-custom-label other-opa"></label></span>';
                                                $("#list4").jqGrid("setCell", key, "download_link", html_percentage);
                                                $("#list4").jqGrid("setCell", key, "email_alert", html_email);
                                                $("#list4").jqGrid("setCell", key, "filesize", value.filesize);
                                            } else {
                                                html_percentage = '<div class="c100 p' + value.progress + ' small"><span>' + value.progress + '%</span><div class="slice"><div class="bar"></div><div class="fill"></div></div></div>';
                                                html_email = '<span><input type="checkbox"   id="email_alert_' + key + '" name="email_alert" class="email_alert checkbox-custom" onclick="updateEmailAlerts(' + key + ');" ' + email_alert_checked + '><label class="checkbox-custom-label"></label></span>';
                                                $("#list4").jqGrid("setCell", key, "download_link", html_percentage);
                                                $("#list4").jqGrid("setCell", key, "email_alert", html_email);
                                                $("#list4").jqGrid("setCell", key, "filesize", value.filesize);
                                            }
                                            $('#email_alert_'+ key).attr('title','');	
                                            $('#email_alert_'+ key).parent().closest('td').attr('title','');	
                                        });
                                    }
                                },
                                error: function (xhr, status, error) {
                                    console.log('Error');
                                }
                            });
                        } else {
                            callProgressBar = 0;
                            clearTimeout(refresh_my_report);
                        }
                    }, 10000);
                } else {
                    callProgressBar = 0;
                    clearTimeout(refresh_my_report);
                }
                commonFunctionsForReportsAndFilters('list4');
                var not_checked = 0;
                var checked = 0
                var element_len = 0;

                //function for invidual delete check and uncheck element.
                var checkbox_id;
                $("td[aria-describedby='list4_cb']").each(function () {
                    element_len++;
                    $(this).on("click", function (e) {
                        checkbox_id = $(this).find('input').attr('id');
                        var row_id = checkbox_id.replace("jqg_list4_", "");
                        if ($('#' + checkbox_id).is(':checked')) {
                            $("#list4").jqGrid("editCell", 0, 0, false);
                            $('.delete_file').find('img').attr('src', '/drmetrix/assets/img/delete-icon-red.svg');
                            checked_row_id_array.push(row_id);
                            $('.delete_file').attr('onClick', 'setPopupId()');
                            $('#' + row_id).addClass('bg-pink');
                            $('#' + checkbox_id).attr('checked', 'checked');
                        } else {
                            $("#list4").jqGrid("editCell", 0, 0, false);
                            $('#' + row_id).removeClass('bg-pink');
                            checked_row_id_array.splice($.inArray(row_id, checked_row_id_array), 1);
                            $('.delete_file').attr('onClick', 'setPopupId()');

                        }
                        $('.delete_file').removeClass('ptr_event');
                        checked = $('input[name^="jqg_list4_"]:checked').length;
                        not_checked = element_len - checked;

                        if (element_len == not_checked) {
                            $('.delete_file').find('img').attr('src', '/drmetrix/assets/img/delete-icon-grey.svg');
                            $('.delete_file').attr('onClick', '');
                            $('.delete_file').addClass('ptr_event');
                        }

                    });
                });


                // function for delete all check and delete all uncheck
                var all_row_id_array = new Array();
                $("#cb_list4").on("click", function (e) {
                    if ($('#cb_list4').is(':checked')) {
                        $('.delete_file').find('img').attr('src', '/drmetrix/assets/img/delete-icon-red.svg');
                        $('.delete_file').removeClass('ptr_event');
                        $("td[aria-describedby='list4_cb']").each(function () {
                            var checkbox_id = $(this).find('input').attr('id');
                            var row_id = checkbox_id.replace("jqg_list4_", "");
                            $('#' + row_id).addClass('bg-pink');
                            all_row_id_array.push(row_id);
                            $('.delete_file').attr('onClick', 'setPopupId()');
                        });
                    } else {
                        $('.delete_file').find('img').attr('src', '/drmetrix/assets/img/delete-icon-grey.svg');
                        $('.delete_file').addClass('ptr_event');
                        $("td[aria-describedby='list4_cb']").each(function () {
                            var checkbox_id = $(this).find('input').attr('id');
                            var row_id = checkbox_id.replace("jqg_list4_", "");
                            $('#' + row_id).removeClass('bg-pink');
                            all_row_id_array.splice($.inArray(row_id, all_row_id_array), 1);
                            $('.delete_file').attr('onClick', '');
                        });

                    }

                });
            },
        });


        var cm = listTable[0].p.colModel;
        $.each(listTable[0].grid.headers, function (index, value) {
            var cmi = cm[index], colName = cmi.name;
            if (!cmi.sortable && colName == 'report_type' || colName == 'advertiser_name' || colName == 'brand_name' || colName == 'creative_name' || colName == 'filters_used' || colName == 'file_name' || colName == 'filesize' || colName == 'email_alert' || colName == 'shared_report' || colName == 'category' || colName == 'download_link' || colName == 'delete_file' && colName !== 'subgrid') {
                $('div.ui-jqgrid-sortable', value.el).css({ cursor: "default" });
            }
        });
    }



    var duplicate = 0;
    function checkForDuplicates(changedFileName, colName, rowId, id) {
        var saveReportForId = lastSel;
        duplicate = 0;
        //   changedFileName = changedFileName.trim();
        $('[aria-describedby="list4_file_name"]').removeClass("error-field");
        $('#duplicate-report-error').css('display', 'none');

        var rowData = $("#list4").jqGrid("getRowData", saveReportForId);

        if (rowData.download_link.indexOf('Download') == -1) {
            duplicate = 1;

            $('#' + saveReportForId + ' > [aria-describedby="list4_file_name"]').addClass("error-field");
            $('#duplicate-report-error').text('Please wait until your file is finished downloading.');
            $('#duplicate-report-error').css('display','block');
            setTimeout(function () {
                $('[aria-describedby="list4_file_name"]').removeClass("error-field");
                $('#duplicate-report-error').css('display', 'none');
            }, 6000);

            return [true, ""];
        }

        var checkSplChar = cleanFileName(changedFileName);
        //   changedFileName     = changedFileName.replace(/%/g, '');
        //   changedFileName     = changedFileName.replace("'", "");
        //changedFileName = changedFileName.replace(/[_\W]+/g, "");
        var count_start_brackets = changedFileName.split("{").length;

        var count_end_brackets = changedFileName.split("}").length;

        if (changedFileName == '' || count_start_brackets > 1 || count_end_brackets > 1 || checkSplChar == 1) {
            duplicate = 1;
            $('#' + saveReportForId + ' > [aria-describedby="list4_file_name"]').addClass("error-field");
            $('#duplicate-report-error').text('Invalid File name.');
            $('#duplicate-report-error').css('display', 'block');
            setTimeout(function () {
                $('[aria-describedby="list4_file_name"]').removeClass("error-field");
                $('#duplicate-report-error').css('display', 'none');
            }, 6000);

            return [true, ""];
        }

        if (lastSelectedName != changedFileName) {
            $.each(cachedReportsData, function (key, value) {
                if((saveReportForId !== value.rowid) && (value.check_for_file.toLowerCase() == changedFileName.toLowerCase())){
                    duplicate = 1;
                }
            });
            if (duplicate == 0) {
                var rowData = $("#list4").jqGrid("getRowData", saveReportForId);

                $.post('/drmetrix/api/index.php/save_inline_edit_report_data', {
                    'file_name': changedFileName,
                    'rowid': saveReportForId,
                    'file_path': rowData.file_path
                }, function (data, status) {
                    $.each(cachedReportsData, function (key, value) {
                        if (value.rowid == saveReportForId) {
                            value.check_for_file = changedFileName;
                        }
                    });
                });

                return [true, ""];
            } else {
                $('#' + saveReportForId + ' > [aria-describedby="list4_file_name"]').addClass("error-field");
                $('#duplicate-report-error').text('File name already exists.');
                $('#duplicate-report-error').css('display', 'block');
                setTimeout(function () {
                    $('[aria-describedby="list4_file_name"]').removeClass("error-field");
                    $('#duplicate-report-error').css('display', 'none');
                }, 6000);

                return [true, ""];
            }
        } else {
            return [true, ""];
        }
    }

    $scope.callMyReports = function () {
        sessionStorage.show_reports_progress = 1;
        //var my_network_called = $rootScope.my_network_called = sessionStorage.my_network_called = 0;
        if ($('#user_account').hasClass('top-menu-active') ||$('#mob_user_account').hasClass('mob-menu-active')) {
            // $('#ranking .deactivate').css('display','block');
            // $('#ranking .activate').css('display','none');
            if($scope.showme) { 
                $('.ranking').removeClass('mob-menu-active');
                $('.my_reports').addClass('mob-menu-active');
            } else {
                $('.ranking').removeClass('top-menu-active');
                $('.my_reports').addClass('top-menu-active');
            }
            $('.my_reports .deactivate').css('display', 'none');
            $('.my_reports .activate').css('display', 'block');

        } else {
            // $('#ranking .deactivate').css('display','block');
            // $('#ranking .activate').css('display','none');
            $('.my_reports .deactivate').css('display', 'none');
            $('.my_reports .activate').css('display', 'block');
           

            if($scope.showme) { 
                $('.my_reports').removeClass('mob-menu-active');
                $('.ranking').removeClass('mob-menu-active');
                $('.directories').removeClass('mob-menu-active');
                $('.my_networks').removeClass('mob-menu-active');
                $('.tracking').removeClass('mob-menu-active');
            } else {
                $('.my_reports').addClass('top-menu-active');
                $('.ranking').removeClass('top-menu-active');
                $('.directories').removeClass('top-menu-active');
                $('.my_networks').removeClass('top-menu-active');
                $('.tracking').removeClass('top-menu-active');
            }
        }
        var userId = sessionStorage.loggedInUserId;
        $('#switchOneSharedReport').attr('checked', false);
        $scope.getAllReportData();
        $rootScope.sharedReport = 'My';
        $scope.getUser(userId);
        $scope.getActiveSharedUsers('reports');
    }

    $scope.getActiveSharedUsers = function (page) {
        apiService.post('/show_active_shared_users', { 'page': page })
            .success(function (data) {
                if (data.status) {
                    $rootScope.users = data.result;
                    $rootScope.users_count = data.count;
                    var users_html = '<option>All</option>';

                    $rootScope.users.forEach(function (element) {
                        users_html += '<option id="' + element.user_id + '">' + element.name + '</option>';
                    });

                    if (page == 'reports') {
                        $('#my_report_users').html(users_html);
                    } else if(page == 'list'){
                        $('.list_users').html(users_html);
                    } else {
                        $('.filter_users').html(users_html);
                    }
                }
            })
            .error(function (data, status, headers, config) {
                console.log("error inside");
            });
    }
    $scope.setMenusDeactivate = function () {
        $scope.whats_new = 0;
        $('#ranking .deactivate').css('display', 'block');
        $('#ranking .activate').css('display', 'none');
        $("#rank a").removeClass('active');
        $("#networks a").removeClass('active');
    }

    var cachedReportsData = [];
    $scope.getAllReportData = function () {
        $.ajax({
            type: 'POST',
            url: '/drmetrix/api/index.php/get_all_report_data',
            dataType: "json",
            //  async: false,
            success: function (data) {
                if (data.status) {
                    cachedReportsData = data.return_arr;
                }
            },
            error: function (xhr, status, error) {
                console.log('Error');
            }
        });
    }
    $scope.getUser = function (user_id) {

        if ($rootScope.export_click) {
            showProgressbar();
            $rootScope.export_click = false;
        } else {
            callProgressBar = 0;
        }

        if (callProgressBar_gridexcel) {
            showProgressbar();
            callProgressBar_gridexcel = false;
        }

        $.ajax({
            url: '/drmetrix/api/index.php/get_user',
            type: 'POST',
            dataType: "json",
            data: {
                user_id: user_id,
            },
            success: function (data) {
                if (data.status) {
                    $('#switchOne').val(data.result[0].excel_download_popup);
                    var excelVal = $('#switchOne').val();
                    if (excelVal == 1) {
                        $('#switchOne').prop("checked", true);
                    } else if (excelVal == 0) {
                        $('#switchOne').prop("checked", false);
                    }
                    if (my_report_grid_data == 0) {
                        $scope.jqgridMyReportsData(user_id);
                    } else {
                        $("#list4").jqGrid('GridUnload');
                        $scope.jqgridMyReportsData(user_id);
                    }
                }
            },
            beforeSend: function (data) {
                $("#my_report_page").hide();
            },
            error: function (xhr, status, error) {
                console.log('Error');
            }
        });
    }
    /***End - My report code starts here By Ashwini**/


    $rootScope.decode = function (input) {

        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = _keyStr.indexOf(input.charAt(i++));
            enc2 = _keyStr.indexOf(input.charAt(i++));
            enc3 = _keyStr.indexOf(input.charAt(i++));
            enc4 = _keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = $rootScope.utf8_decode(output);

        return output;
    }
    //20-5-2015 00:00:00 1463702400
    //MTQ2MzcwMjQwMA%3D%3D

    //30-5-2016 1464566400 MTQ2NDU2NjQwMA%3D%3D -10

    //10-5=2016 1462838400 MTQ2MjgzODQwMA%3D%3D 10

    //12-5-2016 1463011200 MTQ2MzAxMTIwMA%3D%3D
    var querystring_timestamp = decodeURIComponent($scope.getParameterByName('guid'));

    var querystring_timestamp = $rootScope.decode(querystring_timestamp);
    var current_timestamp = Math.round((new Date()).getTime() / 1000);
    var difference = current_timestamp - querystring_timestamp;
    var daysDifference = Math.floor(difference / 60 / 60 / 24);

    if (typeof $scope.getParameterByName('guid') != 'undefined') {
        sessionStorage.pdf = 1;
    }

    if (result.hasOwnProperty("video") || $stateParams['video'] == 1 || sessionStorage.pdf == 1) {
        // $scope.user = {loggedIn: true, notLoggedIn: false, year_of_copyrights: (new Date()).getFullYear()};
        sessionStorage.pdf = 1;
        sessionStorage.video = result.video;
    } else {
        // $scope.user = {loggedIn: false, notLoggedIn: true, year_of_copyrights: (new Date()).getFullYear()};
        sessionStorage.pdf = 0;
        sessionStorage.video = 0;
    }


    sessionStorage.monthArray = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];
    if (sessionStorage.sessionExpired) {
        $scope.user = {};
    } else {
        $scope.user = { loggedIn: false, notLoggedIn: true, year_of_copyrights: (new Date()).getFullYear(), pdf: sessionStorage.pdf, role: sessionStorage.role, checkIn: sessionStorage.checkIn };
    }



    $scope.homeUrl = function () {
        $state.go('ranking');
        return false;
    }

    $scope.clickedSomewhereElse = function () {
        $scope.user.search_result = 0;
    };

    $scope.search_result = function () {
        //clear search session
        sessionStorage.adv_search_brand = ['', ''];
        sessionStorage.adv_search_response = ['', ''];
        sessionStorage.adv_search_category = ['', ''];
        var search_str = $scope.user.search_str;
        if (($scope.user.search == 'text' && search_str.length > 2) || search_str.length > 5 /*&& $scope.user.search_result*/) {
            $rootScope.search = $scope.user.search;
            $rootScope.search_str = $scope.user.search_str;
            var ctype = $rootScope.ctype ? $rootScope.ctype : $scope.user.ctype;
            var search = $rootScope.search ? $rootScope.search : $stateParams.search;
            $state.go('searchResultByBrand', { search: search, search_str: search_str }, { reload: true, notify: true });
            $scope.user.search_result = 0;
            $scope.user.search_str = '';
            if ($scope.user.search == 'text') sessionStorage.vanity = 0;
        }
    }
    $scope.adv_search_result = function () {
        var adv_search_str = $scope.user.adv_search_str;
        var dis_resp = [];
        sessionStorage.adv_search_brand = ['', ''];
        sessionStorage.adv_search_response = ['', ''];
        sessionStorage.adv_search_category = ['', ''];

        var i = 0;
        $("input[name='brand_type[]']").each(function () {
            if ($(this).is(':checked')) {
                dis_resp[i] = $(this).val();
                i++;
            }

        });
        sessionStorage.adv_search_brand = dis_resp;

        i = 0;
        dis_resp = [];
        if (!$("#checkbox-all-res").is(':checked')) {
            $("input[name='response_type[]']").each(function () {
                if ($(this).is(':checked')) {
                    dis_resp[i] = $(this).val();
                    i++;
                }

            });
        }
        sessionStorage.adv_search_response = dis_resp;

        i = 0;
        dis_resp = [];
        if (!$("#checkbox-all-cat").is(':checked')) {
            $("input[name='checkbox-category[]']").each(function () {
                if ($(this).is(':checked')) {
                    dis_resp[i] = $(this).val();
                    i++;
                }

            });
        }
        sessionStorage.adv_search_category = dis_resp;


        $rootScope.search = $scope.user.search;
        $rootScope.adv_search_str = $scope.user.adv_search_str;
        var ctype = $rootScope.ctype ? $rootScope.ctype : $scope.user.ctype;
        var search = $rootScope.search ? $rootScope.search : $stateParams.search;
        search = search ? search : 'text';
        $rootScope.search_str = "";
        $state.go('searchResultByBrand', { search: search, search_str: adv_search_str }, { reload: true, notify: true });
        $scope.user.search_result = 0;
        $scope.user.adv_search_str = '';
        if ($scope.user.search == 'text') sessionStorage.vanity = 0;

        $scope.user.search_str = '';
        $(".res-dropdown").attr("style", "display:none");
        $(".brand-dropdown").attr("style", "display:none");
        $(".cat-dropdown").attr("style", "display:none");
        $(".response_type").prop("checked", true);
        $(".checkboxcategory").prop("checked", true);
        $(".brand_type").prop("checked", true);
        $("#checkbox-all-res").prop("checked", true);
        $("#checkbox-all-brn").prop("checked", true);
        $("#checkbox-all-cat").prop("checked", true);
        $(".res_dispay").html("All");
        $(".cat_dispay").html("All");
        $(".brn_dispay").html("All");
        FoundationApi.closeActiveElements('advancedModalSearch');
    }
    $scope.clearCategoryList = function () {

        $(".checkboxcategory").prop("checked", false);
        $(".cat_dispay").html("");
        $("#checkbox-all-cat").prop("checked", false);
        $(".category-ul").attr("style", "display:none");
    }
    $scope.selectCategoryList = function () {

        $(".checkboxcategory").prop("checked", true);
        $(".cat_dispay").html("All");
        $("#checkbox-all-cat").prop("checked", true);
        $(".category-ul").attr("style", "display:none");
    }
    $scope.closeAdvanceSearch = function () {

        $scope.user.search_str = '';
        $scope.user.adv_search_str = '';
        $(".res-dropdown").attr("style", "display:none");
        $(".brand-dropdown").attr("style", "display:none");
        $(".cat-dropdown").attr("style", "display:none");
        $(".response_type").prop("checked", true);
        $(".checkboxcategory").prop("checked", true);
        $(".brand_type").prop("checked", true);
        $("#checkbox-all-res").prop("checked", true);
        $("#checkbox-all-brn").prop("checked", true);
        $("#checkbox-all-cat").prop("checked", true);
        $(".res_dispay").html("All");
        $(".cat_dispay").html("All");
        $(".brn_dispay").html("All");
        FoundationApi.closeActiveElements('advancedModalSearch');
    }
    $scope.setRS = function (ctype, browse_type) {
        if (browse_type) $rootScope.browse_type = ctype;
        else $rootScope.browse_type = '';
        $rootScope.search = $scope.user.search ? $scope.user.search : $scope.user.search;
        $rootScope.search_str = $scope.user.search_str ? $scope.user.search_str : $rootScope.search_str;
        $scope.user.search_str = '';
        $scope.user.search_result = null;
        $rootScope.ctype = ctype ? ctype : $rootScope.ctype;
        if ($scope.user.search == 'text') sessionStorage.vanity = 0;
        FoundationApi.closeActiveElements('advancedModalSearch');
    }
    $scope.logout = function () {
        sessionStorage.selectDate = 1;
        apiService.showLoader($scope);
        apiService.post('/user_logout', $scope.user)
            .success(function (data) {
                if (data.status) {
                    sessionStorage.loggedIn = sessionStorage.category = sessionStorage.sub_category = '';
                    delete sessionStorage.cachedCategoriesData;
                    sessionStorage.category_id = sessionStorage.sub_category_id = 0;
                    sessionStorage.selectClassfication = 'All';
                    delete sessionStorage.role;
                    delete sessionStorage.admin;
                    delete sessionStorage.stored_data;
                    delete sessionStorage.contactemail;
                    delete sessionStorage.user_company;
                    delete sessionStorage.assistant_admin;
                    delete sessionStorage.nc_selected_array;
                    $rootScope.admin = 0;
                    $rootScope.assistant_admin = 0;
                    delete sessionStorage.superadmin;
                    $rootScope.superadmin = 0;
                    $scope.user.loggedIn = false;
                    sessionStorage.tracking = 0;
                    delete sessionStorage.tracking;
                    delete sessionStorage.all_networks_data;
                    delete sessionStorage.active_networks_data;
                    delete sessionStorage.activeNetwroksParams
                    $rootScope.setCookie("loggedIn", '', -1);
                    $rootScope.setCookie("allVals", '', -1);
                    $rootScope.setCookie("selected_networks", '', -1);
                    $rootScope.searchStr = '';
                    location.href = '/drmetrix/';
                }
                $scope.modal.deactivate();
                setTimeout(function () { $('.is-active').removeClass('is-active'); }, 0);
            })
            .error(function (data, status, headers, config) {
                clearInterval($rootScope.myLogin); // stop the interval
                $scope.modal.deactivate();
                setTimeout(function () { $('.is-active').removeClass('is-active'); }, 0);
                console.log('Errooor');
            })
    }


    //for mobile side menu
    $scope.menuCalled = function () {
        $scope.setImage('');
    }

    $scope.autoFill = function () {
        var d = new Date();
        var w = [1];
        var html = '';

        $rootScope.customMon = $rootScope.customMon ? $rootScope.customMon : d.getMonth() + 1;

        var customMon = $("#month_list :selected").val()

        if (customMon == undefined) {
            customMon = $rootScope.customMon;
        }

        $rootScope.currentYear = $rootScope.currentYear ? $rootScope.currentYear : d.getFullYear();

        var currentYear = $("#year_list :selected").val()

        if (currentYear == undefined) {
            currentYear = $rootScope.currentYear;
        }
        var week_selection = $rootScope.selectedCustomWeek ? $rootScope.selectedCustomWeek : 1

        $rootScope.selectedCustomWeek = 1;

        $('#week_select option:eq(0)').attr('selected', true);

    }

    $scope.setMonth = function (month) {
        $rootScope.customMon = month;
    }

    $scope.setYear = function (year) {
        $rootScope.selectedCustomYear = year;
    }

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
    $http({
        method: 'get',
        url: '/drmetrix/api/index.php/get_all_media_data'
       }).then(function successCallback(response) {
        // Store response data
        var data = response.data;

        var sd = data.last_week.sd;
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec"];
        var monthArray = { '01': 'Jan', '02': 'Feb', '03': 'Mar', '04': 'Apr', '05': 'May', '06': 'June', '07': 'July', '08': 'Aug', '09': 'Sept', '10': 'Oct', '11': 'Nov', '12': 'Dec' };

        var start_date = new Date(sd.replace(" ", "T"));
        var start_month = months[start_date.getMonth()];
        var start_day = start_date.getUTCDate();
        var year = start_date.getUTCFullYear();
        var last_week_start_date = start_month + ' ' + start_day;

        $rootScope.from_date = last_week_start_date;

        var ed = data.last_week.ed;
        var end_date = new Date(ed.replace(" ", "T"));
        var end_month = months[end_date.getMonth()];
        var end_day = end_date.getUTCDate();
        var year = end_date.getUTCFullYear();
        var last_week_end_date = end_month + ' ' + end_day;
        $rootScope.year = year;

        sessionStorage.media_start_date = data.last_week.start_date;
        sessionStorage.media_end_date = data.last_week.end_date;

        sessionStorage.media_start_db = data.last_week.sd;
        sessionStorage.media_end_db = data.last_week.ed;

        sessionStorage.week_calendar_id = data.last_week.calendar_id;

        sessionStorage.current_year = $scope.current_year = data.current_year;
        $scope.selectedYear = data.current_year;
        sessionStorage.lifetime_year = data.lifetime.year;
        sessionStorage.lifetime_min_sd = data.lifetime.start_date;
        sessionStorage.lifetime_max_ed = data.lifetime.end_date;
        sessionStorage.lifetime_db_min_sd = data.lifetime.start_date_db;
        sessionStorage.lifetime_db_max_ed = data.lifetime.end_date_db;

        //last month data
        var sd_mon = data.last_month.sd;
        var res = sd_mon.substring(5, 7);
        var start_mon = monthArray[res];
        sessionStorage.monthName = start_mon;

        sessionStorage.media_month_date = data.last_month.start_date;
        sessionStorage.media_monthend_date = data.last_month.end_date;
        sessionStorage.media_month_start_db = data.last_month.sd;
        sessionStorage.media_month_end_db = data.last_month.ed;
        sessionStorage.month_calendar_id = "(" + data.last_month.calendar_id + ")";

        //Current week data
        sessionStorage.current_start_date = data.current_week.start_date;
        sessionStorage.current_end_date = data.current_week.end_date;
        sessionStorage.current_start_db = data.current_week.sd;
        sessionStorage.current_end_db = data.current_week.ed;
        sessionStorage.current_calendar_id = data.current_week.calendar_id;
        $scope.current_week = data.current_week.calendar_id;

        //Current Month Data
        var sd_mon = data.current_month.sd;
        var res = sd_mon.substring(5, 7);
        var start_mon = monthArray[res];
        sessionStorage.currentmonthName = start_mon;

        sessionStorage.media_currentmonth_date = data.current_month.start_date;
        sessionStorage.media_currentmonthend_date = data.current_month.end_date;
        sessionStorage.media_currentmonth_start_db = data.current_month.sd;
        sessionStorage.media_currentmonth_end_db = data.current_month.ed;
        sessionStorage.currentmonth_calendar_id = "(" + data.current_month.calendar_id + ")";
        $scope.current_month = data.current_month.media_month_id;

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

    $scope.search = function () {
        $scope.user.search_vanity = 0;
        var search_str = $scope.user.search_str;
        if (search_str.length > 2) {
            var tfn = /^(\+?1)?(.|-|\()?(8(00|44|55|66|77|88)(\+1|\d)?(.|-|\))?(\d{1,3})?(.|-)?(\d{1,3})?(.|-)?(\d{1,4})?)$/;
            var vanity = /^(\+?1)?(.|-|\()?(8(00|44|55|66|77|88)(\+1|\d)?(.|-|\))?([A-Za-z0-9]{1,20})?(.|-)?([A-Za-z0-9]{1,20})?(.|-)?([A-Za-z0-9]{1,20})?)$/; //(\d{1,20})?
            var url = /^(([A-Za-z]{3,9}):\/\/)?([-;:&=\+\$,\w]+@{1})?(([-A-Za-z0-9]+\.)+[A-Za-z]{2,3})(:\d+)?((\/[-\+~%/\.\w]+)?\/?([&?][-\+=&;%@\.\w]+)?(#[\w]+)?)?$/;
            var tfn_search = tfn.test(search_str)
            var vanity_search = vanity.test(search_str)
            var url_search = url.test(search_str)
            if (tfn_search || vanity_search || url_search) {
                $scope.user.search_result = 0;
                if (search_str.length < 6) return;
                if (/*!tfn_search &&*/ !url_search) {
                    var replaceAll = function (string, find, replace) {
                        function escapeRegExp(string) {
                            return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
                        }
                        return string.replace(new RegExp(escapeRegExp(find), 'g'), replace);
                    }
                    var search_str_num = replaceAll(search_str, '-', '');
                    search_str_num = replaceAll(search_str, '.', '');
                    search_str_num = replaceAll(search_str, '(', '');
                    search_str_num = replaceAll(search_str, ')', '');
                    if (vanity_search) {
                        search_str = search_str.toUpperCase();
                        String.prototype.replaceAt = function (index, character) {
                            return this.substr(0, index) + character + this.substr(index + 1); //character.length -> 1
                        }
                        var vanity_letters = Array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
                        var vanity_numbers = Array(2, 2, 2, 3, 3, 3, 4, 4, 4, 5, 5, 5, 6, 6, 6, 7, 7, 7, 7, 8, 8, 8, 9, 9, 9, 9);
                        for (i = 0; i < search_str.length; i++) {
                            var charAt = search_str.charAt(i);
                            if (isNaN(charAt)) {
                                var vanitee = vanity_numbers[vanity_letters.indexOf(charAt)];
                                if (vanitee) search_str = search_str.replaceAt(i, vanitee);
                            }
                        }
                        $scope.user.search_vanity = search_str;
                        sessionStorage.vanity = search_str;
                    }
                }
                $scope.user.search = url_search ? 'url' : 'tfn';
                $scope.user.search_short = 0;
                $scope.user.searching = 1;
                $scope.user.short_result = {};
                apiService.post('/search_short', { 'search': $scope.user.search, 'search_str': search_str }) //$scope.user.search_str
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.searching = $scope.user.search_short = 0;
                            $scope.user.search_result = 1;
                            $scope.user.short_result = data.result;
                            $scope.user.short_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.searching = $scope.user.search_long = 1;
                $scope.user.long_result = {};
                apiService.post('/search_long', { 'search': $scope.user.search, 'search_str': search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.searching = $scope.user.search_long = 0;
                            $scope.user.search_result = 1;
                            $scope.user.long_result = data.result;
                            $scope.user.long_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.searching = $scope.user.search_brand = 1;
                $scope.user.brand_result = {};
                apiService.post('/search_brand', { 'search': $scope.user.search, 'search_str': search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.searching = $scope.user.search_brand = 0;
                            $scope.user.search_result = 1;
                            $scope.user.brand_result = data.result;
                            $scope.user.brand_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.searching = $scope.user.search_adv = 1;
                $scope.user.adv_result = {};
                apiService.post('/search_adv', { 'search': $scope.user.search, 'search_str': search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.searching = $scope.user.search_adv = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_result = data.result;
                            $scope.user.adv_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.searching = 0;
                        console.log('Errooor');
                    })

            } else {
                $scope.user.search = 'text';
                $scope.user.searching = $scope.user.search_short = 1;
                $scope.user.short_result = {};
                apiService.post('/search_text_short', { 'search': 'text', 'search_str': $scope.user.search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.searching = $scope.user.search_short = 0;
                            $scope.user.search_result = 1;
                            $scope.user.short_result = data.result;
                            $scope.user.short_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.searching = $scope.user.search_long = 1;
                $scope.user.long_result = {};
                apiService.post('/search_text_long', { 'search': 'text', 'search_str': $scope.user.search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.searching = $scope.user.search_long = 0;
                            $scope.user.search_result = 1;
                            $scope.user.long_result = data.result;
                            $scope.user.long_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.searching = $scope.user.search_brand = 1;
                $scope.user.brand_result = {};
                apiService.post('/search_text_brand', { 'search': 'text', 'search_str': $scope.user.search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.searching = $scope.user.search_brand = 0;
                            $scope.user.search_result = 1;
                            $scope.user.brand_result = data.result;
                            $scope.user.brand_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.searching = $scope.user.search_adv = 1;
                $scope.user.adv_result = {};
                apiService.post('/search_text_adv', { 'search': 'text', 'search_str': $scope.user.search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.searching = $scope.user.search_adv = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_result = data.result;
                            $scope.user.adv_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.searching = 0;
                        console.log('Errooor');
                    })
            }
        }

        $scope.setImage(''); // added by ashwini to resolve issue 4896.
    }

    $scope.adv_search = function () {
        $scope.user.search_vanity = 0;
        var adv_search_str = $scope.user.adv_search_str;
        if (adv_search_str.length > 2) {
            var tfn = /^(\+?1)?(.|-|\()?(8(00|44|55|66|77|88)(\+1|\d)?(.|-|\))?(\d{1,3})?(.|-)?(\d{1,3})?(.|-)?(\d{1,4})?)$/;
            var vanity = /^(\+?1)?(.|-|\()?(8(00|44|55|66|77|88)(\+1|\d)?(.|-|\))?([A-Za-z0-9]{1,20})?(.|-)?([A-Za-z0-9]{1,20})?(.|-)?([A-Za-z0-9]{1,20})?)$/; //(\d{1,20})?
            var url = /^(([A-Za-z]{3,9}):\/\/)?([-;:&=\+\$,\w]+@{1})?(([-A-Za-z0-9]+\.)+[A-Za-z]{2,3})(:\d+)?((\/[-\+~%/\.\w]+)?\/?([&?][-\+=&;%@\.\w]+)?(#[\w]+)?)?$/;
            var tfn_search = tfn.test(adv_search_str)
            var vanity_search = vanity.test(adv_search_str)
            var url_search = url.test(adv_search_str)
            if (tfn_search || vanity_search || url_search) {
                $scope.user.search_result = 0;
                if (adv_search_str.length < 6) return;
                if (/*!tfn_search &&*/ !url_search) {
                    var replaceAll = function (string, find, replace) {
                        function escapeRegExp(string) {
                            return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
                        }
                        return string.replace(new RegExp(escapeRegExp(find), 'g'), replace);
                    }
                    var search_str_num = replaceAll(adv_search_str, '-', '');
                    search_str_num = replaceAll(adv_search_str, '.', '');
                    search_str_num = replaceAll(adv_search_str, '(', '');
                    search_str_num = replaceAll(adv_search_str, ')', '');
                    if (vanity_search) {
                        adv_search_str = adv_search_str.toUpperCase();
                        String.prototype.replaceAt = function (index, character) {
                            return this.substr(0, index) + character + this.substr(index + 1); //character.length -> 1
                        }
                        var vanity_letters = Array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
                        var vanity_numbers = Array(2, 2, 2, 3, 3, 3, 4, 4, 4, 5, 5, 5, 6, 6, 6, 7, 7, 7, 7, 8, 8, 8, 9, 9, 9, 9);
                        for (i = 0; i < adv_search_str.length; i++) {
                            var charAt = adv_search_str.charAt(i);
                            if (isNaN(charAt)) {
                                var vanitee = vanity_numbers[vanity_letters.indexOf(charAt)];
                                if (vanitee) adv_search_str = adv_search_str.replaceAt(i, vanitee);
                            }
                        }
                        $scope.user.search_vanity = adv_search_str;
                        sessionStorage.vanity = adv_search_str;
                    }
                }
                $scope.user.search = url_search ? 'url' : 'tfn';
                $scope.user.search_short = 0;
                $scope.user.adv_searching = 1;
                $scope.user.adv_short_result = {};
                apiService.post('/search_short', { 'search': $scope.user.search, 'search_str': adv_search_str }) //$scope.user.search_str
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.adv_searching = $scope.user.search_short = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_short_result = data.result;
                            $scope.user.adv_short_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.adv_searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.adv_searching = $scope.user.search_long = 1;
                $scope.user.adv_long_result = {};
                apiService.post('/search_long', { 'search': $scope.user.search, 'search_str': adv_search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.adv_searching = $scope.user.search_long = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_long_result = data.result;
                            $scope.user.adv_long_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.adv_searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.adv_searching = $scope.user.search_brand = 1;
                $scope.user.adv_brand_result = {};
                apiService.post('/search_brand', { 'search': $scope.user.search, 'search_str': adv_search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.adv_searching = $scope.user.search_brand = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_brand_result = data.result;
                            $scope.user.adv_brand_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.adv_searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.adv_searching = $scope.user.search_adv = 1;
                $scope.user.adv_adv_result = {};
                apiService.post('/search_adv', { 'search': $scope.user.search, 'search_str': adv_search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.adv_searching = $scope.user.search_adv = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_adv_result = data.result;
                            $scope.user.adv_adv_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.adv_searching = 0;
                        console.log('Errooor');
                    })

            } else {
                $scope.user.search = 'text';
                $scope.user.adv_searching = $scope.user.search_short = 1;
                $scope.user.adv_short_result = {};
                apiService.post('/search_text_short', { 'search': 'text', 'search_str': $scope.user.adv_search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.adv_searching = $scope.user.search_short = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_short_result = data.result;
                            $scope.user.adv_short_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.adv_searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.adv_searching = $scope.user.search_long = 1;
                $scope.user.adv_long_result = {};
                apiService.post('/search_text_long', { 'search': 'text', 'search_str': $scope.user.adv_search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.adv_searching = $scope.user.search_long = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_long_result = data.result;
                            $scope.user.adv_long_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.adv_searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.adv_searching = $scope.user.search_brand = 1;
                $scope.user.adv_brand_result = {};
                apiService.post('/search_text_brand', { 'search': 'text', 'search_str': $scope.user.adv_search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.adv_searching = $scope.user.search_brand = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_brand_result = data.result;
                            $scope.user.adv_brand_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.adv_searching = 0;
                        console.log('Errooor');
                    })
                $scope.user.adv_searching = $scope.user.search_adv = 1;
                $scope.user.adv_adv_result = {};
                apiService.post('/search_text_adv', { 'search': 'text', 'search_str': $scope.user.adv_search_str })
                    .success(function (data) {
                        if (data.status) {
                            $scope.user.adv_searching = $scope.user.search_adv = 0;
                            $scope.user.search_result = 1;
                            $scope.user.adv_adv_result = data.result;
                            $scope.user.adv_adv_result.count = data.count;
                        }
                    })
                    .error(function (data, status, headers, config) {
                        $scope.user.adv_searching = 0;
                        console.log('Errooor');
                    })
            }
        }
    }

    /******* Start --  Common angular js ***********/

    $scope.showDialog = function () {
        $('.select-dropdown').css('display', 'flex');
        $('.select-list').css('border-bottom', '1px solid #222');
        if (sessionStorage.ranking) {
            $('.main-filter .brand-dropdown').hide();
            $('.main-filter .cat-dropdown').hide();
            if ($('.duration-dropdown')) { $('.duration-dropdown').hide(); }
        }
    }

    $scope.showResponseTypes = function () {
        $('.response_dropdown .filter-position').css('display', 'block');
        $('.main-filter #brand-dropdown').hide();
        $('.creatives_dropdown .filter-position').hide();
    }

    $scope.showLangType = function () {
        $('.main-filter #brand-dropdown').hide();
        $('.response_dropdown .filter-position').hide();
        $('.creatives_dropdown .filter-position').css('display', 'flex');
    }

    $scope.showCategory = function () {
        $('.main-filter .cat-dropdown').css('display', 'flex');
        $('.main-filter .brand-dropdown').hide();
        $('.select-dropdown').hide();
        if ($('.duration-dropdown')) { $('.duration-dropdown').hide(); }
    }

    $scope.showDuration = function () {
        $('.duration-dropdown').css('display', 'flex');
        $('.main-filter .brand-dropdown').hide();
        $('.select-dropdown').hide();
        $('.main-filter .cat-dropdown').hide();
    }

    $scope.isActive = function (root) {
        if (root !== null) {
            return $state.includes(root);
        } else {
            return false;
        }
    };

    var isMobile = {
        Android: function () {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function () {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function () {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function () {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function () {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function () {
            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
        }
    };

    $scope.setImage = function (root) {
        if (root != '') {
            setTimeout(function () {
                if (!isMobile.any()) {
                    if ($('#' + root).hasClass('top-menu-active') && root !== null) {
                        var listItems = $(".main-nv-bg li");
                        listItems.each(function (idx, li) {
                            var id = ($(li).attr('id'));
                            $('#' + id + ' a .deactivate').css('display', 'block');
                            $('#' + id + ' a .activate').css('display', 'none');
                        });

                        $('#' + root + ' .activate').css('display', 'block');
                        $('#' + root + ' .deactivate').css('display', 'none');
                    }
                }


                //for mobile
                if (isMobile.any()) {
                    if ($('#' + root + '_m').hasClass('top-menu-active') && root !== null) {
                        var listItems = $(".menu-bar li");
                        listItems.each(function (idx, li) {
                            var id = ($(li).attr('id'));
                            $('#' + id + '_m a .deactivate').css('display', 'block');
                            $('#' + id + '_m a .activate').css('display', 'none');
                        });

                        $('#' + root + '_m .activate').css('display', 'block');
                        $('#' + root + '_m .deactivate').css('display', 'none');
                    }
                }

                if (!isMobile.any()) {
                    if (root == '' && root !== null) {
                        var listItems = $(".main-nv-bg li");
                        listItems.each(function (idx, li) {
                            var id = ($(li).attr('id'));
                            $('#' + id + ' a .deactivate').css('display', 'block');
                            $('#' + id + ' a .activate').css('display', 'none');
                        });
                    }
                }

                if (isMobile.any()) {
                    //for mobile
                    if (root == '' && root !== null) {
                        var listItems = $(".menu-bar li");
                        listItems.each(function (idx, li) {
                            var id = ($(li).attr('id'));
                            $('#' + id + '_m a .deactivate').css('display', 'block');
                            $('#' + id + '_m a .activate').css('display', 'none');
                        });
                    }
                }
            }, 100);
        }
    }

    $scope.setImage('');

    $scope.selectClearAll = function (val) {
        if (val == 1) {
            $("input[name='checkbox5[]']").each(function () { //loop through each checkbox
                this.checked = true;
            });
            $("input[name='checkbox6[]']").each(function () { //loop through each checkbox
                this.checked = true;
            });
        }
        if (val == 2) {
            $("input[name='checkbox5[]']").each(function () { //loop through each checkbox
                this.checked = false;
            });
            $("input[name='checkbox6[]']").each(function () { //loop through each checkbox
                this.checked = false;
            });
        }
    }

    $scope.showsubCats = function (categoryId, v) {
        var $checked = '', $flag;

        if ($('#checkbox5_' + categoryId).is(':checked')) {
            $flag = '1';
            $checked = "checked=checked";
        } else {
            $('#checkbox5_all').prop('checked', false);
            $("#checkbox5_all").removeAttr('checked');
            $checked = "";
            $flag = '0';
        }


        if (!$('#subcat_ul' + categoryId).is(':visible')) {
            if ($('[name="checkbox6[]"]:checked').length == 0) {
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('full-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('half-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).addClass('empty');
            } else if ($('#checkbox5_' + categoryId).is(':checked')) {
                $('#checkbox5_' + categoryId).prop('checked', true);
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('half-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('empty');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).addClass('full-filled');
            } else if (!$('#checkbox5_' + categoryId).is(':checked')) {
                $('#checkbox5_' + categoryId).prop('checked', false);
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).addClass('empty');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('half-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('full-filled');
            }
            else if ($('[name="checkbox6[]"]:checked').length != $('[name="checkbox6[]"]').length) {
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('empty');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('full-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).addClass('half-filled');
            } else if ($('[name="checkbox6[]"]:checked').length == $('[name="checkbox6[]"]').length) {
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('empty');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('half-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).addClass('full-filled');
            }
        } else {
            if ($('[name="checkbox6[]"]:checked').length == 0) {
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('full-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('half-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).addClass('empty');
            }
            else if ($('[name="checkbox6[]"]:checked').length != $('[name="checkbox6[]"]').length) {
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('empty');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('full-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).addClass('half-filled');
            } else if ($('[name="checkbox6[]"]:checked').length == $('[name="checkbox6[]"]').length) {
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('empty');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('half-filled');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).addClass('full-filled');
            }
        }



        if (v == 1) {
            if (!$('#subcat_ul' + categoryId).is(':visible')) {
                $('#label_' + categoryId + ' #right').removeClass('fa-caret-right');
                $('#right').addClass('fa-caret-down');
                $("#checkbox5_" + categoryId + " + #label_" + categoryId).removeClass('empty');
                $('#' + categoryId).show();
            } else {
                $('#label_' + categoryId + ' #right').removeClass('fa-caret-down');
                $('#right').addClass('fa-caret-right');
                $('#' + categoryId).hide();

            }
        } else {
            allCats('1');
        }
    }

    $scope.backToTracking = function (v) {
        if ($("#notification_required").length) { $('#notification_required').css('display', 'none'); $('#save').removeAttr('disabled'); }
        $(v).modal('hide');
    }
    $scope.getDayPart = function (obj, cid, nid) {
        $rootScope.aCategory = obj.category;
        var c = obj.category;
        var catArr = c.split("<br>");
        $rootScope.day = catArr[0];
        $rootScope.time = catArr[1];
        $rootScope.color = obj.color;
        $rootScope.cid = cid;
        $rootScope.nid = nid
        $('.chart-wrapper').hide();
        $('.creative-grid').show();
    }

    $scope.assistant_admin_check = function (value) {
        if (value == '1') {
            return true;
        } else {
            return false;
        }

    }

    $scope.backToRanking = function () {
        $state.go('ranking');
    }
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
            .success(function (data) {
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
            })
            .error(function (data, status, headers, config) {

            });
    }


    $scope.verifyDuplicateEmail = function (username, id) {
        apiService.post('/check_email', { 'email': username, 'user_id': id })
            .success(function (data) {
                if (data.status) {
                    if (data.valid) {
                        $rootScope.errors = 1;
                        if (sessionStorage.company_id == data.result[0].company_id) {
                            $rootScope.email_user_id = data.user_result[0].user_id;
                            $rootScope.usernameValidInCompany = 1;
                            $rootScope.usernameValid = 0;
                        } else {
                            $rootScope.usernameValid = 1; //username already exists.
                            $rootScope.usernameValidInCompany = 0;
                            $('#duplicate_username').css('display', 'block');
                        }

                    } else {
                        $rootScope.errors = 0;
                        $rootScope.usernameValid = 0; //username not exists.
                        $rootScope.usernameValidInCompany = 0;
                    }
                }
            })
            .error(function (data, status, headers, config) {

            });
    }

    if (sessionStorage.admin == 1) {
        $scope.admin = sessionStorage.admin;
    }

    if (sessionStorage.assistant_admin == 1) {
        $scope.assistant_admin = sessionStorage.assistant_admin;
    }
    /******* End --  Common angular js ***********/

    $(document).ajaxError(function (event, jqxhr, settings, thrownError) {
        if (jqxhr.statusText != "from network export" && jqxhr.statusText != "from refine export" && jqxhr.statusText != "networks_excel") {
            if (jqxhr.status == 401) {
                $('#browser_timeout').modal('show');
                $("#r_loader").hide();
                $("#dialogModal").hide();
            } else if (jqxhr.status == 403) {
                $('#session_timeout').modal('show');
                $("#r_loader").hide();
                $("#dialogModal").hide();
            } else if (jqxhr.status == 406) {
                $('#access_error').modal('show');
                $("#r_loader").hide();
                $("#dialogModal").hide();
            } else if (jqxhr.status == 409) {
                FoundationApi.publish('update_version', 'show');
                $("#r_loader").hide();
                $("#dialogModal").hide();
            } else if (jqxhr.status == 307) {
                window.location.href = "/drmetrix";
            } else {
                $('#request_timeout').modal('show');
                $("#r_loader").hide();
                $("#dialogModal").hide();
            }
        }
    });

    $scope.setLoginTime = function () {
        apiService.post('/get_last_login', { 'user_id': sessionStorage.loggedInUserId })
            .success(function (data) {
                if (data.status) {
                    $rootScope.last_login = data.result.last_login;

                    if (sessionStorage.lastLoginTime != undefined && sessionStorage.lastLoginTime != $rootScope.last_login) {
                        $('#browser_timeout').modal('show');

                    } else {
                        sessionStorage.lastLoginTime = $scope.last_login;
                    }
                } else {
                    clearInterval($rootScope.myLogin); // stop the interval
                }
            })
            .error(function (data, status, headers, config) {

            });
    }

    $rootScope.eulaDisagreeFlag = 0;

    $rootScope.setCookie = function (cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toGMTString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    $rootScope.getCookie = function (cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    $scope.showTrackingDialogue = function (alert_type, type_id, name) {
        reset_callProgressBar(1);
        $.ajax({
            type: 'POST',
            url: '/drmetrix/api/index.php/get_tracking_detail',
            data: {
                "alert_type": alert_type,
                "type_id": type_id,
                "name": name
            }, success: function (data) {
                var response = jQuery.parseJSON(data);

                switch (alert_type) {
                    case 'advertiser':
                        $("#track_advertiser_ul").hide();
                        $("#track_brand_ul").show();
                        $("#track_creative_ul").show();
                        $("#brand_classification_div").attr('style','display:none !important');
                        break;
                    case 'brand':
                        $("#track_advertiser_ul").hide();
                        $("#track_brand_ul").hide();
                        $("#track_creative_ul").show();
                        $("#brand_classification_div").attr('style','display:none !important');
                        break;
                    case 'category':
                        $("#track_advertiser_ul").hide();
                        $("#track_brand_ul").show();
                        $("#track_creative_ul").show();
                        $("#brand_classification_div").attr('style','display:block !important');
                        break;
                    case 'network':
                        $("#track_advertiser_ul").show();
                        $("#track_brand_ul").show();
                        $("#track_creative_ul").show();
                        $("#brand_classification_div").attr('style','display:block !important');
                        break;
                }
                if (response.status == 1) {
                    if (response.data['frequency'] != "") {
                        $("input[name=alert-frequency][value='daily']").prop('checked', response.data['frequency'].indexOf("daily") != -1);
                        $("input[name=alert-frequency][value='weekly']").prop('checked', response.data['frequency'].indexOf("weekly") != -1);
                        $("input[name=alert-frequency][value='monthly']").prop('checked', response.data['frequency'].indexOf("monthly") != -1);
                    }
                    if (sessionStorage.tracking == 1) {
                        if (response.data['status'] == 'active') {
                            $("#other_tracking_actions").html('<a onclick="inactiveTracking(\'inactive\');"><i class="fa fa-eye blue-eye" title="Track"></i></a>');
                        } else {
                            $("#other_tracking_actions").html('<a onclick="inactiveTracking(\'active\');"><i class="fa fa-eye-slash grey-eye" title="Track"></i></a>');
                        }
                    } else {
                        $("#other_tracking_actions").html('');
                    }
                    var elements = response.data['track_elements'].split(",");
                    elements.forEach(function (element) {
                        if (element == 'advertiser') {
                            $("#track_advertiser_input").prop('checked', true);
                        }
                        if (element == 'brand') {
                            $("#track_brand_input").prop('checked', true);
                        }
                        if (element == 'creative') {
                            $("#track_creative_input").prop('checked', true);
                        }
                    });
                    if (alert_type == 'network' || alert_type == 'category') {
                        var classification = response.data['classification'];
                        if (classification != "") {
                            $("input[name=brand-classification]").prop('checked', false);
                            classification.forEach(function (element) {
                                switch (element) {
                                    case 'short_form_products':
                                        $("#short_form_products").prop('checked', true);
                                        break;
                                    case 'lead_generation':
                                        $("#lead_generation").prop('checked', true);
                                        break;
                                    case 'brand_direct':
                                        $("#brand_direct").prop('checked', true);
                                        break;
                                    case '285_mins':
                                        $("#285_mins").prop('checked', true);
                                        break;
                                }
                            });
                        } else {
                            $("input[name=brand-classification]").prop('checked', false);
                        }
                    }
                }
                $('#openTrackModal').modal('show');
            }, error: function (xhr, status, error) {
                console.log('Error');
            }
        });
    }

    $scope.getAgencyContacts = function () {
        let agency_name = $('#_agency_list option:selected').attr('id');
        if(agency_name == 'In-House Agency') {
            FoundationApi.publish('openInHouseAgencyModal', 'show');
        }
    }

    $scope.hideTrackingModal = function () {
        setTimeout(function () {
            $('#openTrackmodal').modal('hide');
         }, 2000);
    }

    $scope.hideCategoryrackingModal = function () {
        setTimeout(function () { 
            $('#openCategoryTrackModal').modal('hide');
         }, 2000);
    }

    $scope.showAppleHelp = function () {
        $('#apple_help').modal('show');
    }
    $scope.setPaginationBar = function(id, pager) {
        if(pager.current_page_count <= pager.limit ) {
            $('#'+pager.pager_id).hide();
        } else{
            $('#'+pager.pager_id).show();
        }
    }
    $rootScope.initialise_365datepicker = function () {
        $('#datepicker_checkbox').daterangepicker({
            dateLimit: {
                days: '365',
            },
            locale: {
                format: 'YYYY-MM-DD'
            },

            startDate: (sessionStorage.is_apply_calendar == 1) ? sessionStorage.start_date : moment(),
            endDate: (sessionStorage.is_apply_calendar == 1) ? sessionStorage.end_date : moment(),
            maxDate: moment().diff(new Date(), moment().add(365, 'days')) > 365 ? moment().add(365, 'days') : new Date()
        });

        $('#datepicker_checkbox').on('showCalendar.daterangepicker', function (ev, picker) {
            $('.overlay-filter').show();
        });

        $('#datepicker_checkbox').on('apply.daterangepicker', function (ev, picker) {
            var scope = angular.element($("#ranking_page")).scope();
            var startDate; var start_date_tsp;
            var endDate; var end_date_tsp;
            start_date_tsp = picker.startDate;
            end_date_tsp = picker.endDate;
            var timediff = Math.abs(end_date_tsp - start_date_tsp);
            var diffDays = Math.ceil(timediff / (1000 * 3600 * 24));
            var start_dt = start_date_tsp.format('YYYY-MM-DD');

            var end_dt = end_date_tsp.format('YYYY-MM-DD');
            sessionStorage.start_date = scope.start_date = start_dt;
            sessionStorage.end_date = scope.end_date = end_dt;

            var disp_start_dt = picker.startDate.format('MM/DD/YYYY')
            var disp_end_dt = picker.endDate.format('MM/DD/YYYY')
            sessionStorage.disp_start_date = scope.disp_start_date = disp_start_dt
            sessionStorage.disp_end_date = scope.disp_end_date = disp_end_dt;
            sessionStorage.is_apply_calendar = 0;

            scope.ranking.date_range = 'Date Range - ' + sessionStorage.disp_start_date + ' thru ' + sessionStorage.disp_end_date;
            scope.findDiff(scope.start_date);
            $('#datepicker_checkbox').prop('checked', true);
            $('.overlay-filter').hide();
            sessionStorage.selectDate = 'calender';
            $('#hidden_val').val('calender');
            scope.ranking.selectDate = sessionStorage.selectDate = $rootScope.selected_date = 'calender';
        });

        $('#datepicker_checkbox').on('outsideClick.daterangepicker', function (e, picker) {
            var target = $(e.target);
            // if the page is clicked anywhere except within the daterangerpicker/button
            // itself then call this.hide()
            if (
                // ie modal dialog fix
                e.type == "focusin" ||
                target.closest(this.element).length ||
                target.closest(this.container).length ||
                target.closest('.calendar-table').length
            ) return;
            picker.show();
        });

        $('#datepicker_checkbox').on('hide.daterangepicker', function (ev, picker) {
            // $('.overlay-filter').hide();
        });

        $('#datepicker_checkbox').on('cancel.daterangepicker', function (ev, picker) {
            var scope = angular.element($("#ranking_page")).scope();
            var data = scope.getParameters();
            $('.overlay-filter').hide();
        });

        /* Daterangepicker input disabled */
        $('.daterangepicker_input .input-mini').attr('disabled', true);
    }

    if ($rootScope.getCookie("allVals").length > 0) {
        sessionStorage.nc_selected_array = $rootScope.getCookie("allVals");
    }

    $scope.openVideoHelp = function () {
        $scope.state = !$scope.state;
    }

});
