angular.module('drmApp').controller('UserController', function ($scope, $timeout, $http, $interval, uiGridTreeViewConstants, $rootScope, apiService) {
    var usCtrl = this;
    $scope.showUsers = function () {
        var config = {
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var correctTotalPaginationTemplate =
    //same as normal template, but fixed totals:  {{(((grid.options.paginationCurrentPage-1)*grid.options.paginationPageSize)+1)}}   {{(grid.options.paginationCurrentPage*grid.options.paginationPageSize>grid.options.totalItems?grid.options.totalItems:grid.options.paginationCurrentPage*grid.options.paginationPageSize)}}
    "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-triangle\"><div class=\"first-bar\"></div></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-triangle prev-triangle\"></div></button> <input type=\"number\" ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\">/</abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"last-triangle next-triangle\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-triangle\"><div class=\"last-bar\"></div></div></button></div></div><div class=\"ui-grid-pager-count-container\"><div class=\"ui-grid-pager-count\"><span ng-show=\"grid.options.totalItems > 0\">{{(((grid.options.paginationCurrentPage-1)*grid.options.paginationPageSize)+1)}} <abbr ui-grid-one-bind-title=\"paginationThrough\">-</abbr> {{(grid.options.paginationCurrentPage*grid.options.paginationPageSize>grid.options.totalItems?grid.options.totalItems:grid.options.paginationCurrentPage*grid.options.paginationPageSize)}} {{paginationOf}} {{grid.options.totalItems}} {{totalItemsLabel}}</span></div></div></div>";
        $scope.gridOptionsUser = {};
        $scope.gridOptionsUser = {
            //Pagination
            paginationPageSize: 10,
            rowHeight: 30,
            paginationTemplate: correctTotalPaginationTemplate,
            columnDefs: [
                { name: 'name', displayName:'Name', cellTemplate:'<span title="{{row.entity.name == \'\' ? \'-\' : row.entity.name}}" >{{row.entity.name == \'\' ? \'-\' : row.entity.name | limitTo: 15}}{{row.entity.name.length > 15 ? \'...\' : \'\'}}</span>' },

                { name: 'username', displayName:'Username', cellTemplate:'<span title="{{row.entity.country_code}}{{row.entity.username == \'\' ? \'-\' : row.entity.username}}" >{{row.entity.username == \'\' ? \'-\' : row.entity.username | limitTo: 20}}{{row.entity.username.length > 20 ? \'...\' : \'\'}}</span>' },
                { name: 'phone_number', displayName:'Mobile #', cellTemplate:'<span title="+{{row.entity.country_code}}{{row.entity.phone_number}}" >+{{row.entity.country_code}}{{row.entity.phone_number}}</span>' },

                { name: 'position', displayName:'Position', cellTemplate:'<span title="{{row.entity.position}}" >{{row.entity.position}}</span>' },
                { name: 'role', displayName:'role', width: '70',cellTemplate:'<span title="{{row.entity.role}}" >{{row.entity.role}}</span>' },

                { name: 'ads_authenticated', displayName:'Adsphere Authenticated', enableColumnMenus: false, cellTemplate:'<span class="{{row.entity.ads_authenticated ? \'user-not-verified\' : \'\'}}"  >{{row.entity.ads_authenticated ? \'No\' : \'\'}}</span><span class="{{row.entity.ads_authenticated ? \'user-not-verified-link\' : \'user-verified\'}}" id ="resend_link_{{row.entity.user_id}}" ng-click="sendPassword(row.entity.passphrase,row.entity.user_id)" >{{row.entity.ads_authenticated ? \'Resend Email\' : \'Yes\'}}</span>' },

                { name: 'authy_cookie', displayName:'Authy Authenticated', cellTemplate:'<span class="{{row.entity.authy_cookie ? \'user-verified\' : \'user-not-verified-authy\'}}">{{row.entity.authy_cookie ? \'Yes\' : \'No\'}}<span>' },

                { name: 'vdate', displayName: 'Verified Date', cellTemplate:'<span>{{row.entity.vdate ? row.entity.vdate : \'-\'}}</span>' },
                { name: 'assistant_admin', displayName: 'Assistant Admin', cellTemplate:'<span>{{row.entity.assistant_admin == 1 ? \'Yes\' : \'No\'}}</span>' },

                { name: 'skip_authy', displayName: 'Skip Authy', width: '70', cellTemplate:'<nav class="grid-content" id="skip_authy"><ul class="no-bullet"><li><input ui-grid-checkbox type="checkbox" class="" id="skip_authy_check_{{row.entity.user_id}}" ng-click="manageSkipAuthy(row.entity.user_id)" ng-checked="row.entity.skip_authy == 1 ? true : false " /><label class="checkbox-custom-label"></label></li></ul></nav>' },

                { name: 'status', displayName: 'Status', width: '80', cellTemplate:'<span>{{row.entity.status == "active" ? "Active" : "Inactive"}}</span>' },
                { name: 'last_login', displayName: 'Last Login', cellTemplate:'<span>{{row.entity.last_login}}</span>' },
                { name: 'login_count', displayName:'Total Logins', cellTemplate:'<span>{{row.entity.login_count}}</span>' },
                { name: 'last_30_days_count', displayName:'Last 30 Days Logins', cellTemplate:'<span>{{row.entity.last_30_days_count}}</span>' },
                { name: 'tracking_alert_subscribed', displayName:'Tracking alert', cellTemplate:'<span>{{row.entity.tracking_alert_subscribed == 1 ? \'Yes\' : \'No\'}}</span>' },

                { name: 'user_id', displayName: 'Action', width: '80', cellClass: "overflow-visible", cellTemplate: '<div class="dropdown"><i class="fa fa-cog fa-2x" data-toggle="dropdown"></i><ul class="dropdown-menu"><li><a ng-click="grid.appScope.getUser(row.entity.user_id)" >Edit User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.deleteUser(row.entity.user_id)">Delete User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.resetPassword(row.entity.email,row.entity.user_id)" data-toggle="modal" data-target="#animatedModal">Reset Password</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.deactivate(row.entity.user_id,row.entity.status)">{{row.entity.status == "active" ? "Deactivate" : "Activate"}} User</a></li></ul></div>' }

                // { name: 'user_id', displayName: 'Action', width: '80', cellClass: "overflow-visible", cellTemplate: '<a ng-click="grid.appScope.getUser(row.entity.user_id)" >Edit User</a>' }
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
        debugger;
        console.log(user_id);
        $rootScope.mobileValid = 0;
        $rootScope.usernameValidInCompany = 0;
        $rootScope.usernameValid = 0;
        $('#authy_add_mobile').hide();
        $('#authy_edit_mobile').hide();
        $('#add_mobile').hide();
        $('#edit_mobile').hide();
        $("#options_div"+user_id).css("display", "none");
        $("#advancedModalEdit").modal('hide');

       apiService.post('/get_user_edit',{'user_id':user_id} )
           .then(function(data){
               console.log(data);
               console.log(data.status);
            //    console.log(data.result);
             if(data.status){
                $scope.admin_user.user_result                   = data.result;
                $scope.admin_user.user_result[0].mobile         = data.result[0].phone_number;
                $scope.admin_user.user_result[0].hidden_email   = data.result[0].email;
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
});