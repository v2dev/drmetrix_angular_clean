// http://next.plnkr.co/edit/Cdq2d9l1XxCi10bFXtBt?p=preview&preview

var app = angular.module('drmApp');
// http://next.plnkr.co/edit/Xns6KkIgR1xsWAgtDN3w?p=preview&preview
app.controller('AuthyController', function ($scope,$rootScope, apiService, $http, $state, modalConfirmService, $uibModal) {
    $scope.authy = {};
    $scope.openModal = function() {
      var modalInstance =  $uibModal.open({
        templateUrl: "./templates/modals/authyModalDialog.html",
        controller: "AuthyModalCtrl",
        size: 'md modal-dialog-centered',
        backdrop : false
      });
      
      modalInstance.result.then(function(response){
          $scope.result = `${response} button hitted`;
      });
    };
    $scope.openModal();

    $scope.error_reset = function (){
      $scope.invalidToken = true;
    }
  });

angular.module('drmApp').controller('AuthyModalCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
  $scope.buttonClicked = false;
  $scope.verifyLogin = function(){
    $scope.buttonClicked = true;
    var mobile    = localStorage.mobile;
    var auhty_id  = localStorage.authy_id;
    apiService.post('/verify_authy', {token: $scope.token, id:auhty_id, verify_mobile:mobile , user_id : localStorage.login_user_id})
    .then(function(response) {
      var data = response.data;
      if(data.status){
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
        $uibModalInstance.dismiss();
        $state.go('ranking');
      } else{
          $scope.invalidToken = false;
          $scope.buttonClicked = false;
      }
    }), function() {
        $uibModalInstance.dismiss();
        tate.go('home');
          
    }
  }
});


