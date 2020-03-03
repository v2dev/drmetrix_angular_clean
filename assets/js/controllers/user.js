angular.module('drmApp').controller('UserController', function ($scope, $timeout, $http, $interval, uiGridTreeViewConstants, $rootScope, apiService) {
    var usCtrl = this;
    $scope.showUsers = function () {
        var config = {
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        $scope.gridOptionsUser = {};
        $scope.gridOptionsUser = {
            columnDefs: [
                { name: 'name', displayName:'Name', cellTemplate:'<span title="{{row.entity.name == \'\' ? \'-\' : row.entity.name}}" >{{row.entity.name == \'\' ? \'-\' : row.entity.name | limitTo: 15}}{{row.entity.name.length > 15 ? \'...\' : \'\'}}</span>' },

                { name: 'username', displayName:'Username', cellTemplate:'<span title="{{row.entity.country_code}}{{row.entity.username == \'\' ? \'-\' : row.entity.username}}" >{{row.entity.username == \'\' ? \'-\' : row.entity.username | limitTo: 20}}{{row.entity.username.length > 20 ? \'...\' : \'\'}}</span>' },
                { name: 'phone_number', displayName:'Mobile #', cellTemplate:'<span title="+{{row.entity.country_code}}{{row.entity.phone_number}}" >+{{row.entity.country_code}}{{row.entity.phone_number}}</span>' },

                { name: 'position', displayName:'Position', cellTemplate:'<span title="{{row.entity.position}}" >{{row.entity.position}}</span>' },
                { name: 'role', displayName:'role', cellTemplate:'<span title="{{row.entity.role}}" >{{row.entity.role}}</span>' },

                { name: 'ads_authenticated', displayName:'Adsphere Authenticated', cellTemplate:'<span class="{{row.entity.ads_authenticated ? \'user-not-verified\' : \'\'}}"  >{{row.entity.ads_authenticated ? \'No\' : \'\'}}</span><span class="{{row.entity.ads_authenticated ? \'user-not-verified-link\' : \'user-verified\'}}" id ="resend_link_{{row.entity.user_id}}" ng-click="sendPassword(row.entity.passphrase,row.entity.user_id)" >{{row.entity.ads_authenticated ? \'Resend Email\' : \'Yes\'}}</span>' },

                { name: 'authy_cookie', displayName:'Authy Authenticated', cellTemplate:'<span class="{{row.entity.authy_cookie ? \'user-verified\' : \'user-not-verified-authy\'}}">{{row.entity.authy_cookie ? \'Yes\' : \'No\'}}<span>' },

                { name: 'vdate', displayName: 'Verified Date', cellTemplate:'<span>{{row.entity.vdate ? row.entity.vdate : \'-\'}}</span>' },
                { name: 'assistant_admin', displayName: 'Assistant Admin', cellTemplate:'<span>{{row.entity.assistant_admin == 1 ? \'Yes\' : \'No\'}}</span>' },

                { name: 'skip_authy', displayName: 'Skip Authy', cellTemplate:'<nav class="grid-content" id="skip_authy"><ul class="no-bullet"><li><input ui-grid-checkbox type="checkbox" class="" id="skip_authy_check_{{row.entity.user_id}}" ng-click="manageSkipAuthy(row.entity.user_id)" ng-checked="row.entity.skip_authy == 1 ? true : false " /><label class="checkbox-custom-label"></label></li></ul></nav>' },

                { name: 'status', displayName: 'Status', cellTemplate:'<span>{{row.entity.status == "active" ? "Active" : "Inactive"}}</span>' },
                { name: 'last_login', displayName: 'Last Login', cellTemplate:'<span>{{row.entity.last_login}}</span>' },
                { name: 'login_count', displayName:'Total Logins', cellTemplate:'<span>{{row.entity.login_count}}</span>' },
                { name: 'last_30_days_count', displayName:'Last 30 Days Logins', cellTemplate:'<span>{{row.entity.last_30_days_count}}</span>' },
                { name: 'tracking_alert_subscribed', displayName:'Tracking alert', cellTemplate:'<span>{{row.entity.tracking_alert_subscribed == 1 ? \'Yes\' : \'No\'}}</span>' },

                { name: 'user_id', displayName: 'Action', cellTemplate: '<div class="dropdown"><button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Actions</button><ul class="dropdown-menu"><li><a href="#">HTML</a></li><li><a href="#">CSS</a></li><li><a href="#">JavaScript</a></li></ul></div>' }
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
    $scope.showUsers();

});