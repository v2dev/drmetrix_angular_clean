angular.module('drmApp').controller('UserController', function ($scope, $http, $interval, uiGridTreeViewConstants, $rootScope, apiService) {
    var usCtrl = this;
    $scope.showUsers = function () {
        var config = {
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        $scope.gridOptions1 = {};
        $scope.gridOptions1 = {
            columnDefs: [
                { name: 'name' },
                { name: 'gender', enableHiding: false },
                { name: 'company' }
            ],

            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
            }
        };

        apiService.post('./../../assets/json/100.json', config)
        // apiService.post('/show_users', config)
            .then(function (response) {
                console.log(response.status);
                console.log(response.result);
                console.log(response.count);
                $scope.gridOptions1.data = response.status;
            });

    }
    $scope.showUsers();

});