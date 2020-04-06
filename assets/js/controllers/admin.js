angular.module('drmApp').controller('AdminController', function ($scope, $timeout, $state, $stateParams, $filter, $interval, uiGridConstants, $rootScope, apiService, modalConfirmService, $uibModal) {
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }

    $scope.popupDefaultOptions = {
        size: 'md modal-dialog-centered'
    };
    $scope.popupOptions = {
        closeReq: 1,
        bodyText: '',
        headerText: '',
        closeButtonText: '',
        actionButtonText: ''
    };

    $scope.showPopup = function (size, bodyText, headerText, closeReq) {
        if (size) {
            $scope.popupDefaultOptions.size = size;
        }
        if (bodyText) {
            $scope.popupOptions.bodyText = bodyText;
        }
        if (headerText) {
            $scope.popupOptions.headerText = headerText;
        }
        if (closeReq) {
            $scope.popupOptions.closeReq = closeReq;
        }
        $scope.modalInstance = modalConfirmService.showModal($scope.popupDefaultOptions, $scope.popupOptions); // .then(function (result) {})

        $scope.modalInstance.result.then(function (response) {
            // $scope.result = `${response} button hitted`;
        });

        $scope.modalInstance.result.catch(function error(error) {
            if (error === "backdrop click") {
                // do nothing
            } else {
                // throw error;
            }
        });
    }

    $scope.showAdmins = function () {
        var config = {
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var correctTotalPaginationTemplate =
            "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"prev-page\"></div></button> Page <input ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\"> of </abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"next-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-page\"></div></button></div></div><div class=\"ui-grid-pager-count-container\"></div></div>";
        $scope.gridOptionsAdmin = {};
        $scope.gridOptionsAdmin = {
            //Pagination
            enableSorting: true,
            paginationPageSize: 10,
            rowHeight: 30,
            paginationTemplate: correctTotalPaginationTemplate,
            columnDefs: [
                { name: 'company_name', displayName: 'Company Name', width: '130', cellTemplate: '<span title="{{row.entity.company_name == \'\' ? \'-\' : row.entity.company_name}}" >{{row.entity.company_name == \'\' ? \'-\' : row.entity.company_name | limitTo: 25}}{{row.entity.name.length > 25 ? \'...\' : \'\'}}</span>' },

                { name: 'company_type', displayName: 'Company Type', width: '150', cellTemplate: '<span title="{{row.entity.company_type}}" >{{row.entity.company_type}}</span>' },
                { name: 'company_size', displayName: 'Company Size', width: '120', cellTemplate: '<span title="{{row.entity.company_size}}" >{{row.entity.company_size}}</span>' },

                { name: 'revenue', displayName: 'Revenue', width: '150', cellTemplate: '<span title="{{row.entity.revenue}}" >{{row.entity.revenue}}</span>' },
                { name: 'client', displayName: 'Client', width: '70', cellTemplate: '<span title="{{row.entity.client}}" >{{row.entity.client}}</span>' },

                { name: 'account_owner', displayName: 'A/C Owner', width: '120', enableColumnMenus: false },

                { name: 'name', displayName: 'Name', width: '100', cellTemplate: '<span class="{{row.entity.authy_cookie ? \'user-verified\' : \'user-not-verified-authy\'}}">{{row.entity.authy_cookie ? \'Yes\' : \'No\'}}<span>' },

                { name: 'username', displayName: 'Admin Username', width: '150', cellTemplate: '<span>{{row.entity.vdate ? row.entity.vdate : \'-\'}}</span>' },
                { name: 'phone_number', displayName: 'Admin Mobile#', width: '90', cellTemplate: '<span title="+{{row.entity.country_code}}{{row.entity.phone_number}}" >+{{row.entity.country_code}}{{row.entity.phone_number}}</span>' },

                { name: 'adsphere_authenticate', displayName: 'Adsphere Authenticate', width: '170', cellTemplate: '<span class="{{row.entity.adsphere_authenticate == 0 ? \'user-not-verified\' : \'\'}}"  >{{row.entity.adsphere_authenticate  == 0 ? \'No\' : \'\'}}</span><span class="{{row.entity.adsphere_authenticate  == 0 ? \'user-not-verified-link\' : \'user-verified\'}}" id ="resend_link_{{row.entity.user_id}}" ng-click="grid.appScope.sendPassword(row.entity.passphrase, row.entity.user_id)" >{{row.entity.adsphere_authenticate == 0 ? \'Resend Email\' : \'Yes\'}}</span>' },

                { name: 'authy_cookie', displayName: 'Authy Authenticated', width: '170', cellTemplate: '<span class="{{row.entity.authy_cookie ? \'user-verified\' : \'user-not-verified\'}}" >{{row.entity.authy_cookie ? \'Yes\' : \'No\'}}</span>' },

                { name: 'vdate', displayName: 'Verified Date', width: '80', cellTemplate: '<span>{{row.entity.vdate}}</span>' },
                { name: 'eula_flag', displayName: 'Eula Overried', width: '60', cellTemplate: '<span>{{row.entity.eula_flag}}</span>' },
                { name: 'network_tab', displayName: 'Network Tab', width: '60', cellTemplate: '<span>{{row.entity.network_tab}}</span>' },
                { name: 'staging_access', displayName: 'Staging Access', width: '80', cellTemplate: '<span>{{row.entity.staging_access}}</span>' },
                { name: 'skip_authy', displayName: 'Skip Authy', width: '90', cellTemplate: '<span>{{row.entity.skip_authy == 1 ? \'Yes\' : \'No\'}}</span>' },
                { name: 'status', displayName: 'Status', width: '80', cellTemplate: '<span>{{row.entity.status == "active" ? "Active" : "Inactive"}}</span>' },

                { name: 'user_id', displayName: 'Action', width: '80', cellClass: "overflow-visible setting-icon", cellTemplate: '<div class="dropdown"><i class="fa fa-cog fa-2x" data-toggle="dropdown" id="dropdownMenuButton" aria-haspopup="true" aria-expanded="false"></i><ul class="dropdown-menu" aria-labelledby="dropdownMenuButton"><li><a ng-click="grid.appScope.getCompany(row.entity)" >Edit Company</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.deleteCompany(row.entity)">Delete Company</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.deactivate(row.entity)" data-toggle="modal">{{u.status == "active" ? "Deactivate" : "Activate"}} Company</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.getUsers(row.entity)">Edit/Delete/Deactivate/Activate User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.getUsers(row.entity)">Change Admin Of Company</a></li></ul></div>' }
            ],

            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
            }
        };

        // apiService.post('./../../assets/json/100.json', config)
        apiService.post('/show_admins', config)
            .then(function (response) {
                $scope.gridOptionsAdmin.data = response.data.result;
            }, function (response) {
                // this function handlers error
            });

    }
    $scope.showAdmins();
});