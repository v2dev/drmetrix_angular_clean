
"use strict";
angular.module('drmApp').controller('HodModalController', function($scope, $rootScope, $uibModalInstance, $state, apiService){
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    $scope.changeHod = function (value, items) {
        var temp = true;
        for (var i in items) {
            for(var j in items[i].data) {
                if(!items[i].data[j].isSelected)
                    temp = false
            }
        }
        $rootScope.allHour = temp;
    }

    $scope.forHod = function (value, items) {
        for (var i in items) {
            for(var j in items[i].data) {
                items[i].data[j].isSelected = value;
            }
        }
    }
});
