angular.module("drmApp").controller("GlobalSearchController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal, $compile){
    
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }

$scope.uigridGlobalSearch = function(){
    var formData = $rootScope.formdata;
    var vm = this;
    var config = {
        headers : {
            'Content-Type': 'application/json; charset=utf-8'
        }
    }

    var c_dir = '6';
    formData.srch_txt='proactive';
    formData.grid_type='creatives';
    formData.resp_type = formData.responseType;
    formData._search = false;
    formData.rows = '10';
    formData.page = '1';
    formData.sidx = '';
    formData.sord = 'desc';
    
    vm.gridGlobalSearch = {
        enableGridMenu: true,
        enableSorting: true,
        enableExpandableRowHeader: false,
        //Pagination
        paginationPageSizes: [20],
        paginationPageSize: 20,
        paginationTemplate: $rootScope.correctTotalPaginationTemplate,
    };

    vm.gridGlobalSearch.columnDefs = [
        // { name: 'id', displayName:'id' },
        { name: 'status', displayName:'Status', cellTemplate:'<i class="fa fa-circle" id="{{row.entity.is_active_creative}}"></i>' },
        { name: 'creative_name', displayName:'Creatives', cellTemplate:'<span><a href="" style="color: rgb(0, 190, 255);" ng-click="grid.appScope.view_adv_tab(row.entity.adv_name,row.entity.id,\''+c_dir+'\',\'\',\'\',\''+formData.sd+'\',\''+formData.ed+'\',\'creatives\',row.entity.id,row.entity.creative_name,\'global_search\',row.entity.need_help)" >{{COL_FIELD}}</a></span>' },
        { name: 'duration', displayName:'Length' },
        { name: 'brand_name', displayName:'Brand Name', cellTemplate:'<span><a href="" style="color: rgb(0, 190, 255);" ng-click="grid.appScope.view_adv_tab(row.entity.adv_name,row.entity.adv_id,\''+c_dir+'\',\'\',\'\',\''+formData.sd+'\',\''+formData.ed+'\',\'brand\',row.entity.brand_id,row.entity.brand_name,\'global_search\',row.entity.need_help)" >{{COL_FIELD}}</a></span>' },
        { name: 'adv_name', displayName:'Advertiser', cellTemplate:'<span><a href="" style="color: rgb(0, 190, 255);" ng-click="grid.appScope.view_adv_tab(row.entity.adv_name,row.entity.adv_id,\''+c_dir+'\',\'\',\'\',\''+formData.sd+'\',\''+formData.ed+'\',\'adv\',\'\',\'\',\'global_search\',row.entity.need_help)" >{{COL_FIELD}}</a></span>' },
        { name: 'response_type', displayName:'Response Type', cellTemplate: '<span class="response_img"><a href="" ng-if=row.entity.response_url == 1 title="URL" ><img src="/drmetrix_angular_clean/assets/images/url-icon.svg" alt="URL" /></a><a href="" ng-if=row.entity.response_sms == 1 title="SMS"><img src="/drmetrix_angular_clean/assets/images/sms-icon.svg" alt="SMS" /></a><a href="" ng-if=row.entity.response_tfn == 1 title="Telephone"><img src="/drmetrix_angular_clean/assets/images/telephone-icon.svg" alt="Telephone" /></a><a href="" ng-if=row.entity.response_mar == 1 title="Mobile"><img src="/drmetrix_angular_clean/assets/images/mobile-icon.svg" alt="Mobile" /></a>' },
        { name: 'airings', displayName:'Airings' },
        { name: 'spend_index', displayName:'Spend ($)' },
        { name: 'video', displayName: '', cellTemplate:'<span ng-if=!row.entity.thumbnail><i class="fa fa-play-circle-o fa-2x" style="color:#cbcccc;"></i></span><span ng-if=row.entity.thumbnail == \'\'><a href="#basicModalCamp"><i class="fa fa-play-circle-o fa-2x" ng-click="grid.appScope.playvideo(row.entity.thumbnail,\''+formData.sd+'\',\''+formData.ed+'\',row.entity.network_code_grid,\'none\')"></i></a></span>' }
    ];
    apiService.post('/get_global_search_data', formData, config)
    .then(function (data) {
        $scope.PostDataResponse = formData;
        vm.gridGlobalSearch.data = data.data.rows;
    }, function (response) {
        // this function handlers error
    });
}
$scope.uigridGlobalSearch('6481', "Zip Recruiter", "dow", "");

});