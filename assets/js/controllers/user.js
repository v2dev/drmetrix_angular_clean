angular.module('drmApp').controller('UserController', function ($scope, $timeout, $state, $stateParams, $http, $interval, uiGridTreeViewConstants, $rootScope, apiService, modalConfirmService,  $uibModal) {
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }
    var usCtrl = this;
    $scope.showUsers = function () {
        var config = {
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var correctTotalPaginationTemplate =
    //same as normal template, but fixed totals:  {{(((grid.options.paginationCurrentPage-1)*grid.options.paginationPageSize)+1)}}   {{(grid.options.paginationCurrentPage*grid.options.paginationPageSize>grid.options.totalItems?grid.options.totalItems:grid.options.paginationCurrentPage*grid.options.paginationPageSize)}}
    "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"prev-page\"></div></button> Page <input ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\"> of </abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"next-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-page\"></div></button></div></div><div class=\"ui-grid-pager-count-container\"></div></div>";
        $scope.gridOptionsUser = {};
        $scope.gridOptionsUser = {
            //Pagination
            paginationPageSize: 10,
            rowHeight: 30,
            paginationTemplate: correctTotalPaginationTemplate,
            columnDefs: [
                { name: 'name', displayName:'Name', width: '130',cellTemplate:'<span title="{{row.entity.name == \'\' ? \'-\' : row.entity.name}}" >{{row.entity.name == \'\' ? \'-\' : row.entity.name | limitTo: 25}}{{row.entity.name.length > 25 ? \'...\' : \'\'}}</span>' },

                { name: 'username', displayName:'Username', width: '220',cellTemplate:'<span title="{{row.entity.country_code}}{{row.entity.username == \'\' ? \'-\' : row.entity.username}}" >{{row.entity.username == \'\' ? \'-\' : row.entity.username | limitTo: 40}}{{row.entity.username.length > 40 ? \'...\' : \'\'}}</span>' },
                { name: 'phone_number', displayName:'Mobile #', width: '120',cellTemplate:'<span title="+{{row.entity.country_code}}{{row.entity.phone_number}}" >+{{row.entity.country_code}}{{row.entity.phone_number}}</span>' },

                { name: 'position', displayName:'Position', width: '150',cellTemplate:'<span title="{{row.entity.position}}" >{{row.entity.position}}</span>' },
                { name: 'role', displayName:'role', width: '70',cellTemplate:'<span title="{{row.entity.role}}" >{{row.entity.role}}</span>' },

                { name: 'ads_authenticated', displayName:'Adsphere Authenticated', width: '120',enableColumnMenus: false, cellTemplate:'<span class="{{row.entity.ads_authenticated ? \'user-not-verified\' : \'\'}}"  >{{row.entity.ads_authenticated ? \'No\' : \'\'}}</span><span class="{{row.entity.ads_authenticated ? \'user-not-verified-link\' : \'user-verified\'}}" id ="resend_link_{{row.entity.user_id}}" ng-click="sendPassword(row.entity.passphrase,row.entity.user_id)" >{{row.entity.ads_authenticated ? \'Resend Email\' : \'Yes\'}}</span>' },

                { name: 'authy_cookie', displayName:'Authy Authenticated', width: '100', cellTemplate:'<span class="{{row.entity.authy_cookie ? \'user-verified\' : \'user-not-verified-authy\'}}">{{row.entity.authy_cookie ? \'Yes\' : \'No\'}}<span>' },

                { name: 'vdate', displayName: 'Verified Date', cellTemplate:'<span>{{row.entity.vdate ? row.entity.vdate : \'-\'}}</span>' },
                { name: 'assistant_admin', displayName: 'Assistant Admin', width: '90',cellTemplate:'<span>{{row.entity.assistant_admin == 1 ? \'Yes\' : \'No\'}}</span>' },

                { name: 'skip_authy', displayName: 'Skip Authy', width: '70', cellClass:'text-center', cellTemplate:'<nav class="grid-content" id="skip_authy"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="checkbox-custom" id="skip_authy_check_{{row.entity.user_id}}" ng-click="manageSkipAuthy(row.entity.user_id)" ng-checked="row.entity.skip_authy == 1 ? true : false " /><label class="checkbox-custom-label"></label></li></ul></nav>' },

                { name: 'status', displayName: 'Status', width: '80', cellTemplate:'<span>{{row.entity.status == "active" ? "Active" : "Inactive"}}</span>' },
                { name: 'last_login', displayName: 'Last Login', cellTemplate:'<span>{{row.entity.last_login}}</span>' },
                { name: 'login_count', displayName:'Total Logins', cellTemplate:'<span>{{row.entity.login_count}}</span>' },
                { name: 'last_30_days_count', displayName:'Last 30 Days Logins', width: '80',cellTemplate:'<span>{{row.entity.last_30_days_count}}</span>' },
                { name: 'tracking_alert_subscribed', displayName:'Tracking alert', width: '90',cellTemplate:'<span>{{row.entity.tracking_alert_subscribed == 1 ? \'Yes\' : \'No\'}}</span>' },

                { name: 'user_id', displayName: 'Action', width: '80', cellClass: "overflow-visible setting-icon", cellTemplate: '<div class="dropdown" ng-hide="row.entity.role!=\'user\'"><i class="fa fa-cog fa-2x" data-toggle="dropdown" id="dropdownMenuButton" aria-haspopup="true" aria-expanded="false"></i><ul class="dropdown-menu" aria-labelledby="dropdownMenuButton"><li><a ng-click="grid.appScope.getUser(row.entity.user_id)" >Edit User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.deleteUser(row.entity.user_id)">Delete User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.resetPassword(row.entity.email,row.entity.user_id)" data-toggle="modal" data-target="#animatedModal">Reset Password</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.deactivate(row.entity.user_id,row.entity.status)">{{row.entity.status == "active" ? "Deactivate" : "Activate"}} User</a></li></ul></div>' }

                // { name: 'user_id', displayName: 'Action', width: '80', cellClass: "overflow-visible setting-icon", cellTemplate: '<div class="dropdown"><button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">D</button><div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a></div></div>' }
            ],

            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
            }
        };

        // apiService.post('./../../assets/json/100.json', config)
        apiService.post('/show_users', config)
            .then(function (response) {
                $scope.gridOptionsUser.data = response.data.result;
            });

    }
    // $scope.showUsers();

    $scope.getUser = function(user_id){
        $rootScope.mobileValid = 0;
        $rootScope.usernameValidInCompany = 0;
        $rootScope.usernameValid = 0;
        $('#authy_add_mobile').hide();
        $('#authy_edit_mobile').hide();
        $('#add_mobile').hide();
        $('#edit_mobile').hide();
        $("#options_div"+user_id).css("display", "none");
        $("#advancedModalEdit").modal('show');
        // Get Users
        apiService.post('/get_user_edit',{'user_id':user_id} )
            .then(function(data){
            if(data.data.status){
                $scope.admin_user.user_result                   = data.data.result;
                $scope.admin_user.user_result[0].mobile         = data.data.result[0].phone_number;
                $scope.admin_user.user_result[0].hidden_email   = data.data.result[0].email;
                if($scope.admin_user.user_result[0].assistant_admin == '1'){
                    $('#assistant_admin_edit').prop('checked','checked');
                }
                $('#authy-countries').attr('id','authy_country_removed');
                $('select[name=edit_country_code]').attr('id','authy-countries');
            }
            }, function (response) {
            // this function handles error
            });
    }

    // Delete Users
    $scope.deleteUser = function (user_id) {
        if(confirm("Are you sure want to delete the record?")) {
            // debugger;
            // console.log("user_id "+user_id);
            $("#options_div" + user_id ).css("visibility", "hidden");
            apiService.post('/delete_user_from_company', { 'user_id': user_id, 'company_id': sessionStorage.company_id })
            .then(function (data) {
                if (data.status) {
                        $("#deleteMessage").modal('show');
                        setTimeout(function () {
                            $("#deleteMessage").modal('hide');
                            $state.go($state.current, {}, {reload: true});
                        }, 1000);
                }
            }, function (response){
                // this function handlers error
            });
        }
    }

    $scope.resetPassword = function(username,user_id){
        $('#options_div'+user_id).css('display','none');
        // apiService.showLoader($scope);
         apiService.post('/forgot_password',{'username':username})
         .then(function(data) {
           if(data.status){
             setTimeout(function(){$scope.modal.deactivate(); } , 2000);
             setTimeout(function(){ $('#dialog').click();} , 2000);
           }
         }, function (response){
            // this function handlers error
        });
    }

    $scope.openModal = function() {
        var modalInstance =  $uibModal.open({
            templateUrl: "./templates/modals/MaxLimitTemplate.html",
            controller: "MaxLimitController",
            size: 'md modal-dialog-centered',
            backdrop : false
          });

          modalInstance.result.then(function(response){
              $scope.result = `${response} button hitted`;
          });
    };

    $scope.deactivate = function(user_id,status){
        var displaymessage = status == 'active' ? 'activated' : 'deactivated';
        var defaultOptions = {
            size: 'md modal-dialog-centered'
          }
        var options = {
                bodyText: 'Record '+displaymessage+' successfully.',
                headerText: ' ',
                closeReq: 1
          };

        apiService.post('/deactivate_user',{'user_id':user_id,'status':status} )
           .then(function(response){
            var data = response.data;
               $("#advancedModal").modal('hide');
               $("#advancedModalEdit").modal('hide');
             if(data.status == 1){
                   if(data.max_limit == 'yes'){
                        $scope.openModal();

                        $scope.error_reset = function (){
                            $scope.invalidToken = true;
                        }
                   }else{
                        modalConfirmService.showModal(defaultOptions, options).then(function (result) {
                        });
                        $scope.showUsers();
                    }
               }
           }, function (response) {
               // this function handlers error
        });
    }
});

angular.module('drmApp').controller('MaxLimitController', function($scope, $rootScope, $timeout, $uibModalInstance, $state, apiService, modalConfirmService) {

    $scope.sendMail = function(){
        var defaultOptions = {
            size: 'md modal-dialog-centered'
          }
        var options = {
                bodyText: 'Email sent successfully.',
                headerText: ' ',
                closeReq: 1
          };
        var admin_id = sessionStorage.loggedInUserId;
        apiService.post('/contact_us',{'admin_id' : admin_id })
        .then(function(data) {
          if(data.status){
                modalConfirmService.showModal(defaultOptions, options).then(function (result) {
                });
            }
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.closeModal = function() {
            $uibModalInstance.dismiss();
    }

});