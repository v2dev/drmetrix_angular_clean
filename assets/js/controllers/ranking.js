
angular.module("drmApp").controller("RankingController", function($scope, $state, $rootScope, apiService){
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }
});