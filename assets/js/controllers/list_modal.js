
angular.module('drmApp').controller('ListsModalController', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.sharedList = 'My';
    $scope.selected_user = '';
    $scope.loading = true;
    var airings_data = new Array();
    let cachedBrandListsData = [];
    let cachedAdvListsData = [];
    let list_brand_api = 0;
    let list_adv_api = 0;
    let $dropdown;
   
    
    $scope.showSharedLists = function(item) {
        $scope.sharedList = item;
            //with ui grid code, displayes grid data according to rules set
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    // $scope.createDropdown = function(id) {
    //     if($scope.lists) {
    //         angular.forEach($scope.lists, function(value, key) {
    //             if(jQuery.inArray( value.id, $scope.list_id_array) > -1) {
    //                 value.selected = true;
    //             } else {
    //                 value.selected = false;
    //             }
    //         });
    //         $dropdown = $('.dropdown-mul-1').dropdown({
    //             data: $scope.lists,
    //             multipleMode: 'label',
    //             searchTextLengthErrorMessage: '',
    //             limitCount: 100,
    //             limitCountErrorMessage: 'There is a 100 limit for '+$scope.ranking.list_tab+'s chosen. You have reached the limit for this list.',
    //             choice: function () {
    //               console.log(arguments);
    //             },
    //             input: '<div class="search-input"> <span class="search-icon"><i class="fa fa-search" aria-hidden="true"></i></span><input type="text"  id="search_searchable_dropdown" placeholder="Please enter minimum 3 characters" onKeyUp="removeDisabled()"/><span id="clearIconList" class="search-icon cross-icon" style="display:none;"><i class="fa fa-times-circle" title="Clear Search"></i></span></span></div><button type="button" class="btn btn-blue applyBtn" id="search_list" disabled="disabled"><i class="fa fa-search"></i>Search</button>',
    //             // <span class="inline-label search-text-tip">Search tip: <span> Enter minimum 3 characters</span></span>
    //           });
    //     }
    //     setTimeout(function () {
    //         $('#edit_list_'+id).show();
    //         $('span#excel_loader_'+id).hide();  
    //     }, 100);
    //     // $('#edit-list-modal').show();
    //     $('#edit-list-modal').modal('show');
    //     $('#edit-list-modal').css("display","flex");
    //     var scroll=$('.dropdown-mul-1');
    //     scroll.animate({scrollTop: scroll.prop("scrollHeight")});
    // }
        
    // $scope.edit_user_filter = function(id, list_ids) {
    //     console.log($scope.listGridApi.selection.getSelectedRows());
    //     // angular.forEach(data, function(data, index) {
    //     //     data["index"] = index+1;
    //     //     //data.push({"index":index+1})
    //     // })
    //     var p = $("#tab_list").jqGrid("getGridParam");
    //     var iCol = p.iColByName["name"];
    //     $scope.headerListName =  $("#" + id).find('td').eq(iCol).text();

    //     if($dropdown)  $dropdown.data('dropdown').destroy();
    //     $scope.edit_list_id = id; // the list in edit mode
    //     let list_id_array = list_ids.split(',');
    //     $scope.list_id_array = list_id_array;
    //     if($scope.ranking.list_tab == 'brand') {
    //         list_brand_api = cachedBrandListsData.length == 0 ? 1 : 0;
    //     }

    //     if($scope.ranking.list_tab == 'advertiser') {
    //         list_adv_api = cachedAdvListsData.length == 0 ? 1 : 0;
    //     }

    //     if((list_brand_api == 1 || list_adv_api == 1)) {
    //         setTimeout(function () {
    //             $('#edit_list_'+id).hide();
    //             $('#excel_loader_'+id).show();
    //         }, 0);
    //         $.ajax({
    //             type: 'POST',
    //             url: '/drmetrix/api/index.php/get_all_brands_advertisers',
    //             // async: false,
    //             data: {
    //                 tab : $scope.ranking.list_tab == 'brand' ? 1 : 0
    //             }, success: function (data) {
    //                 let response = jQuery.parseJSON(data);
    //                 let items = [];
    //                 var selectedEle ;
    //                 angular.forEach(response.result, function(value, key) {
    //                     selectedEle = false;
    //                     if(jQuery.inArray( value.brand_id, list_id_array )) {
    //                         selectedEle = true;
    //                     }
    //                     items.push({
    //                         'id': value.id, 
    //                         'disabled': false,
    //                         'selected':false,
    //                         'name':  value.name,
                            
    //                     });
    //                 });
    //                 if($scope.ranking.list_tab == 'brand') {
    //                     cachedBrandListsData = items;
    //                 } else {
    //                     cachedAdvListsData = items;
    //                 }
    //                 $scope.lists = items;
    //                 $scope.createDropdown(id);
    //             }, error: function (xhr, status, error) {

    //             }
    //         });
    //     } else {
    //         // $.ajax({
    //         //     type: 'POST',
    //         // });
    //         // setTimeout(function () {
    //             $('#edit_list_'+id).hide();
    //             $('span#excel_loader_'+id).show();
    //         // }, 0);
    //         setTimeout(function () {
    //             $scope.createDropdown(id);
    //         }, 100);
    //     }
    // }

    $scope.apply_user_list = function(row) {
        console.log(row);
        $scope.applied_list_ids = row.criteria_id;
        $scope.display_list_name = row.name;
        $scope.applied_list_type = $rootScope.my_list;
        $scope.list_id   = row.id;
        $rootScope.$broadcast("CallParentMethod", {'applied_list_ids' : $scope.applied_list_ids, 'applied_list_type' : $scope.applied_list_type , list_id : $scope.list_id , 'display_list_name' : $scope.display_list_name });
        $scope.apply_user_list = 1;
        
        if($scope.reset_list == 1) {
            $scope.applied_list_type = '';
            $scope.applied_list_ids = '';
        } 
        
        $scope.reset_list = 0;
        $uibModalInstance.dismiss();
            // $('#listModal').modal('hide');
    }
    // Call brand List ui Grid
    $scope.uigridListModal = function() {
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

        vm.gridOptionsList = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            onRegisterApi: (gridApi) => {
                $scope.listGridApi = gridApi;
            },
        };
        

        vm.gridOptionsList.columnDefs = [
            { name: 'full_name', pinnedLeft:true, displayName:'User'},
            { name: 'name', pinnedLeft:true, displayName:'List Name'},
            { name: 'created_date', pinnedLeft:true, displayName:'Created On' },
            { name: 'shared_list_date', pinnedLeft:true, displayName:'Shared On' },

            { name: 'copy_list', pinnedLeft:true, displayName: 'Copy To My List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="copy_list checkbox-custom" id="copy_list_\'{{row.entity.id}}\'" name="copy_list" ng-click="copySharedList({{row.entity.id}})"  {{row.entity.checked_copy_list}} {{row.entity.disable_copy_list}} /><label for="copy_filter_{{row.entity.id}}" class="checkbox-custom-label {{row.entity.disabled_copy_list_class}}"></label></li></ul></nav>'},

            { name: 'shared_list', displayName: 'Share List', cellTemplate: '<nav class="grid-content"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="share_filter checkbox-custom" id="share_list_\'{{row.entity.id}}\'" name="share_list" ng-click="updateShareListStatus(\'{{row.entity.id}}\')"  \'{{row.entity.checked_shared_list}}\' \'{{row.entity.disabled_shared_list}}\' /><label for="share_filter_\'{{row.entity.id}}\'" class="checkbox-custom-label \'{{row.entity.disabled_class}}\'"></label></li></ul></nav>'},

            { name: 'criteria_name', pinnedLeft:true, displayName:'Detail', cellTemplate:'<span title="{{COL_FIELD}}">{{row.entity.criteria_name == \'\' ? \'-\' : row.entity.criteria_name | limitTo: 60}}</span>' },

            { name: 'edit_list', pinnedLeft:true, displayName:'Edit', cellTemplate: '<span class="edit-list_\'{{row.entity.id}}\' dropdown-list edit-list-icon" id="edit_list_\'{{row.entity.id}}\'"  ng-click="grid.appScope.edit_user_filter(\'{{row.entity.id}}\',\'{{row.entity.edit_list}}\');"  class="edit-list"><i class="fa fa-pencil" aria-hidden="true"></i></span><span class="edit-list-loader_\'{{row.entity.id}}\' edit-list-loader" id="excel_loader_\'{{row.entity.id}}\'"><img src="/drmetrix/assets/img/excel_spinner.gif" alt="Loading icon"></span>' },

            { name: 'apply', pinnedLeft:true, displayName:'Apply', cellTemplate: '<a href="javascript:void(0)" ng-click="grid.appScope.apply_user_list(row.entity);" id="apply_filter_{{row.entity.id}}">Apply</a>' },
        ];
        apiService.post('/get_user_lists', formData, config)
        .then(function (data) {
            $scope.loading = false;
            $scope.PostDataResponse = formData;
            vm.gridOptionsList.data = data.data.rows;
        }, function (response) {
            // this function handlers error
        });
    }

   
        

    //ui grid code
    $scope.uigridListModal();
});
