"use strict";
angular.module('drmApp').controller('RefineByReportModalController', function($scope, $rootScope, $uibModalInstance, $state, apiService){
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    // Call brand List ui Grid
    $scope.uigridRefineByTFNModdal = function() {
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var c_dir = '6';
        formData.sidx = "last_aired";
        formData.sord = "desc";
        formData.network_id = $rootScope.network_id;
        formData.id = $rootScope.brand_id;
        formData.area = 'brand';
        formData.brand_id= $scope.record_id;
        formData.record_id= $scope.record_id;
        formData.creative_id = $scope.creative_id;
        formData.header_name = $scope.header_name;

        vm.gridOptionsRefineModal = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            paginationPageSize: 10,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
        };

        vm.gridOptionsRefineModal.columnDefs = [
            { name: 'network_name', pinnedLeft:true, displayName:'Network Name', cellTemplate: "<span title='row.entity.network_name'>{{COL_FIELD | limitTo: 12}}</span>"},
            { name: 'last_aired', pinnedLeft:true, displayName:'Date/Time'},
            { name: 'breaktype', pinnedLeft:true, displayName:'Breaktype'},
            { name: 'phone_number', pinnedLeft:true, displayName:'Phone Number'},
            { name: 'web_address', pinnedLeft:true, displayName:'Web Address'},
            { name: 'program_name', pinnedLeft:true, displayName:'Program Name', cellTemplate: "<span title='row.entity.program_name'>{{COL_FIELD | limitTo: 18}}</span>"},
            { name: 'video', pinnedLeft:true, displayName:'Play', cellTemplate: '<a href="javascript:void(0);"><i class="fa fa-play-circle-o fa-2x" onclick="displayThumbnail(row.entity.airing_id,row.entity.network_code,\'network_log_video\')"></i></a>'},
            { name: 'thumbnail', pinnedLeft:true, displayName:'View', cellTemplate: '<a href="javascript:void(0);"><i class="fa fa-picture-o fa-2x col-00beff" ng-click="displayThumbnail(row.entity.airing_id,row.entity.network_code,\'network_log_thumbnil\')"></i></a>'},
        ];
        apiService.post('/view_airings_layout_tfn', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptionsRefineModal.data = data.data.rows;
        }, function (response) {
            // this function handlers error
            console.log("rejected with", response);
        });
    }
    $scope.uigridRefineByTFNModdal();

});