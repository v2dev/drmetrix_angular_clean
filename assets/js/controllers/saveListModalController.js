"use strict";
angular.module('drmApp').controller('saveListModalController', function($scope, $rootScope, $uibModalInstance, $state, apiService, $timeout, listService){
    $scope.closeModal = function() {
        $uibModalInstance.dismiss('cancel');
    }

    	
    $scope.save_list = function () {
            $scope.ranking.startFade = 2;
            var count;
            var list_name = $scope.ranking.list_name;
            var primary_tab = $scope.ranking.listName;
            var duplicate_found = 0;
            if ($.inArray(list_name, $scope.list_array) != -1 && $scope.ranking.list_duplicate_found == 1) {
                duplicate_found = 1;
            }
            $scope.list_array.push(list_name);
    
            var checkSplChar = $rootScope.cleanFileName(list_name);
            if (list_name == '' || checkSplChar == 1) {
                $scope.ranking.list_error = 1;
               $scope.ranking.list_message = 'Invalid List Name';
                return false;
            }

            angular.forEach($scope.selectedRows, function (row, key) {
                angular.forEach(row, function (column, colKey) {
                    if (colKey == "brand_id")
                    {
                        listService.listModel.idsOfSelectedRows.push(column);
                    }
                });   
            });
    
            var formdata = { 'list_name': list_name, 'primary_tab': primary_tab, 'criteria_ids': listService.listModel.idsOfSelectedRows.toString(), 'is_duplicate' : duplicate_found, asontv: ($scope.ranking.asontvSelection ? 1 : 0) };
            if($scope.ranking.list_duplicate_found == 1) {
                formdata.list_id =  $scope.ranking.list_id;
            }
            apiService.post('/save_user_list', formdata)
                .then(function (response) {
                    var data = response.data;
                    if (data.status != '') {
                        $scope.ranking.asontvSelection = false;
                        $scope.ranking.list_add = 0;
                        if (data.status == 1 || data.status == 2) {
                            $scope.ranking.list_add = 1;
                            $scope.ranking.list_name = '';
                            var display_text =  data.status == 1  ? 'saved' : 'updated';
                            $scope.ranking.list_message = 'List ' + display_text + ' successfully!';
                             $scope.ranking.startFade = true;
                             $scope.ranking.list_id = '';
                            $timeout(function () {
                                $scope.ranking.startFade = false;
                                //$("#modalSaveList_ranking").modal('hide');
                                $scope.ranking.openListPopUp = 0;
                                //$('#manageRanking').jqGrid('resetSelection');
                                listService.listModel.idsOfSelectedRows = 0;
                                //$("#btn_exp_brand").addClass("disabled");
                                //idsOfSelectedRows = [];
                                //rankOfSelectedRows = [];
                                $uibModalInstance.close($scope);
                            }, 2000);
                            return false;
                        }  else if(data.status == 3 && data.isDuplicate == 'both') { // if both criteria and name entry are there in db, then user can't overwrite
                            $scope.ranking.list_error = 1;
                            $scope.ranking.list_message =  data.message;
                            
                            return false;
                        }else if(data.status == 3 && (data.isDuplicate == 'name' || data.isDuplicate == 'criteria')) { // if  list exists by name or criteria
                            $scope.ranking.list_duplicate_found = 1;
                            $scope.ranking.list_id = data.list_id;
                            $scope.ranking.list_message =  'A list by this ' + data.isDuplicate + ' already exists, if you wish to overwrite the existing list please click Yes or click No to cancel';
                            return false;
                        }
                        
                    }
                },function (error) {
                    //$scope.initializeListPopUpValues();
                });
        }

});