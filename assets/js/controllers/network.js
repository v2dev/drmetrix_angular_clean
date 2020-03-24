angular.module('drmApp').controller('NetworkController', function ($scope, $timeout, $state, $stateParams, $filter, $interval, uiGridConstants, $rootScope, apiService, modalConfirmService, $uibModal, $cookies) {
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }
    $scope.letterLists = ['all', '0-9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
    $scope.selectedLetter = 'all';
    $rootScope.headerDisplay = 1;
    var today = new Date();
    var prev_date = new Date();
    prev_date.setMonth(today.getMonth() - 6);
    var nCtrl = this;
    
    $scope.openModal = function(templateUrl, controller, size, backdrop) {
        $scope.modalInstanceMain =  modalConfirmService.showModal({
            backdrop: true,
            keyboard: true,
            modalFade: true,
            templateUrl: templateUrl,
            controller: 'NetworkModalController',
            scope: $scope,
            size: size ? size : 'xl modal-dialog-centered',
            backdrop : backdrop != null ? backdrop : true
          });

          $scope.modalInstanceMain.result.then(function(response){
              $scope.result = `${response} button hitted`;
          });

          $scope.modalInstanceMain.result.catch(function error(error) {
            if(error === "backdrop click") {
              // do nothing
            } else {
              // throw error;
            }
          });
    };

    $scope.loadNetworks = function() {
        $scope.openModal('./templates/modals/networkModal.html');
        apiService.post('/get_all_active_networks')
        .then(function (response) {
            var data = response.data;
            $scope.networkTabList = data.result;
            console.log($scope.networkTabList );
        }), (function (response) {
        });
    }
    $scope.loadNetworks();

    $scope.checkLiveDateStatus = function (live_date) {
        var l_date = new Date(live_date);
        if (l_date.getTime() >= prev_date.getTime() && l_date.getTime() <= today.getTime()) {
            return true;
        } else {
            return false;
        }
    }

    $scope.getLiveDate = function (live_date) {
        var year = live_date.substring(0, 4);
        var month = live_date.substring(5, 7);
        var date = live_date.substring(8, 10);
        return month + "/" + date + "/" + year;
    }

    $scope.networkSearchBy = function (data, selectedLetter) {
        return function (item) {
            if (data && data != "") {
                return item.network_alias.toLowerCase().indexOf(data.toLowerCase()) > -1;
            } else if (selectedLetter != 'all') {
                return item.network_alias.toLowerCase().indexOf(selectedLetter.toLowerCase()) == 0;
            } else {
                return item;
            }
        };
    }

    $scope.hasTabLetterDisable = function (letter) {
        var temp = [];
        if(typeof($scope.networkTabList) != 'undefined'){
            if (letter == 'all')
                return false;
                if (letter == '0-9') {
                    var lists = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                    for (var i in lists) {
                        temp = $scope.networkTabList.filter(function (item) {
                            if (item.network_alias.toLowerCase().indexOf(lists[i]) == 0)
                                return item;
                        });
                        if (temp.length > 0)
                            return false;
                    }
                } else {
                    temp = $scope.networkTabList.filter(function (item) {
                        if (item.network_alias.toLowerCase().indexOf(letter.toLowerCase()) == 0)
                            return item;
                    });
                }
        }
        return temp.length > 0 ? false : true;
    }

    $scope.networkSelectCount = function () {
        if($scope.networkTabList && $scope.networkTabList.length> 0){
            var tempData = $scope.networkTabList.filter(function(item) {
                return item.isSelected == true;
            })
            $scope.valueofcount = tempData.length;
            if (tempData.length > 1 && tempData.length < 9) {
                $scope.networkApply = 0;
            }
            else {
                $scope.networkApply = 1;
            }
            return tempData.length;
        } else {
            return 0;
        }
    }

    $scope.clearNetworkList = function () {
        for (var i in $scope.networkTabList) {
            $scope.networkTabList[i].isSelected = false;
        }
    }

    $scope.changeLatter = function (letter) {
        $scope.selectedLetter = letter;
    }

    $scope.hasLetterDisable = function (letter) {
        var temp = [];
        if(typeof($scope.networkLists) != 'undefined'){
            if (letter == 'all')
                return false;
                if (letter == '0-9') {
                    var lists = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                    for (var i in lists) {
                        temp = $scope.networkLists.filter(function (item) {
                            if (item.network_alias.toLowerCase().indexOf(lists[i]) == 0)
                                return item;
                        });
                        if (temp.length > 0)
                            return false;
                    }
                } else {
                    temp = $scope.networkLists.filter(function (item) {
                        if (item.network_alias.toLowerCase().indexOf(letter.toLowerCase()) == 0)
                            return item;
                    });
                }
        }
        return temp.length > 0 ? false : true;
    }

    $scope.networkFilterApply = function () {
        $scope.modalInstanceMain.close();
        var checked_count = 0;
        var my_network_called = $rootScope.my_network_called = sessionStorage.my_network_called = 1;
        sessionStorage.selected_arr_networks = [];
        sessionStorage.selected_networks_id = [];
        var selected_network_code = []; // network code array
        var selected_network_id = []; // network id array
        angular.forEach($scope.networkTabList, function(value, key) {
            if(value.isSelected) {
                selected_network_code.push(value.network_code);
                selected_network_id.push(value.network_id);
            }
          });
       
        $scope.selected_network_ids = selected_network_id;
        $cookies.put("selected_network_code",selected_network_code);
        $cookies.put("selected_network_id",selected_network_id);
      
        if ($scope.type) {
            $scope.jqgridBrandNetworkAirings();
        } else {
            $scope.jqgridAdvNetworkAirings();
        }

        $rootScope.network_category_section = 1;
    }

    $scope.jqgridBrandNetworkAirings = function() {
        console.log('brand grid called');
    }

    $scope.$on('modal.closing', (event, reason, closed) => {
        if (!closed) {
            event.preventDefault();
            $scope.$close("Closing");
        }
    });
});

angular.module('drmApp').controller('NetworkModalController', function($scope, $rootScope, $timeout, $uibModalInstance, $state, apiService, modalConfirmService) {

    $scope.dismissModal = function(params) {
        $uibModalInstance.dismiss();
    }

    $scope.closeModal = function() {
        $uibModalInstance.close("Ok");
    }

    $scope.ok = function() {
        $uibModalInstance.close("Ok");
    }

    $scope.cancel = function() {
        $uibModalInstance.dismiss();
    }

});

