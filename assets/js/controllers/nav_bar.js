"use strict";
angular.module("drmApp").controller("NavBarController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal, $compile, modalConfirmService, uiGridConstants, uiGridExporterConstants){
    $scope.state = $state;
    console.log($scope.state.current);
});


