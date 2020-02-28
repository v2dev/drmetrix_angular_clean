// http://next.plnkr.co/edit/Cdq2d9l1XxCi10bFXtBt?p=preview&preview

var app = angular.module('drmApp');
// http://next.plnkr.co/edit/Xns6KkIgR1xsWAgtDN3w?p=preview&preview
app.controller('AuthyController', function ($scope,$rootScope, apiService, $http, $state, modalConfirmService, $uibModal) {
    $scope.authy = {};
    $scope.authy.invalidToken = true;
    $scope.buttonClicked = false;
    $scope.showModal = function() {
      var dlgElem = angular.element(document.querySelector('#auhtyModalDlg'));
      // var dlgElem = angular.element("#auhtyModalDlg");
      if (dlgElem) {
         dlgElem.on("hide.bs.modal", function() {
          dlgElem.scope().someValueField = "This text is from the caller.";
          dlgElem.modal("show");
            console.log("reset data model..");
         });
      }
    }
    $scope.showModal();

    // $scope.authy.mobile = $stateParams.mobile ? $stateParams.mobile : $rootScope.mobile;
   
   
    $scope.verifyLogin = function(){
      $scope.buttonClicked = true
      if(sessionStorage.mobile == undefined || sessionStorage.authy_id == undefined ){
        $state.go('home');
        return false;
      }

      var mobile = sessionStorage.mobile;
      //sessionStorage.mobile = '';
      var auhty_id = sessionStorage.authy_id;
      //sessionStorage.authy_id = '';
      apiService.post('/verify_authy', {token: $scope.authy.token,id:auhty_id,verify_mobile:mobile , user_id : sessionStorage.login_user_id , user_id : sessionStorage.login_user_id})
        .success(function(data) {
          localStorage.complete_name = sessionStorage.complete_name = $rootScope.complete_name = data.name;
          if(data.status){
            $scope.authy.invalidToken = true;
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

           if(data.eulaFlag == 0){
              $rootScope.udata = data;
              $scope.init1(data);
              $state.go('eulaAgreement');
            }else{               
		
              var prototype= Object.getPrototypeOf($scope); //$scope.__proto__;
              prototype.user.loggedIn = true;
              prototype.user.name = data.name;
              sessionStorage.role = localStorage.role = data.role;
              sessionStorage.assistant_admin = localStorage.assistant_admin = data.assistant_admin;
              sessionStorage.loggedIn = data.name;
              sessionStorage.loggedInUserId = data.user_id;
              sessionStorage.contactemail = data.contactemail;
              $rootScope.loggedIn = 1;
              $rootScope.setCookie("loggedIn", data.name, 30);
              $rootScope.setCookie("userrole", data.role, 30);
              if(sessionStorage.role == 'superadmin'){
                    sessionStorage.superadmin = 1;
                    $rootScope.superadmin = 1;
                    sessionStorage.admin = 0;
                    $rootScope.admin = 0;
                    sessionStorage.user_company = 0;
                    $rootScope.user_company  = 0;
                }else if(sessionStorage.role == 'admin'){
                    sessionStorage.admin = 1;
                    $rootScope.admin = 1;
                    sessionStorage.superadmin = 0;
                    $rootScope.superadmin = 0;
                    sessionStorage.user_company = data.user_company;
                    $rootScope.user_company  = data.user_company;
                    $rootScope.setCookie("user_company", data.user_company);
                }else{
                     sessionStorage.login_user = 1;
                    $rootScope.login_user = 1;
                    sessionStorage.admin = 0;
                    $rootScope.admin = 0;
                    sessionStorage.superadmin = 0;
                    $rootScope.superadmin = 0;
                    sessionStorage.user_company = data.user_company;
                    $rootScope.user_company  = data.user_company;
                    $rootScope.setCookie("user_company", data.user_company);
                }
                
              if(sessionStorage.tracking !== undefined){
                $state.go('tracking');
              }else if(sessionStorage.video_not_played == 1){
                $scope.user.pdf = 1;
                $state.go('video_page', {
                  id: sessionStorage.video_url,
                  video: $.cookie("video") //selectedItem and id is defined
                });
            }else{
                $scope.init1(data);
                if(sessionStorage.role == 'superadmin'){
                  $state.go('adminConsole');
              } else {
                $state.go('ranking');
              }
              } 
            }                 
                    
          }else{
                $scope.authy.invalidToken = false;
                $scope.buttonClicked = false;
          }
        })
        .error(function(data, status, headers, config) {
          console.log('Errooor');
        });
    }

    $scope.error_reset = function (){
      $scope.authy.invalidToken = true;
    }

    $scope.ok = function () {
        $uibModalInstance.close($scope.selected.item);
      };
    
      $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
      };
});

