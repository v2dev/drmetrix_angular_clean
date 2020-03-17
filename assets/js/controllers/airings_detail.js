angular.module("drmApp").controller("airingsDetailController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal){
    if (!apiService.isUserLogged($scope)) {
        // $state.go('home');
        // return;
    }
$scope.uigridAiringSpend = function(){
    var formData = $rootScope.formdata;
    var vm = this;
    var config = {
        headers : {
            'Content-Type': 'application/json; charset=utf-8'
        }
    }
    // var c_dir = $scope.ranking.creative_type == 'short' ? '6':'1';
    var c_dir = '6';
    formData.tab = "brand";
    formData.primary_tab = "";
    formData.secondary_tab = 'NA';
    formData._search = true;
    formData.rows = '10';
    formData.page = '1';
    formData.sidx = "airings";
    formData.sord = 'desc';
    formData.brand_id = $scope.brand_id;
    formData.brand_name = $scope.brand_name;
    formData.cat_id = 'all';
    formData.breaktype = 'A';
    formData.network_code = 'all_networks';
    formData.hour = 'all_hour';
    formData.day = 'all_day';
    formData.dayparts = 'all_dayparts';
    formData.creative_duration = 'all_short_duration';

    vm.gridAiringSpend = {
        enableGridMenu: true,
        enableSelectAll: true,
        enableSorting: true,
    };

    vm.gridAiringSpend.columnDefs = [
        { name: 'id', pinnedLeft:true, displayName:'ID'},
        { name: 'network_code', pinnedLeft:true, displayName:'NetWork'},
        { name: 'network_alias', pinnedLeft:true, displayName:'Network Name'},
        { name: 'creatives', pinnedLeft:true, displayName:'Creatives' },
        { name: 'program_count', pinnedLeft:true, displayName:'Program' },
        { name: 'airings', pinnedLeft:true, displayName:'Total Airings' },
        { name: 'total_spend', pinnedLeft:true, displayName:'Total Spend' },
        { name: 'national_count', pinnedLeft:true, displayName:'National Airings' },
        { name: 'national', pinnedLeft:true, displayName:'National' },
        { name: 'nat_spend', pinnedLeft:true, displayName:'National Spend' },
        { name: 'local_spend', pinnedLeft:true, displayName:'DPI Airings' },
        { name: 'local', pinnedLeft:true, displayName:'DPI % ' },
        { name: 'local_spend', pinnedLeft:true, displayName:'DPI Spend ($)' },
        { name: 'duration', pinnedLeft:true, displayName:'ASD' },
    ];
    apiService.post('/brand_networks', formData, config)
    .then(function (data) {
        $scope.PostDataResponse = formData;
        vm.gridAiringSpend.data = data.data.rows;
    }, function (response) {
        // this function handlers error
    });
}

$scope.uigridAiringSpend($scope.brand_id, $scope.brand_name, $scope.active_tab, $scope.all_network);
    
});