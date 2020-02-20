var app = angular.module('drmApp');
 app.controller('LoginController', function($scope, $rootScope, apiService, $http, $cookies) {
  var username = sessionStorage.loggedIn !== "undefined" && sessionStorage.loggedIn ? sessionStorage.loggedIn : '';
  var pdf = sessionStorage.pdf;
  sessionStorage.lastLoginTime = '';
  $scope.login_error = 0;
  $scope.eulaDisagreeFlag = 0;
  $scope.loginForm = function (form) {
      debugger;
    if (form.$valid) {
        $rootScope.udata = '';
        sessionStorage.user_company = 0 ; sessionStorage.login_user = 0; sessionStorage.admin = 0;
      apiService.post('/user_login', $scope.user)
      .then(function(response) {
        var data = response.data;
          $scope.user.admin_inactive_msg = $scope.user.deactive_msg = $scope.user.staging_access_inactive_msg =  $scope.user.deleted_msg =false;
          if (data.status) {
            sessionStorage.company_id       = data.company_id;
            sessionStorage.lastLoginTime    = data.last_login;
            sessionStorage.assistant_admin  =  $rootScope.assistant_admin = data.assistant_admin;
            sessionStorage.role             = $rootScope.role = data.role;
            sessionStorage.login_user_id    = data.user_id;
            sessionStorage.complete_name    = sessionStorage.loggedIn = $rootScope.complete_name = data.name;
            if (data.new_user == 1) {
              sessionStorage.mobile = data.mobile;
              sessionStorage.authy_id = data.authy_id;
              $state.go('authy');
            } else {
              if (data.eulaFlag == 0) {
                $rootScope.udata = data;
                $state.go('eulaAgreement');
              } else {
                // var prototype = Object.getPrototypeOf($scope); 
                // prototype.user.loggedIn         = true;
                // prototype.user.name             = data.name;
                sessionStorage.roles            = data.roles;
                sessionStorage.contactemail     = data.contactemail;
                sessionStorage.loggedInUserId   = data.user_id;
                sessionStorage.company_id       = data.company_id;
                sessionStorage.company_name     = data.company_name;
                sessionStorage.admin_id         = data.admin_id;
                $rootScope.loggedIn             = 1;
                $cookies.put("loggedIn",data.name);
                $cookies.put("userrole",data.role);
                // $rootScope.setCookie("loggedIn", data.name, 30);
                // $rootScope.setCookie("userrole", data.role, 30);
                $rootScope.adsphere_blog_url = $rootScope.sub_menu[2].href = $rootScope.right_sub_menu[2].href = data.ADSPHERE_BLOG_URL;
                $rootScope.system_status_url = $rootScope.sub_menu[5].href = $rootScope.right_sub_menu[5].href = data.SYSTEM_STATUS_URL;
                if (sessionStorage.role == 'superadmin') {
                  sessionStorage.superadmin = 1;
                  sessionStorage.admin = 0;
                  $scope.init1(data);
                  $state.go('adminConsole');
                } else if (sessionStorage.role == 'admin') {
                  sessionStorage.admin = 1;
                  sessionStorage.user_company = data.user_company;
                  $cookies.put("user_company",data.user_company);
                  // $rootScope.setCookie("user_company", data.user_company);
                } else {
                  sessionStorage.login_user = 1;
                  sessionStorage.user_company = data.user_company;
                  $rootScope.user_company = data.user_company;
                  $cookies.put("user_company",data.user_company);
                  // $rootScope.setCookie("user_company", data.user_company);
                }
                if (sessionStorage.role != 'superadmin') {
                  if (sessionStorage.tracking !== undefined && sessionStorage.tracking != 0) {
                    $state.go('tracking');
                  } else if (sessionStorage.video_not_played == 1) {
                    $scope.user.pdf = 1;
                    $state.go('video_page', {
                      id: sessionStorage.video_url,
                      video: $.cookie("video") //selectedItem and id is defined
                    });
                  } else {
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

                    $('#blog_status').attr('href', data.ADSPHERE_BLOG_URL);
                    $('#sys_status').attr('href', data.SYSTEM_STATUS_URL);
                    $scope.init1(data);

                    $state.go('ranking');
                  }
                }
              }
            }
          } else {
            if (data.response != '' && data.response != 'inactive' && data.response != 'deleted') {
              $scope.login_error = 1;
              $scope.send_sms_error = data.response;
              $timeout(function(){
                $scope.login_error = 0;
              }, 5000);
            } else if (data.response == 'inactive') {
              if (data.flag == 0) {
                $scope.user.admin_inactive_msg = true;
              } else if (data.flag == 2) {
                $scope.user.staging_access_inactive_msg = true;
              } else {
                $scope.user.admin_email = data.admin_email;
                $scope.user.admin_name = data.admin_name;
                $scope.user.deactive_msg = true;
              }
            } else if (data.response == 'deleted') {
              $scope.user.deleted_msg = true;
            }else {
              $scope.user.invalid = true;
            }
          }
        }, function (response) {

          // this function handles error
          
          });  
    }

  }

  });