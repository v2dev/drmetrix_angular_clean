
"use strict";
angular.module('drmApp').controller('ProgramModalController', function($scope, $rootScope, $uibModalInstance, $state, apiService, $compile) {
    $scope.all_ranking_program  = $scope.programs_ids != '' ? false : true;
    $scope.checkedPrograms      = angular.copy($rootScope.checkedRankingPrograms);

    $scope.applyModal = function() {
        $uibModalInstance.dismiss();
    }

    $scope.getCheckedPrograms = function() {
        var checked_programs = [];
        if(!$scope.all_ranking_program) {
            angular.forEach($rootScope.ranking_programs, function(item, key) {
                angular.forEach(item, function(v, k) {
                    if(v.isSelected) {
                        checked_programs.push(k);
                    }
                });
            });
            $scope.length_of_programs  = checked_programs.length;
            $scope.checked_programs_id = checked_programs.join(',');
        }
    }

    $scope.selectAllProgram = function() {
        angular.forEach($rootScope.ranking_programs, function(item, key) {
            angular.forEach(item, function(v, k) {
                v.isSelected = $scope.all_ranking_program;
            });
        });
        $scope.getCheckedPrograms();
    }

    $scope.checkAllProgram = function(program) {
        $scope.getCheckedPrograms();
        $scope.all_ranking_program = true;
       if($scope.length_of_programs != $rootScope.ranking_programs.length ) {
           $scope.all_ranking_program = false;
       }
    }

    $scope.applyModal = function() {
        console.log($scope.checked_programs_id);
        $rootScope.$broadcast("CallParentMethod", {'network_id' : $scope.selectedNetwork,'network_alias' : $scope.selectedNetworkAlias,'program_id' : $scope.checked_programs_id});
        $uibModalInstance.dismiss();
    }

    $scope.resetPrograms = function() {
        $scope.all_ranking_program = true;
        angular.forEach($rootScope.ranking_programs, function(item, key) {
            angular.forEach(item, function(v, k) {
                v.isSelected = true;
            });
        });
        $rootScope.$broadcast("CallParentMethod", {'network_id' : $scope.selectedNetwork,'network_alias' : $scope.selectedNetworkAlias});
        $uibModalInstance.dismiss();
    }


    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});
