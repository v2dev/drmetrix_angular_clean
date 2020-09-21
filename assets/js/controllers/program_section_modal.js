"use strict";
angular.module('drmApp').controller('ProgramSectionModalController', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    // Call brand List ui Grid
    $scope.uigridProgramModdal = function() {
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var c_dir = '6';
        formData.network_id = $rootScope.network_id;
        formData.id = $rootScope.brand_id;
        formData.area = 'brand';

        vm.gridOptionsProgram = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            paginationPageSize: 10,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
        };

        vm.gridOptionsProgram.columnDefs = [
            { name: 'program', pinnedLeft:true, displayName:'Program'},
            { name: 'start_time', pinnedLeft:true, displayName:'Star Time (EST)'},
            { name: 'total_airings', pinnedLeft:true, displayName:'Total Airings'},
            { name: 'total_spend', pinnedLeft:true, displayName:'Total Spend'},
            { name: 'national_airings', pinnedLeft:true, displayName:'National Airings'},
            { name: 'national_percent', pinnedLeft:true, displayName:'National %'},
            { name: 'national_spend', pinnedLeft:true, displayName:'National Spend($)'},
            { name: 'local_airings', pinnedLeft:true, displayName:'DPI Airings'},
            { name: 'local_percent', pinnedLeft:true, displayName:'DPI %'},
            { name: 'local_spend', pinnedLeft:true, displayName:'DPI Spend($)'},
        ];
        apiService.post('/get_programs_by_network', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptionsProgram.data = data.data.rows;
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.show_Program_list = function () {
        //ui grid code
        $scope.uigridProgramModdal();
    }
    $scope.show_Program_list();
});