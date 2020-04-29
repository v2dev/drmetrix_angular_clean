"use strict";
angular.module('drmApp').controller('DaypartModalController', function($scope, $rootScope, $uibModalInstance){
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
    

    $scope.adayparts = function (value, items) {
        for (var i in items) {
                items[i].isSelected = value;
        }
    }

    $scope.changedaypart= function (value, items) {
        var temp = true;
        for (var i in items) {
                if(!items[i].isSelected)
                    temp = false
        }
        $rootScope.allDayparts = temp;
    }


});
