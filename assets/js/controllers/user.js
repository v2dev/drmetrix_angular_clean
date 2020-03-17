angular.module('drmApp').controller('UserController', function ($scope, $timeout, $state, $stateParams, $http, $interval, uiGridTreeViewConstants, $rootScope, apiService, modalConfirmService,  $uibModal) {
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }
    var usCtrl = this;
    $scope.admin_user = {};
    $scope.save_clicked = false;
    $scope.userRowForAction = {};
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

                { name: 'ads_authenticated', displayName:'Adsphere Authenticated', width: '120',enableColumnMenus: false, cellTemplate:'<span class="{{row.entity.ads_authenticated ? \'user-not-verified\' : \'\'}}"  >{{row.entity.ads_authenticated ? \'No\' : \'\'}}</span><span class="{{row.entity.ads_authenticated ? \'user-not-verified-link\' : \'user-verified\'}}" id ="resend_link_{{row.entity.user_id}}" ng-click="grid.appScope.sendPassword(row.entity)" >{{row.entity.ads_authenticated ? \'Resend Email\' : \'Yes\'}}</span>' },

                { name: 'authy_cookie', displayName:'Authy Authenticated', width: '100', cellTemplate:'<span class="{{row.entity.authy_cookie ? \'user-verified\' : \'user-not-verified-authy\'}}">{{row.entity.authy_cookie ? \'Yes\' : \'No\'}}<span>' },

                { name: 'vdate', displayName: 'Verified Date', cellTemplate:'<span>{{row.entity.vdate ? row.entity.vdate : \'-\'}}</span>' },
                { name: 'assistant_admin', displayName: 'Assistant Admin', width: '90',cellTemplate:'<span>{{row.entity.assistant_admin == 1 ? \'Yes\' : \'No\'}}</span>' },

                { name: 'skip_authy', displayName: 'Skip Authy', width: '70', cellClass:'text-center', cellTemplate:'<nav class="grid-content" id="skip_authy"><ul class="no-bullet"><li class="checkbox-normal"><input ui-grid-checkbox type="checkbox" class="checkbox-custom" id="skip_authy_check_{{row.entity.user_id}}" ng-click="grid.appScope.manageSkipAuthy(row.entity)" ng-checked="row.entity.skip_authy == 1 ? true : false " /><label class="checkbox-custom-label"></label></li></ul></nav>' },

                { name: 'status', displayName: 'Status', width: '80', cellTemplate:'<span>{{row.entity.status == "active" ? "Active" : "Inactive"}}</span>' },
                { name: 'last_login', displayName: 'Last Login', cellTemplate:'<span>{{row.entity.last_login}}</span>' },
                { name: 'login_count', displayName:'Total Logins', cellTemplate:'<span>{{row.entity.login_count}}</span>' },
                { name: 'last_30_days_count', displayName:'Last 30 Days Logins', width: '80',cellTemplate:'<span>{{row.entity.last_30_days_count}}</span>' },
                { name: 'tracking_alert_subscribed', displayName:'Tracking alert', width: '90',cellTemplate:'<span>{{row.entity.tracking_alert_subscribed == 1 ? \'Yes\' : \'No\'}}</span>' },

                { name: 'user_id', displayName: 'Action', width: '80', cellClass: "overflow-visible setting-icon", cellTemplate: '<div class="dropdown" ng-hide="row.entity.role!=\'user\'"><i class="fa fa-cog fa-2x" data-toggle="dropdown" id="dropdownMenuButton" aria-haspopup="true" aria-expanded="false"></i><ul class="dropdown-menu" aria-labelledby="dropdownMenuButton"><li><a ng-click="grid.appScope.getUser(row.entity.user_id)" >Edit User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.confirmUserDeletion(row.entity)">Delete User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.resetPassword(row.entity)" data-toggle="modal" data-target="#animatedModal">Reset Password</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.deactivate(row.entity)">{{row.entity.status == "active" ? "Deactivate" : "Activate"}} User</a></li></ul></div>' }

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
        }, function (response){
            // this function handlers error
        });

    }
    // $scope.showUsers();

    $scope.editUser = function(user_id){
        $scope.save_clicked = true;
        var mobile =  $('#mobile_edit').val();
        var country_code =  $('#edit_country_code').val();
        /*if(mobile.length < 12 && (country_code != 61 && country_code != 64) ){
            $('#edit_mobile').show();
            return false;
        }else if((mobile.length < 11 || mobile.length > 11)  && country_code == 61 ){
            $('#edit_mobile').show();
            return false;
        }else if(mobile.length < 10  && country_code == 64 ){//console.log("64");
            $('#edit_mobile').show();
            return false;
        }*/
        var assistant_admin = 0;
        if ($("#assistant_admin_edit").is(":checked")) {
            assistant_admin = 1;
        }

        var first_name          = $scope.admin_user.user_result[0].first_name;
        var last_name           = $scope.admin_user.user_result[0].last_name;
        var username            = $scope.admin_user.user_result[0].username;
        var phone_number        = mobile;//$scope.admin_user.user_result[0].phone_number;
        var position            = $scope.admin_user.user_result[0].position;
        //var assistant_admin = $scope.admin_user.user_result[0].assistant_admin;
        var receive_report      = $scope.admin_user.user_result[0].receive_report;
        var tier                = 1;//$scope.admin_user.user_result[0].tier;
        var country_code        = $scope.admin_user.user_result[0].country_code;
        var role                = 'user';
        var admin_id            = sessionStorage.loggedInUserId;
        var changed             = $('#tier_change').val(); // added this field for editing value other than tier, allow to edit.
        var hidden_email        = $scope.admin_user.user_result[0].hidden_email;

        var postEdit = {'first_name':first_name,'last_name':last_name,'username':username,'mobile':phone_number,'position':position,'receive_report':receive_report,'role':role,'user_id':user_id,'admin_id':admin_id,'tier':tier,'changed':changed,'country_code':country_code,'assistant_admin':assistant_admin ,hidden_email : hidden_email }; 
        $("#edit_user_btn").prop( "disabled", true );
        // if( $scope.admin_user.user_result[0].admin_skip_authy != '1' ) {
            // postEdit.skip_authy = $scope.admin.user_result[0].skip_authy;
            postEdit.skip_authy = $("#skip_authy_edit").is(":checked") ? 1 : 0;
        // }
        apiService.post('/edit_user',postEdit)
        .then(function(response) {
            $("#edit_user_btn").prop( "disabled", false );
            var data = response.data;
            if(data.status == 1){
                if(data.max_limit == 'yes'){
                    $("#advancedModalEdit").modal('hide');
                    $("#advancedModal3").modal('show');
                }else{
                    $("#advancedModalEdit").modal('hide');
                    $("#editMessage").modal('show');
                    //setTimeout(function(){ FoundationApi.publish('editMessage', 'hide'); $scope.showUsers();  } , 1000 );
                    setTimeout(function(){
                        $("#editMessage").modal('hide');
                        $state.go($state.current, {}, {reload: true});
                    } , 1000);
                }
                // modalConfirmService.showModal(defaultOptions, options).then(function (result) {});
            }else if(data.status == 2){
                $("#domain_msg").html(data.domain_msg);
                FoundationApi.publish('domainOverrideMessage', 'show');
            }else if(data.status == 4){
                $("#domain_msg").html(data.domain_msg);
                FoundationApi.publish('domainOverrideMessage', 'show');
            }else{
                $('#authy_edit_mobile').html(data.error);
                $('#authy_edit_mobile').show();
            }
            $scope.save_clicked = false;
        }, function (response){
            // this function handlers error
        });

    }

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
        .then(function(response){
            var data = response.data;
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

    $scope.getAuthyCountries = function() {
        apiService.post('/get_authy_countries',{} )
        .then(function(response){
            var data = response.data;
            if(data.status){
                $scope.admin_user.authy_countries = data.result;
            }
        }, function (response){
            // this function handlers error
        });
    }

    if(sessionStorage.role == 'superadmin'){
        $state.go('adminConsole');
    }else{
         $scope.showUsers();
         $scope.getAuthyCountries();
    }

    $scope.eula_check = function (value) {
        if (value == '1') {
            return true;
        } else {
            return false;
        }
    }

    $scope.manageSkipAuthy = function(rowEntity) {
        $scope.userRowForAction = rowEntity;
        let id = $scope.userRowForAction.user_id;
        var skip_authy_checked = $('#skip_authy_check_' + id).is(":checked") ? 1 : 0;
        apiService.post('/manage_skip_authy', { 'user_id': id, 'skip_authy_checked': skip_authy_checked })
        .then(function(response) {
            var data = response.data;
            if (data.status == 1) {
                $("#editMessage").modal('show');
                //setTimeout(function(){ FoundationApi.publish('editMessage', 'hide'); $scope.showUsers();  } , 1000 );
                /*setTimeout(function(){
                    $("#editMessage").modal('hide');
                    // $state.go($state.current, {}, {reload: true});
                } , 1000);*/
            }
        }, function (response){
            // this function handlers error
        });
    }

    $scope.saveUser = function(  active ){
        if (typeof (active) == 'undefined') {
            active = false;
        }

        $("#domain_msg").html('');
        var mobile =  $('#mobile').val();
        var country_code =  $('#add_country_code').val();
        var timeout = 30;
        var no_of_apps = 1;
        var first_name = $scope.admin_user.first_name;
        var last_name = $scope.admin_user.last_name;
        var username = $scope.admin_user.username;
        var country_code = $scope.admin_user.country_code;
        var mobile = mobile;//$scope.admin_user.mobile;
        var position = $scope.admin_user.position;
        var role = 'user';
        var receive_report = $scope.admin_user.receive_report;
        var admin_id = sessionStorage.loggedInUserId;
        var assistant_admin = 0;
        if ($("#assistant_admin").is(":checked")) {
            assistant_admin = 1;
        }
        var tier = 1;//$scope.admin_user.users['tiers'][0].id;//$scope.admin_user.tier;

        var postS = {'first_name':first_name, 'last_name':last_name, 'username':username, 'mobile':mobile,'position':position, 'role':role,'receive_report':receive_report,'admin_id':admin_id,'tier':tier,'country_code':country_code,'timeout':timeout,'no_of_apps':no_of_apps,'assistant_admin':assistant_admin, 'active' :active };
        if(active != ''){
            postS.user_id  =  $('.user_ads_id').text() ;
            postS.zoho_user_id =  $('.user_zoho_id').text();
        }
        $("#save_user_btn").prop( "disabled", true );
        apiService.post('/save_user',postS)
        .then(function(response) {
            var data = response.data;
            $("#save_user_btn").prop( "disabled", false );
            if(data.status == 1){
                $('#activateUserExists').modal('hide');
                $('#inactivateUserExists').modal('hide');
                if(data.max_limit == 'yes'){
                    $('#add_user')[0].reset();
                    $('#advancedModal').modal('hide');
                    $('#advancedModal3').modal('show');
                }else{
                    $('#add_user')[0].reset();
                    $('#advancedModal').modal('hide');
                    $('#saveMessage').modal('show');
                    //$scope.email(username,data.comp_name);
                    setTimeout(function(){
                        $('#saveMessage').modal('hide');
                        // window.location.href = '/drmetrix/userAccount';
                        $state.go($state.current, {}, {reload: true});
                    } , 1000 );
                }
            }else if(data.status == 4){
                $("#domain_msg").html(data.domain_msg);
                $('#domainOverrideMessage').modal('show');
            }else if(data.status == 2){
                $("#error_msg_span").html(data.error_msg);
                $('#errorMsg').modal('show');
            }else if(data.status == 3){
                $("#user_zoho_msg").html(data.err_zoho_user_msg);
                $('#userExistsZohoMsg').modal('show');
            } else if(data.status == 5) {
                $(".email_exists_id").html(data.userInfo.ADS_Username);
                $(".active_user_zoho_id").html(data.userInfo.id);
                $(".active_user_ads_id").html(data.userInfo.ADS_Record_ID);
                $('#inactivateUserExists').modal('hide');
                $('#activateUserExists').modal('show');
                $("#type_save_active").text(data.type);
            } else if(data.status == 6) {
                $(".email_exists_id").html(data.userInfo.ADS_Username);
                $(".user_zoho_id").html(data.userInfo.id);
                $(".user_ads_id").html(data.userInfo.ADS_Record_ID);

                $('#inactivateUserExists').modal('show');
                $("#type_save_deactive").text(data.type);
            }else if(data.status == 7) {
                $('#add_user')[0].reset();
                $('#advancedModal').modal('hide');
                $('#inactivateUserExists').modal('hide');
                $('#activateUserExists').modal('hide');
                $('#editMessage', 'show');
                setTimeout(function(){
                    $('#editMessage').modal('hide');
                    // window.location.href = '/drmetrix/userAccount';
                    $state.go($state.current, {}, {reload: true});
                } , 1000 );
            }else{
                $('#authy_add_mobile').html(data.error);
                $('#authy_add_mobile').show();
            }
        }, function (response){
            // this function handlers error
        });

        //$scope.email(username);
    }

    // Delete Users
    $scope.confirmUserDeletion = function (rowEntity) {
        $scope.userRowForAction = rowEntity;
        $("#deleteUser").modal('show');
    }
    $scope.deleteUser = function () {
        let user_id = $scope.userRowForAction.user_id;
        $("#deleteUser").modal('hide');
        // if(confirm("Are you sure want to delete the record?")) {
            $("#options_div" + user_id ).css("visibility", "hidden");
            apiService.post('/delete_user_from_company', { 'user_id': user_id, 'company_id': sessionStorage.company_id })
            .then(function (response) {
                var data = response.data;
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
        // }
    }

    $scope.sendMail = function(){
        //var tier = $scope.admin_user.tier;
        var admin_id = sessionStorage.loggedInUserId;
        //'/contact_us',{tier : tier ,admin_id : admin_id }
        apiService.post('/contact_us',{'admin_id' : admin_id })
        .then(function(response) {
            var data = response.data;
            if(data.status){
                $("#emailModal").modal('show');
                setTimeout(function(){ $("#emailModal").modal('hide'); } , 1000 );
            }
        }, function (response){
            // this function handlers error
        });
    }

    $scope.resetPassword = function(rowEntity){
        $scope.userRowForAction = rowEntity;
        let username = $scope.userRowForAction.email;
        let user_id = $scope.userRowForAction.user_id;
        $('#options_div'+user_id).css('display','none');
        // apiService.showLoader($scope);
        apiService.post('/forgot_password',{'username':username})
        .then(function(response) {
            var data = response.data;
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

    $scope.deactivate = function(rowEntity){
        $scope.userRowForAction = rowEntity;
        let status = $scope.userRowForAction.status;
        let user_id = $scope.userRowForAction.user_id;
        var displaymessage = status == 'active' ? 'deactivated' : 'activated';
        var defaultOptions = {
            size: 'md modal-dialog-centered'
          }
        var options = {
                bodyText: 'User '+displaymessage+' successfully.',
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
                    modalConfirmService.showModal(defaultOptions, options).then(function (result) {});
                    // $scope.showUsers();
                    $state.go($state.current, {}, {reload: true});
                }
            }
        }, function (response) {
               // this function handlers error
        });
    }

    $scope.verifyDuplicateMobile = function (mobile) {
        // mobile = $scope.admin_user.user_result[0].phone_number;
        //var admin_id = $('#admin_id').val();
        var user_id    = $('#edit_data_user_id').val();
        var admin_id   = sessionStorage.admin_id;
        var hidden_mobile_no = $("#mobile_edit_hidden").val();
        if ($('[id="advancedModalEdit"]').hasClass('is-active')) {
            hidden_mobile_no = $("#mobile_edit_company_hidden").val();
        }

        if(sessionStorage.role == 'superadmin'){
            admin_id = $('#edit_company_admin_id').val();
            if(admin_id == ''){
                admin_id = $('#edit_company_page_admin_id').val();
            }
            if($('#admin_id').val() != '') {
                admin_id = $('#admin_id').val();
            }
        }

        apiService.post('/check_mobile', { 'mobile': mobile , 'admin_id' : admin_id, 'user_id' : user_id, 'hidden_mobile_no' : hidden_mobile_no})
        .then(function (response) {
            var data = response.data;
            if (data.status) {
                if (data.valid) {
                    $scope.errors = 1;
                    $scope.mobileValid = 1; //mobile invalid
                    $('#duplicate_mobile').css('display', 'block');
                } else {
                    $scope.errors = 0;
                    $scope.mobileValid = 0; //mobile valid
                }
            }
        }, function (response){
            // this function handlers error
        });
    }

    $scope.verifyDuplicateEmail = function (username, id) {
        /*let id = $scope.admin_user.user_result[0].user_id;
        let username = $scope.admin_user.user_result[0].username;*/
        apiService.post('/check_email', { 'email': username, 'user_id': id })
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    if (data.valid) {
                        $scope.errors = 1;
                        if(sessionStorage.company_id == data.result[0].company_id){
                            $scope.email_user_id = data.user_result[0].user_id;
                            $scope.usernameValidInCompany = 1;
                            $scope.usernameValid = 0;
                            // $('#duplicate_username').css('display', 'block');
                        } else {
                            $scope.usernameValid = 1; //username already exists.
                            $scope.usernameValidInCompany = 0;
                            $('#duplicate_username').css('display', 'block');
                        }
                    } else {
                        $scope.errors = 0;
                        $scope.usernameValid = 0; //username not exists.
                        $scope.usernameValidInCompany = 0;
                    }
                }
            }, function (response){
                // this function handlers error
            });
    }

    $scope.sendPassword = function(rowEntity){
        $scope.userRowForAction = rowEntity;
        let user_id = $scope.userRowForAction.user_id;
        let passphrase = $scope.userRowForAction.passphrase;
        if(passphrase ==''){
            return false;
        }
        $('#resend_link_'+user_id).addClass('resend_email');
        apiService.post('/regenerate_password',{'user_id':user_id,'type':'admin'})
        .then(function(response) {
            var data = response.data;
            if(data.status){
                $('#regenerate').modal('show');
                setTimeout(function(){ $('#regenerate').modal('hide'); } , 1000 );
                setTimeout(function(){ $('#resend_link_'+user_id).removeClass('resend_email');  } , 120000 );
            }
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.openAddUser = function(){
        //  $('#authy-countries').attr('id','authy_country_removed');
        //  $('select[name=country_code]').attr('id','authy-countries');
        //  $('.add_authy_country_code').html(add_authy_country_code_html);
        $rootScope.mobileValid = 0;
        $rootScope.usernameValidInCompany = 0;
        $rootScope.usernameValid = 0;
        $('#authy_add_mobile').hide();
        $('#authy_edit_mobile').hide();
        $('#add_mobile').hide();
        $('#edit_mobile').hide();
        $('#advancedModal').modal('show');
        //  $('#admin_id').val(sessionStorage.admin_id);
        $('#duplicate_mobile').css('display','none');
        $('#add_user')[0].reset();
        $scope.admin_user.first_name = '';
        $scope.admin_user.last_name = '';
        $scope.admin_user.username = '';
        $scope.admin_user.mobile = '';
        $scope.admin_user.admin_id = sessionStorage.admin_id;
        $scope.admin_user.add_user.$setPristine();
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
        .then(function(response) {
            var data = response.data;
            if(data.status){
                modalConfirmService.showModal(defaultOptions, options).then(function (result) {});
            }
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

});
