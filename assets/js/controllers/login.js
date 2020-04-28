var app = angular.module('drmApp');

app.controller('LoginController', function($scope,$rootScope, apiService, $cookies, $state, modalConfirmService, $timeout) {
  $scope.login_error = 0;
  $rootScope.login_user_id = '';
  $scope.loginForm = function (form) {
    if (form.$valid) {
        $rootScope.udata = '';
      apiService.post('/user_login', $scope.user)
      .then(function(response) {
        var data = response.data;
         $scope.user.errors = false;
          if (data.status) {
            $rootScope.assistant_admin = data.assistant_admin;
            $rootScope.role = data.role;
            $rootScope.login_user_id     = data.user_id;
            $rootScope.complete_name = localStorage.complete_name = data.name;
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
                sessionStorage.loggedInUserId   = data.user_id;
                sessionStorage.admin_id         = data.admin_id;
                $rootScope.loggedIn             = 1;
                $rootScope.adsphere_blog_url = $rootScope.right_menu[2].href = data.ADSPHERE_BLOG_URL;
                $rootScope.system_status_url = $rootScope.right_menu[5].href = data.SYSTEM_STATUS_URL;
                if ($rootScope.role == 'superadmin') {
                  $scope.init1(data);
                  $state.go('adminConsole');
                } else if ($rootScope.role == 'admin') {
                  $cookies.put("user_company",data.user_company);
                } else {
                  $rootScope.user_company = data.user_company;
                  $cookies.put("user_company",data.user_company);
                }
                if ($rootScope.role != 'superadmin') {
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

                    // $scope.init1(data);

                    $state.go('ranking');
                 
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
                $scope.error_text = "<p>We apologize but your user account is deactivated by system administrator.</p><p> Please contact <a href='mailto:"+data.admin_email+"'>"+data.admin_email+"</a> if you wish to have your account reactivated.</p>";
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
      size: 'md modal-dialog-centered'
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