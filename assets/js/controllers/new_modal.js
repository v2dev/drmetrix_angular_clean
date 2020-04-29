"use strict";
angular.module('drmApp').controller('NewModalController', function($scope, $rootScope, $uibModalInstance, $state, apiService, $compile) {
    $scope.checkRadioButton = function() {
        $scope.newType = 'none';
        if($scope.newCheckBox) {
            $scope.newType = ($rootScope.type == 'brands')  ? 'brands' : 'advertisers';
        } else {
            
        }
    }

    $scope.newCheckBox = function() {
        $scope.newCheckBox = true;
    }

    $scope.applyModal = function() {
        $uibModalInstance.dismiss();
    }

    $scope.applyModal = function() {
        $rootScope.$broadcast("CallParentMethod", {'newType' : $scope.newType, 'newCheckBox' : $scope.newCheckBox });
        $uibModalInstance.dismiss();
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});
