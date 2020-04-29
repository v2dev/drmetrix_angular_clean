angular.module('drmApp').controller('TrackModalController', function($scope, $rootScope, $uibModalInstance, $state, apiService, $compile) {
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});