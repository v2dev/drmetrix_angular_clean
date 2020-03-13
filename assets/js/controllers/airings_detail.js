angular.module("drmApp").controller("airingsDetailController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal){
    if (!apiService.isUserLogged($scope)) {
        // $state.go('home');
        // return;
    }
    console.log($scope.page_call);
console.log('inside controller');
console.log($scope.name);
console.log($scope.id);

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
    formData.primary_tab = $rootScope.my_list;
    formData.secondary_tab = 'NA';
    formData._search = true;
    formData.rows = '10';
    formData.page = 1;
    formData.sidx = "created_date";
    formData.sord = 'desc';

    vm.gridAiringSpend = {
        enableGridMenu: true,
        enableSelectAll: true,
        enableSorting: true,
    };

    vm.gridAiringSpend.columnDefs = [
        { name: 'full_name', pinnedLeft:true, displayName:'User'},
        { name: 'name', pinnedLeft:true, displayName:'List Name'},
        { name: 'created_date', pinnedLeft:true, displayName:'Created On' },
        { name: 'shared_list_date', pinnedLeft:true, displayName:'Shared On' },

        { name: 'copy_list', pinnedLeft:true, displayName: 'Copy To My List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="copy_list checkbox-custom" id="copy_list_\'{{row.entity.id}}\'" name="copy_list" ng-click="copySharedList({{row.entity.id}})"  {{row.entity.checked_copy_list}} {{row.entity.disable_copy_list}} /><label for="copy_filter_{{row.entity.id}}" class="checkbox-custom-label {{row.entity.disabled_copy_list_class}}"></label></li></ul></nav>'},

        { name: 'shared_list', displayName: 'Share List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="share_filter checkbox-custom" id="share_list_\'{{row.entity.id}}\'" name="share_list" ng-click="updateShareListStatus(\'{{row.entity.id}}\')"  \'{{row.entity.checked_shared_list}}\' \'{{row.entity.disabled_shared_list}}\' /><label for="share_filter_\'{{row.entity.id}}\'" class="checkbox-custom-label \'{{row.entity.disabled_class}}\'"></label></li></ul></nav>'},

        { name: 'criteria_name', pinnedLeft:true, displayName:'Detail', cellTemplate:'<span title="{{COL_FIELD}}">{{row.entity.criteria_name == \'\' ? \'-\' : row.entity.criteria_name | limitTo: 60}}</span>' },

        { name: 'edit_list', pinnedLeft:true, displayName:'Edit', cellTemplate: '<span class="edit-list_\'{{row.entity.id}}\' dropdown-list edit-list-icon" id="edit_list_\'{{row.entity.id}}\'"  ng-click="edit_user_filter(\'{{row.entity.id}}\',\'{{row.entity.edit_list}}\');"  class="edit-list"><i class="fa fa-pencil" aria-hidden="true"></i></span><span class="edit-list-loader_\'{{row.entity.id}}\' edit-list-loader" id="excel_loader_\'{{row.entity.id}}\'"><img src="/drmetrix/assets/img/excel_spinner.gif" alt="Loading icon"></span>' },

        { name: 'apply', pinnedLeft:true, displayName:'Apply', cellTemplate: '<a href="javascript:void(0)" ng-click="apply_user_list(\'{{row.entity.id}}\', \'{{row.entity.apply}}\');" id="apply_filter_{{row.entity.id}}">Apply</a>' },
    ];
    apiService.post('/get_user_lists', formData, config)
    .then(function (data) {
        $scope.PostDataResponse = formData;
        vm.gridAiringSpend.data = data.data.rows;
    }, function (response) {
        // this function handlers error
    });
}

// $scope.uigridAiringSpend(name, id, active_tab, all_network, all_day, all_hour, network_cnt, spend, c, tab, val, sd, ed, returnText, lang, area, adv_name, brand_name, brand_id, network_id, network_dpi);
    
});