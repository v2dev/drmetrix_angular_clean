var app = angular.module('drmApp');

app.controller('LoginController', function($scope,$rootScope, apiService, $http, $cookies, $state, modalConfirmService) {
  var pdf = sessionStorage.pdf;
  sessionStorage.lastLoginTime = '';
  $rootScope.login_user_id = '';
  $scope.login_error = 0;
  $scope.loginForm = function (form) {
    if (form.$valid) {
        $rootScope.udata = '';
        sessionStorage.user_company = 0 ; sessionStorage.login_user = 0; sessionStorage.admin = 0;
      apiService.post('/user_login', $scope.user)
      .then(function(response) {
        var data = response.data;
         $scope.user.errors = false;
          if (data.status) {
            sessionStorage.company_id       = data.company_id;
            sessionStorage.lastLoginTime    = data.last_login;
            sessionStorage.assistant_admin  =  $rootScope.assistant_admin = data.assistant_admin;
            sessionStorage.role             = $rootScope.role = data.role;
            localStorage.login_user_id     = data.user_id;
            sessionStorage.complete_name    = sessionStorage.loggedIn = $rootScope.complete_name = localStorage.complete_name.complete_name = data.name;
            $cookies.put("loggedIn",data.name);
            $cookies.put("userrole",data.role);
            if (data.new_user == 1) {
              localStorage.mobile = data.mobile;
              localStorage.authy_id = data.authy_id;
              $state.go('authy');
            } else {
              if (data.eulaFlag == 0) {
                $rootScope.udata = data;
                $state.go('eulaAgreement');
              } else {
                sessionStorage.roles            = data.roles;
                sessionStorage.contactemail     = data.contactemail;
                sessionStorage.loggedInUserId   = $rootScope.login_user_id = data.user_id;
                sessionStorage.company_id       = data.company_id;
                sessionStorage.company_name     = data.company_name;
                sessionStorage.admin_id         = data.admin_id;
                $rootScope.loggedIn             = 1;
                $rootScope.adsphere_blog_url = $rootScope.sub_menu[2].href = $rootScope.right_sub_menu[2].href = data.ADSPHERE_BLOG_URL;
                $rootScope.system_status_url = $rootScope.sub_menu[5].href = $rootScope.right_sub_menu[5].href = data.SYSTEM_STATUS_URL;
                if (sessionStorage.role == 'superadmin') {
                  sessionStorage.superadmin = 1;
                  $scope.init1(data);
                  $state.go('adminConsole');
                } else if (sessionStorage.role == 'admin') {
                  sessionStorage.admin = 1;
                  sessionStorage.user_company = data.user_company;
                  $cookies.put("user_company",data.user_company);
                } else {
                  sessionStorage.login_user = 1;
                  sessionStorage.user_company = data.user_company;
                  $rootScope.user_company = data.user_company;
                  $cookies.put("user_company",data.user_company);
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
            $scope.user.errors = true;
            if (data.response != '' && data.response != 'inactive' && data.response != 'deleted') {
              $scope.login_error = 1;
              $scope.send_sms_error = data.response;
              $timeout(function(){
                $scope.login_error = 0;
              }, 5000);
            } else if (data.response == 'inactive') {
              if (data.flag == 0) {
                $scope.error_text = "<p>Your company's evaluation of AdSphere has ended or your subscription is no longer valid. To enable access again, please contact <a mailto:'sales@drmetrix.com'>sales@drmetrix.com</a></p>";
              } else if (data.flag == 2) {
                $scope.error_text = '<p>Adsphere staging build is currently not available. Please login to regular production build at <a href="http://adsphere.drmetrix.com">http://adsphere.drmetrix.com</a></p><p>To gain access to staging build, please contact DRMetrix.</p>';
              } else {
                $scope.error_text = "<p>We apologize but your user account has been deactivated by your system administrator.</p><p> Please contact <a href='mailto:"+data.admin_email+"'>"+data.admin_email+"</a> if you wish to have your account reactivated.</p>";
              }
            } else if (data.response == 'deleted') {
              $scope.error_text = "<p>We're sorry, your user account has been disabled.</p> <p>Please ask one of your Adsphere System Administrators to enable your account.</p><p> If your company requires additional user licenses, please contact <a href='mailto:sales@drmetrix.com'>sales@drmetrix.com</a></p>";
            }else {
              $scope.user.invalid = true;
            }
            
          }
        }, function (response) {
          // this function handles error
          });  
    }
  }

  $scope.relogin = function() {
      $rootScope.eulaDisagreeFlag = 0;
      $state.go('home');
      return false;
  }

  $scope.showModal = function(){
    var defaultOptions = {
      size: 'lg modal-dialog-centered'
    }
    var options = {
          bodyText: 'Instructions to reset your password has been sent to your email address. <br/>Thank You!',
          headerText: ' ',
          closeReq: 1
      };

    modalConfirmService.showModal(defaultOptions, options).then(function (result) {
    });
  }

  $scope.forgotPasswordForm = function (form) {
    if (form.$valid) {
      apiService.post('/forgot_password', $scope.user)
        .then(function (response) {
          var data = response.data;
          if (data.status) {
            $state.go('home');
            $scope.showModal();
          } else {
            $scope.user.invalid = true;
          }
        }
        , function (response) {
          // this function handles error
          }); 
    }
  }


  });