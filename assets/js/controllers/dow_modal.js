angular.module('drmApp').controller('DowModalController', function($scope, $rootScope, $uibModalInstance){
    $scope.adow = function (type, items) {
        $rootScope.selectedWeekType = type;
        for (var i in items) {
            if ($rootScope.weekType[type].indexOf(items[i].id) != -1) {
                items[i].isSelected = true;
            } else {
                items[i].isSelected = false;
            }
        }
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

});