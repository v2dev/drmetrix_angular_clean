
angular.module("drmApp").controller("RankingController", function($scope, $state, $rootScope, apiService){
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }

    $scope.rankingPrimaryFiler = 0;

    $scope.editable = function () {
        $scope.rankingPrimaryFiler = 1;

    }

    $scope.applicable = function () {
        console.log();
        $scope.rankingPrimaryFiler = 0;

    }

    $scope.resettable = function () {
        $scope.rankingPrimaryFiler = 0;
    }

    $scope.cancelable = function () {
        $scope.rankingPrimaryFiler = 0;

    }

});