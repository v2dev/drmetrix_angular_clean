angular.module('drmApp').controller('NetworkDropdownModalController', function($scope, $rootScope, $uibModalInstance){
    $scope.forNetworkList = function (value) {
        $rootScope.all_networks_selected = value;
        for (var i in $rootScope.network_lists) {
            $rootScope.network_lists[i].isSelected = $rootScope.all_networks_selected;
        }
        // $rootScope.all_program_selected = $rootScope.all_networks_selected;
      
        angular.forEach($rootScope.programs, function (p) {
            angular.forEach(p, function(prg) {
                prg.isSelected = $rootScope.all_program_selected;
            });
            
        });
    }

    $scope.forProgramList = function() {
        $rootScope.all_program_selected = !$rootScope.all_program_selected;
        if($rootScope.all_program_selected) {
            $rootScope.checkedPrograms = $rootScope.copyCheckedPrograms;
        } else {
            $rootScope.checkedPrograms = [];
        }

        angular.forEach($rootScope.programs, function (p) {
            angular.forEach(p, function(prg) {
                prg.isSelected = $rootScope.all_program_selected;
            });
            
        });
    }

    $scope.forSingleNetwork = function() {
        var temp = true;
        $rootScope.network_selection = [];
        angular.forEach($rootScope.network_lists, function (network) {
            if(network.isSelected) {
                $rootScope.network_selection.push(network.network_id);
                temp = false;
            }
        });
        $rootScope.all_networks_selected = temp;

        angular.forEach($rootScope.programs, function (p) {
            angular.forEach(p, function(prg) {
                if($rootScope.network_selection.indexOf(prg.network_id) !== -1)
                    prg.isSelected = $rootScope.all_program_selected;
            });
        });

    }

    $scope.forSingleProgram = function() {
        var temp = true;
        angular.forEach($rootScope.programs, function (p) {
            if(!p.isSelected) {
                temp = false;
            }
        });
        $rootScope.all_program_selected = temp;
    }

    $scope.checkChildIsHide = function(id) {
        return $('#'+id+ ' div ').is(':visible');
    }
    
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});
