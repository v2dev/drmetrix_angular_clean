
angular.module("drmApp").controller("RankingController", function($scope, $state, $rootScope, apiService,  $uibModal){
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }

    $scope.initialisation = function() {
        $rootScope.headerDisplay = 1;
        $rootScope.complete_name = localStorage.complete_name;
       
    }
    $scope.initialisation() ;

    $scope.showTab = function(tab) {
        $scope.type = tab;
        
    }

    $scope.call_filter_list = function(menu) {
        var modalInstance = $uibModal.open({
            templateUrl: './templates/ranking-modals.html',
            controller: "FilterCtrl",
            backdrop:'static',
            size :'lg',
            keyboard:false,
        });
    }
    
    feather.replace();

});

angular.module('drmApp').controller('FilterCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    console.log('filter called');
  });
  