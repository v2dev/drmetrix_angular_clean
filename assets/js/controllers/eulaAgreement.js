angular.module('drmApp').controller('EulaAgreementController', function ($scope, $state, $rootScope,  apiService, $uibModal) {
  // https://embed.plnkr.co/plunk/PbnBdN
 
    $scope.open = function() {
        var modalInstance =  $uibModal.open({
          templateUrl: "./templates/modals/eulaAgreementModalDialog.html",
          controller: "EulaModalCtrl",
          size: 'md modal-dialog-centered',
        });
        
        modalInstance.result.then(function(response){
            $scope.result = `${response} button hitted`;
        });
      };

      $scope.eulaDisagree = function(){
        $rootScope.eulaDisagreeFlag = 1;
        $state.go('home');
        return false;
      }

  
});

angular.module('drmApp').controller('EulaModalCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.eulaDisagree = function(){
        $rootScope.eulaDisagreeFlag = 1;
        $uibModalInstance.dismiss();
        $state.go('home');
        return false;
      }
  
     
    $scope.eulaAgree = function(){  
      debugger;
        apiService.post('/eula_check_updated',{})
        .then(function(response) {
          $uibModalInstance.dismiss();
          $state.go('ranking');
      }), function() {
        $rootScope.eulaDisagreeFlag = 0;
        $state.go('home');
        return false;
      }   
    }
  
  });