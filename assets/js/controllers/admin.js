angular.module('drmApp').controller('AdminController', function ($scope, $timeout, $state, $stateParams, $filter, $interval, uiGridConstants, $rootScope, apiService, modalConfirmService, $uibModal) {
    $scope.admin = {};
    $scope.admin.complete_name =  $rootScope.complete_name = localStorage.complete_name;
    $scope.save_clicked = false;
    $scope.userRowForAction = {};
    $scope.ranking = {searchText: ''};

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
            enableGridMenu: true,
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

                { name: 'name', displayName: 'Name', width: '100', cellTemplate: '<span title="{{row.entity.name}}" >{{row.entity.name}}</span>' },
                { name: 'username', displayName: 'Admin Username', width: '150', cellTemplate: '<span>{{row.entity.username ? row.entity.username : \'-\'}}</span>' },
                { name: 'phone_number', displayName: 'Admin Mobile#', width: '90', cellTemplate: '<span title="+{{row.entity.country_code}}{{row.entity.phone_number}}" >+{{row.entity.country_code}}{{row.entity.phone_number}}</span>' },

                { name: 'adsphere_authenticate', displayName: 'Adsphere Authenticate', width: '170', cellTemplate: '<span class="{{row.entity.adsphere_authenticate == 0 ? \'user-not-verified\' : \'\'}}"  >{{row.entity.adsphere_authenticate  == 0 ? \'No\' : \'\'}}</span><span class="{{row.entity.adsphere_authenticate  == 0 ? \'user-not-verified-link\' : \'user-verified\'}}" id ="resend_link_{{row.entity.user_id}}" ng-click="grid.appScope.sendPassword(row.entity.passphrase, row.entity.user_id)" >{{row.entity.adsphere_authenticate == 0 ? \'Resend Email\' : \'Yes\'}}</span>' },

                { name: 'authy_cookie', displayName: 'Authy Authenticated', width: '170', cellTemplate: '<span class="{{row.entity.authy_cookie ? \'user-verified\' : \'user-not-verified\'}}" >{{row.entity.authy_cookie ? \'Yes\' : \'No\'}}</span>' },

                { name: 'vdate', displayName: 'Verified Date', width: '80', cellTemplate: '<span>{{row.entity.vdate ? row.entity.vdate : \'-\'}}</span>' },
                { name: 'eula_flag', displayName: 'Eula Overried', width: '60', cellTemplate: '<nav><ul class="no-bullet"><li class="checkbox-normal"><input type="checkbox" class="checkbox-custom" id="{{row.entity.company_id}}" ng-click="grid.appScope.eulaOverride(row.entity.company_id)" ng-checked="row.entity.eula_flag == 1 ? true : false" /><label class="checkbox-custom-label"></label></li></ul></nav>' },
                { name: 'network_tab', displayName: 'Network Tab', width: '60', cellTemplate: '<nav><ul class="no-bullet"><li class="checkbox-normal"><input type="checkbox" class="checkbox-custom" id="network_tab_check_{{row.entity.company_id}}" ng-click="manageNetworkTab(row.entity.company_id)" ng-checked="row.entity.network_tab == 1 ? true : false" /><label class="checkbox-custom-label"></label></li></ul></nav>' },
                { name: 'staging_access', displayName: 'Staging Access', width: '80', cellTemplate: '<nav><ul class="no-bullet"><li class="checkbox-normal"><input type="checkbox" class="checkbox-custom" id="staging_access_check_{{row.entity.company_id}}" ng-click="manageStagingAccess(row.entity.company_id)" ng-checked="row.entity.staging_access == 1 ? true : false" /><label class="checkbox-custom-label"></label></li></ul></nav>' },                
                { name: 'skip_authy', displayName: 'Skip Authy', width: '90', cellTemplate: '<nav><ul class="no-bullet"><li class="checkbox-normal"><input type="checkbox" class="checkbox-custom" id="skip_authy_check_{{row.entity.company_id}}" ng-click="manageSkipAuthy(row.entity.company_id)" ng-checked="row.entity.skip_authy == 1 ? true : false" /><label class="checkbox-custom-label"></label></li></ul></nav>' },
                { name: 'status', displayName: 'Status', width: '80', cellTemplate: '<span>{{row.entity.status == "active" ? "Active" : "Inactive"}}</span>' },

                { name: 'user_id', displayName: 'Action', width: '80', cellClass: "overflow-visible setting-icon", cellTemplate: '<div class="dropdown"><i class="fa fa-cog fa-2x" data-toggle="dropdown" id="dropdownMenuButton" aria-haspopup="true" aria-expanded="false"></i><ul class="dropdown-menu" aria-labelledby="dropdownMenuButton"><li><a ng-click="grid.appScope.getCompany(row.entity)" >Edit Company</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.confirmUserDeletion(row.entity)">Delete Company</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.deactivate(row.entity.user_id, row.entity.status)" data-toggle="modal">{{row.entity.status == "active" ? "Deactivate" : "Activate"}} Company</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.assignAdminId(row.entity.user_id, row.entity.company_id)">Add User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.getUsers(row.entity)">User Information</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.getUsersInfo(row.entity)">Change Admin Of Company</a></li></ul></div>' }
            ],

            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.grid.registerRowsProcessor( $scope.singleFilter, 200 );
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

    $scope.filterGridWithSearchText = function() {
        $scope.gridApi.grid.refresh();
    }

    $scope.singleFilter = function( renderableRows ){
        var matcher = new RegExp($scope.ranking.searchText);
        renderableRows.forEach( function( row ) {
          var match = false;
          [ 'company_name', 'name' ].forEach(function( field ){
            if ( row.entity[field].match(matcher) ){
              match = true;
            }
          });
          if ( !match ){
            row.visible = false;
          }
        });
        return renderableRows;
      };

    $scope.manageSkipAuthy = function(id) {
        var skip_authy_checked = $('#skip_authy_check_' + id).is(":checked") ? 1 : 0;

        $.post('/drmetrix/api/index.php/manage_skip_authy', { 'company_id': id, 'skip_authy_checked': skip_authy_checked },
            function (data, status) {
                var response = jQuery.parseJSON(data);
                if (response.status == 1) {
                    console.log("done")
                }
            }).error(function () {
                console.log("error");
            });
    }

    $scope.getUsersInfo = function (rowEntity) {
        $scope.admin.selected_users         = '';
        $scope.admin.selected_company_users = '';
        $scope.admin.change_users           = '';
        $scope.admin.company_id             = sessionStorage.company_id = rowEntity.company_id;
        $scope.admin.delete_user            = '';
        $scope.openModal('./templates/modals/advancedModalChangeAdmin.html');
        $("#list_admin").html('Loading Admin...');
        $("#list_users").html('Loading Users...');
        $rootScope.usr = rowEntity.user_id;
        apiService.post('/get_users_of_company', { 'company_id': rowEntity.company_id })
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    var html_admin = '<ul name="admin_user" id="admin_user" style="margin-left:0px;">';
                    var html_users = '<select name="change_users" id="users" ng-model="admin.change_users" required>';
                    $.each(data.result, function (key, value) {
                        if (value.role == 'admin') {
                            html_admin = html_admin + '<input type="hidden" id="hidden_admin_id" ng-model="admin.hidden_admin_id" value="' + value.user_id + '" /></span><li value=' + value.user_id + ' >' + value.first_name + ' ' + value.last_name + '</li>';
                        } else if (value.role == 'user') {
                            $scope.admin.selected_users = data.result;
                            html_users = html_users + '<option custom_attr_status="'+value.status+'" value= ' + value.user_id + ' > ' + value.first_name + ' ' + value.last_name + '</li>';
                        }
                    });
                    html_admin = html_admin + '</ul>';
                    html_users = html_users + '</select>';
                    $("#list_admin").html(html_admin);
                    $("#list_users").html(html_users);
                }
            }, function (response){
                // this function handlers error
            });
    }

    $scope.getUsers = function (rowEntity) {
        $scope.admin.selected_users         = '';
        $scope.admin.selected_company_users = '';
        $scope.admin.change_users           = '';
        $scope.admin.company_id             = sessionStorage.company_id = rowEntity.company_id;
        $scope.admin.delete_user            = '';
        $scope.openModal('./templates/modals/advancedModalDeleteUser.html');
        $("#list_admin").html('Loading Admin...');
        $("#list_users").html('Loading Users...');
        $rootScope.usr = rowEntity.user_id;
        apiService.post('/get_users_of_company', { 'company_id': rowEntity.company_id })
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    if ($('#advancedModalDeleteUser').hasClass('is-active')) {
                        $scope.admin.selected_company_users = data.result;
                        $.each(data.result, function (key, value) {
                            if (value.role == 'admin') {
                                $scope.admin.company_admin_id = value.user_id;
                            }
                        });
                    } else {
                        var html_admin = '<ul name="admin_user" id="admin_user" style="margin-left:0px;">';
                        var html_users = '<select name="change_users" id="users" ng-model="admin.change_users" required>';
                        $.each(data.result, function (key, value) {
                            if (value.role == 'admin') {
                                html_admin = html_admin + '<input type="hidden" id="hidden_admin_id" ng-model="admin.hidden_admin_id" value="' + value.user_id + '" /></span><li value=' + value.user_id + ' >' + value.first_name + ' ' + value.last_name + '</li>';
                            } else if (value.role == 'user') {
                                $scope.admin.selected_users = data.result;
                                html_users = html_users + '<option custom_attr_status="'+value.status+'" value= ' + value.user_id + ' > ' + value.first_name + ' ' + value.last_name + '</li>';
                            }
                        });
                        html_admin = html_admin + '</ul>';
                        html_users = html_users + '</select>';
                        $("#list_admin").html(html_admin);
                        $("#list_users").html(html_users);
                    }
                }
            }, function (response){
                // this function handlers error
            });
    }

    $scope.changeAdminOfCompany = function () {
        $("#change_admin_ok").prop("disabled", true);
        var company_id = $scope.admin.company_id;
        var hidden_admin_id = $("#hidden_admin_id").val();
        var user_id = $("#users").val();
        apiService.post('/change_admin', { 'company_id': company_id, 'admin_id': hidden_admin_id, 'user_id': user_id })
            .then(function (data) {
                if (data.status) {
                    $("#change_admin_ok").prop("disabled", false);
                    $scope.modalInstanceMain.close();
                    $scope.showPopup('', 'Admin changed for company successfully.', 'Changed Message', '');
                    setTimeout(function(){
                        $scope.modalInstance.close();
                    } , 1000);
                }
            }, function (response){
                // this function handlers error
            });
    }

    $scope.editUser = function(user_id){
        var mobile =  $('#mobile_user_edit').val();
         var country_code =  $('#edit_country_code').val();
         var assistant_admin = 0;
         if ($("#assistant_admin_edit").is(":checked")) {
             assistant_admin = 1;
         }
         var first_name         = $scope.admin.user_result[0].first_name;
         var last_name          = $scope.admin.user_result[0].last_name;
         var username           = $scope.admin.user_result[0].username;
         var phone_number       = mobile;
         var position           = $scope.admin.user_result[0].position;
         var receive_report     = $scope.admin.user_result[0].receive_report;
         var tier               = 1;
         var country_code       = $scope.admin.user_result[0].country_code;
         var role               = 'user';
         var admin_id           = sessionStorage.company_admin_id ;
         var hidden_email       = $scope.admin.user_result[0].hidden_email;
         var sendPasswordFlag   = 0;
         var changed            = $('#tier_change').val(); // added this field for editing value other than tier, allow to edit.
        if(hidden_email != username &&  $scope.admin.user_result[0].adsphere_authenticate == 0) {
            sendPasswordFlag = $scope.sendPassword($scope.admin.user_result[0].passphrase , user_id );
        } else {
            sendPasswordFlag = 1;
        }
        if(sendPasswordFlag) {
            var postEdit = {'first_name':first_name,'last_name':last_name,'username':username,'mobile':phone_number,'position':position,'receive_report':receive_report,'role':role,'user_id':user_id,'admin_id':admin_id,'tier':tier,'changed':changed,'country_code':country_code,'assistant_admin':assistant_admin , 'hidden_email' : hidden_email}; 
            $("#edit_user_btn").prop( "disabled", true );
            if( $scope.admin.user_result[0].admin_skip_authy != '1' ) {
                // postEdit.skip_authy = $scope.admin.user_result[0].skip_authy;
                postEdit.skip_authy = $("#skip_authy_edit").is(":checked") ? 1 : 0;
            }
            apiService.post('/edit_user',postEdit)
            .then(function(response) {
                let data = response.data;
                $("#edit_user_btn").prop( "disabled", false );
                $scope.modalInstanceMain.close();
                if(data.status == 1){
                if(data.max_limit == 'yes'){
                    $scope.openModal('./templates/modals/advancedModal3.html');
                    setTimeout(function(){
                        $scope.modalInstance.close();
                    } , 1000 );
                }else{
                    $scope.showPopup('', 'Record updated successfully.', 'Edit Message', '');
                    setTimeout(function(){
                        $scope.modalInstance.close();
                        $scope.modalInstanceMain.close();
                    } , 1000 );
                }
                }else if(data.status == 2){
                    $("#domain_msg").html(data.domain_msg);
                    $scope.openModal('./templates/modals/domainOverrideMessage.html');
                    setTimeout(function(){ $scope.modalInstance.close(); } , 1000 );
                }else if(data.status == 4){
                    $("#domain_msg").html(data.domain_msg);
                    $scope.openModal('./templates/modals/domainOverrideMessage.html');
                    setTimeout(function(){ $scope.modalInstance.close(); } , 1000 );
                }else{
                    $('#authy_edit_mobile').html(data.error);
                    $('#authy_edit_mobile').show();
                }
            }, function (response){
                // this function handlers error
            });
        }
    }

    $scope.saveCompany = function (create ) {
        if (typeof (create) == 'undefined') {
            create = false;
        }
        var mobile          = $('#mobile').val();
        var country_code    = $('#add_country_code').val();
        var a_limit         = '';

        var access_type_p = $("#access_type_p");
        var account_owner = $('#account_owner').val();

        if (account_owner.length == 0) {
            $('#err_owner').show();
            return false;
        } else {
            $('#err_owner').hide();
        }

        var country_code = $scope.admin.country_code;
        var timeout     = 30;
        var no_of_apps  = 1;
        var first_name  = $scope.admin.first_name;
        var last_name   = $scope.admin.last_name;
        var company_name = $scope.admin.company_name;
        var company_type = $scope.admin.company_type;
        var company_size = $scope.admin.company_size;
        var revenue      = $('#revenue').val();

        var username = $scope.admin.username;
        var mobile = mobile;
        var billing = $scope.admin.billing;
        var tier = $scope.admin.tier;
        var users_limit = $scope.admin.users_limit;
        var download_limit = $scope.admin.download_limit;
        var amount = $scope.admin.amount;
        var client = $scope.admin.client;
        var domain_override = $scope.admin.domain_override;
        var account_owner_zoho_id = $("#account_owner option:selected").attr("cust-attr-id");
        var access_type = 'F';
        var zoho_account_id;
        var zoho_account_name;
        var domain_account_name;
        if (access_type_p.is(':checked') == true) {
            var access_type = 'P';
        }
        if(create == 'use_same') {
            zoho_account_id     = $('.zoho_account_id').text();
            zoho_account_name   = $('.zoho_account_name').text();
        }

        if($('#domainExistsDifferentAccount').hasClass('is-active') && create != 'new_contact_ads'){
            var span_ele = $.trim($('#page_set').text());
            if(span_ele == 'ADS') {
                create = 'use_same_ads';
                $scope.backToAdmin('domain_found_ADS');
                return;
            }
            if(span_ele == 'ZOHO') {
                create = 'use_same_zoho';
            }
        }

        if(create == 'use_same_ads' ||create == 'use_same_zoho') {
            domain_account_name     = $('#domain_found_account_add').text();
        }

        var postS = {'company_name':company_name, 'company_type':company_type, 'company_size':company_size, 'revenue':revenue,'username':username, 'mobile':mobile, 'billing':billing, 'tier':tier,'users_limit':users_limit, 'amount':amount,'country_code':country_code,'timeout':timeout,'no_of_apps':no_of_apps,'first_name':first_name,'last_name':last_name,'access_type':access_type,'monthly_cap':a_limit,'client':client,'account_owner':account_owner,'account_owner_zoho_id':account_owner_zoho_id,'domain_override':domain_override,'download_limit':download_limit,'create' : create, 'zoho_account_id' : zoho_account_id, 'zoho_account_name' : zoho_account_name , 'domain_account_name' : domain_account_name }; 

        $("#save_company_btn").prop( "disabled", true );
        apiService.post('/save_company',postS)
        .then(function(response) {
            let data = response.data;
            $("#save_company_btn").prop( "disabled", false );
            if(data.status == 1){
                $('#add_company')[0].reset();
                $('#contactExistsDifferentAccount').modal('hide');
                $('#domainExistsDifferentAccount').modal('hide');
                $('#emailExistsDifferentAccount').modal('hide');
                $("#advancedModal5").modal('hide');
                $('#accountNotExistsZoho').modal('hide');
                $scope.showPopup('', 'Record added successfully and Email sent successfully.', 'Save Message', '');
                setTimeout(function(){
                    $scope.modalInstance.close();
                } , 1000 );
            }else if(data.status == 2){
                $("#error_msg_span").html(data.error_msg);
                $scope.openModal('./templates/modals/errorMsgModal.html');
                setTimeout(function(){
                    $scope.modalInstance.close();
                } , 1000 );
            }else if(data.status == 3){
                $("#user_zoho_msg").html(data.err_zoho_user_msg);
                $scope.openModal('./templates/modals/userExistsZohoMsg.html');
            }else if(data.status == 4) {
                $scope.openModal('./templates/modals/accountNotExistsZoho.html');
                $("#advancedModal5").modal('hide');
            }else if(data.status == 5) {
                $scope.openModal('./templates/modals/emailExistsDifferentAccount.html');
                $('.zoho_account_id').text(data.userInfo.data[0].Account_Name.id);
                $('.zoho_account_name').text(data.userInfo.data[0].Account_Name.name);
                $("#advancedModal5").modal('hide');
            }else if(data.status == 6) {
                $("#advancedModal5").modal('hide');
                $('#accountNotExistsZoho').modal('hide');
                $scope.openModal('./templates/modals/domainExistsDifferentAccount.html');
                $('#domain_found_account_add').text(data.userInfo.account_name);
                $('#page_set').text('ADS');
            }else if(data.status == 8) {
                $("#advancedModal5").modal('hide');
                $('#domainExistsDifferentAccount').modal('hide');
                $('#emailExistsDifferentAccount').modal('hide');
                $('#accountNotExistsZoho').modal('hide');
                $("#user_zoho_msg").html('Admin is already exists on existing account. Please fix the account in zoho.');
                $scope.openModal('./templates/modals/userExistsZohoMsg.html');
            }else if(data.status == 7) {
                $("#advancedModal5").modal('hide');
                $('#domainExistsDifferentAccount').modal('hide');
                $('#emailExistsDifferentAccount').modal('hide');
                $('#accountNotExistsZoho').modal('hide');
                $('#editMessage').modal('show');
                setTimeout(function(){
                    $('#editMessage').modal('hide');
                     window.location.href = '/drmetrix/adminConsole';
                } , 1000 );
            }else if(data.status == 9) {
                $("#advancedModal5").modal('hide');
                $('#accountNotExistsZoho').modal('hide');
                $scope.openModal('./templates/modals/domainExistsDifferentAccount.html');
                $('#domain_found_account_add').text(data.userInfo.data[0].Account_Name.name);
                $('#page_set').text('ZOHO ');
            }else if(data.status == 10) {
                $("#advancedModal5").modal('hide');
                $('#accountNotExistsZoho').modal('hide');
                $('#domainExistsDifferentAccount').modal('hide');
                $('#emailExistsDifferentAccount').modal('hide');
                $scope.openModal('./templates/modals/contactExistsDifferentAccount.html');
            } else if(data.status == 'domain_yes_admin_role'){
                $scope.backToAdmin('domain_found_zoho');
            }else{
                $('#authy_add_mobile').html(data.error);
                $('#authy_add_mobile').show();
            }
        }, function (response){
            // this function handlers error
        });
    }

    $scope.editUserFromCompany = function() {
        $rootScope.mobileValid = 0;
        $rootScope.usernameValid = 0;
        $rootScope.usernameValidInCompany = 0;
        var user_id             = $scope.admin.delete_user;
        var company_admin_id    = sessionStorage.company_admin_id = $scope.admin.company_admin_id;
        var company_id          = $scope.admin.company_id;
        $('#advancedModalDeleteUser').modal('hide');
        $('#authy_add_mobile').hide();
        $('#authy_edit_mobile').hide();
        $('#add_mobile').hide();
        $('#edit_mobile').hide();
        $("#options_div"+user_id).css("display", "none");
        $scope.openModal('./templates/modals/advancedModalEditUser.html');
        apiService.post('/get_user_edit',{'user_id':user_id} )
           .then(function(response){
            let data = response.data;
             if(data.status){
                $scope.admin.user_result = data.result;
                if($scope.admin.user_result[0].assistant_admin == '1'){
                    $('#assistant_admin_edit').prop('checked','checked');
                }
                $scope.admin.user_result[0].mobile                  = data.result[0].phone_number;
                $scope.admin.user_result[0].hidden_email            = data.result[0].email;
                $scope.admin.user_result[0].passphrase              = data.result[0].passphrase;
                $scope.admin.user_result[0].adsphere_authenticate   = data.result[0].adsphere_authenticate;
                $('#authy-countries').attr('id','authy_country_removed');
                $('select[name=edit_country_code]').attr('id','authy-countries');
               }
           }, function (response){
            // this function handlers error
        });


    }

    $scope.deleteUserFromCompanyModal = function (button_click) {
        if(button_click == 'delete'){
            $scope.openModal('./templates/modals/deleteUserFromCompany.html');
        }
    }

    $scope.deleteUserFromCompany = function (button_click) {
        var url = '/delete_user_from_company';
        var promptClicked = 'no';
        if (button_click == 'deactivate') {
            promptClicked = 'yes';
            $("#user_active_deactive").prop("disabled", true);
            url = '/deactivate_user_from_company'; // used from user.js
        }
        var user_id             = $scope.admin.delete_user;
        var company_admin_id    = $scope.admin.company_admin_id;
        var company_id          = $scope.admin.company_id;

        apiService.post(url, { 'user_id': user_id, 'company_admin_id': company_admin_id, 'company_id': company_id })
        .then(function (response) {
            let data = response.data;
            if (data.status) {
                if (button_click == 'deactivate') {
                    $("#user_active_deactive").prop("disabled", false);
                    $('#advancedModalDeleteUser').modal('hide');
                    if (data.max_limit == 'yes') {
                        $scope.openModal('./templates/modals/advancedModal3.html');
                    } else {
                        if (data.user_status == 'active') { $scope.showPopup('', 'Record activated successfully.', 'Save Message', '');}
                        if (data.user_status == 'inactive') { $scope.showPopup('', 'Record deactivated successfully.', 'Save Message', ''); }
                        setTimeout(function(){
                            $scope.modalInstance.close();
                        } , 1000 );
                    }
                } else {
                    $("#save_company_delete_user_btn").prop("disabled", false);
                    $('#delete_user_under_company')[0].reset();
                    $scope.modalInstanceMain.close();
                    $scope.showPopup('', 'Record deleted successfully.', 'Delete Message', '');
                    setTimeout(function () {
                        $scope.modalInstance.close();
                        $state.go($state.current, {}, {reload: true});
                    }, 3000);
                }

            }
        }, function (response){
            // this function handlers error
        });
    }

    $scope.backToAdmin = function (v) {
        $rootScope.mobileValid = 0;
        $rootScope.usernameValid = 0;
        $rootScope.usernameValidInCompany = 0;
        $scope.modalInstanceMain.close();

        if (v == 'add') { $scope.modalInstance.close(); $('#add_company')[0].reset(); }
        else if (v == 'edit_user') { $scope.modalInstance.close(); }
        else if (v == 'edit') { $scope.modalInstance.close(); }
        else if (v == 'add_user') { $scope.modalInstance.close(); $('#add_user_under_company')[0].reset(); }
        else if (v == 'delete_user') { $scope.modalInstance.close(); $('#delete_user_under_company')[0].reset(); }
        else if (v == 'change_admin') { $scope.modalInstance.close(); }
        else if( v == 'account_name_match') {$("#options_div" + $scope.admin.company_result[0].user_id ).css("visibility", "hidden"); $scope.modalInstance.close(); }
        else if( v == 'account_name_no_match') {   $("#options_div" + $scope.admin.company_result[0].user_id ).css("visibility", "hidden"); $scope.modalInstance.close(); }
        else if( v == 'email_found_zoho') {
            $("#user_zoho_msg").html( 'Please fix issue in Zoho with matching ADMIN record before continuing');
            $scope.modalInstance.close();
            $scope.openModal('./templates/modals/userExistsZohoMsg.html');
            $('#add_company')[0].reset();
        } else if(v == 'domain_found_ADS') {
            $("#user_zoho_msg").html( 'Please fix the issue in ADS with conflicting company before continuing');
            $('#domain_title').html('ADS Warning');
            $scope.modalInstance.close();
            $scope.openModal('./templates/modals/userExistsZohoMsg.html');
            $('#add_company')[0].reset();
        } else if(v == 'domain_found_zoho')  {
            $("#user_zoho_msg").html( 'Please fix the issue in Zoho with conflicting account before continuing');
            $scope.modalInstance.close();
            $scope.openModal('./templates/modals/userExistsZohoMsg.html');
        }else if( v == 'contact_found_zoho') {
            $('#emailExistsDifferentAccount').modal('hide');
            $scope.modalInstance.close();
            $("#advancedModal5").modal('hide'); $('#add_company')[0].reset();
        }
        else {  $scope.modalInstance.close(); }
    }

    $scope.getAuthyCountries = function() {
        apiService.post('/get_authy_countries',{} )
        .then(function(response){
            var data = response.data;
            if(data.status){
                $scope.admin.authy_countries = data.result;
            }
        }, function (response){
            // this function handlers error
        });
    }

    $scope.getRevenue = function () {
        apiService.post('/get_revenue', {})
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    $scope.admin.revenue = data.result;
                }
            }, function (response) {
                // this function handlers error
            });
    }

    $scope.getAccountOwner = function() {
        apiService.post('/get_company_owners', {})
        .then(function (response) {
            var data = response.data;
            if (data.status) {
                $scope.admin.account_owners = data.result;
            }
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.assignAdminId = function (admin_id, company_id) {
        $scope.admin_id = admin_id;
        $scope.company_id = company_id;
        $('#edit_data_user_id').val('');
        $scope.mobileValid = 0;
        $scope.usernameValidInCompany = 0;
        $scope.usernameValid = 0;
        $scope.admin.first_name = '';
        $scope.admin.last_name = '';
        $scope.admin.username = '';
        $scope.admin.mobile = '';
        $scope.openModal('./templates/modals/advancedModalAddUser.html');
    }

    $scope.addUserInCompany = function(  active ){
        if (typeof (active) == 'undefined') {
            active = false;
        }

        $scope.domain_msg = '';
        var mobile =  $('#mobile_add_user').val();
        var country_code =  $('#add_country_code_add_user').val();
        var timeout = 30;
        var no_of_apps = 1;
        var first_name = $scope.admin.first_name_add_user;
        var last_name = $scope.admin.last_name_add_user;
        var username = $scope.admin.username_add_user;
        var country_code = $scope.admin.add_country_code_add_user;
        var mobile = mobile;//$scope.admin.mobile;
        var position = $scope.admin.position_add_user;
        var role = 'user';
        var receive_report = $scope.admin.receive_report;
        var admin_id = sessionStorage.loggedInUserId;
        var assistant_admin = 0;
        if ($("#assistant_admin").is(":checked")) {
            assistant_admin = 1;
        }
        var tier = 1;//$scope.admin.users['tiers'][0].id;//$scope.admin.tier;

        var postS = {'first_name':first_name, 'last_name':last_name, 'username':username, 'mobile':mobile,'position':position, 'role':role,'receive_report':receive_report,'admin_id':admin_id,'tier':tier,'country_code':country_code,'timeout':timeout,'no_of_apps':no_of_apps,'assistant_admin':assistant_admin, 'active' :active };
        if(active != ''){
            postS.user_id  =  $('.user_ads_id').text() ;
            postS.zoho_user_id =  $('.user_zoho_id').text();
        }
        $("#save_company_add_user_btn").prop( "disabled", true );
        apiService.post('/save_user',postS)
        .then(function(response) {
            var data = response.data;
            $("#save_company_add_user_btn").prop( "disabled", false );
            // modalConfirmService.hideModal();
            let rowEntity = {};
            rowEntity.name = first_name + ' ' + last_name;
            rowEntity.username = username;
            rowEntity.country_code = country_code;
            rowEntity.phone_number = '(' + mobile.replace('-', ') ');
            rowEntity.position = position;
            rowEntity.role = role;
            rowEntity.vdate = '-';
            rowEntity.ads_authenticated = 1;
            rowEntity.authy_cookie = rowEntity.last_login = '';
            rowEntity.login_count = rowEntity.last_30_days_count = 0;
            rowEntity.tracking_alert_subscribed = 1;
            rowEntity.status = 'active';
            rowEntity.skip_authy = 0;
            rowEntity.assistant_admin = assistant_admin;

            if(data.status == 1){
                $('#add_user').reset();
                if(data.max_limit == 'yes'){
                    $scope.openModal('./templates/modals/advancedModal3.html');
                }else{
                    $scope.modalInstanceMain.close();
                    $scope.showPopup('', 'Record added successfully.', 'Save Message', '');
                    //$scope.email(username,data.comp_name);
                    rowEntity.user_id = data.user_id;
                    $scope.gridOptionsUser.data.push(rowEntity);
                    $scope.gridOptionsUser.data = $filter('orderBy')($scope.gridOptionsUser.data, "name", false);
                    $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.EDIT );
                    // $scope.gridApi.grid.refresh();
                    // $state.go($state.current, {}, {reload: true});
                    setTimeout(function(){
                        $scope.modalInstance.close();
                        // window.location.href = '/drmetrix/userAccount';
                    } , 1000);
                }
            }else if(data.status == 4){
                $scope.openModal('./templates/modals/domainOverrideMessage.html');
                $scope.domain_msg = data.domain_msg;
            }else if(data.status == 2){
                $scope.openModal('./templates/modals/errorMsg.html');
                $scope.error_msg = data.error_msg;
            }else if(data.status == 3){
                $scope.openModal('./templates/modals/userExistsZohoMsg.html');
                $scope.user_zoho_msg = data.user_zoho_msg;
            } else if(data.status == 5) {
                $scope.email_exists_id = data.ADS_Username;
                $scope.active_user_zoho_id = data.userInfo.id;
                $scope.active_user_ads_id = data.userInfo.ADS_Record_ID;
                $scope.openModal('./templates/modals/activateUserExists.html');
                $scope.type_save_active = data.type_save_active;
            } else if(data.status == 6) {
                $scope.email_exists_id = data.userInfo.ADS_Username;
                $scope.user_zoho_id = data.userInfo.id;
                $scope.user_ads_id = data.userInfo.ADS_Record_ID;
                $scope.openModal('./templates/modals/inactivateUserExists.html');
                $scope.type_save_deactive = data.type;
            }else if(data.status == 7) {
                $scope.modalInstanceMain.close();
                // $('#add_user')[0].reset();
                $scope.showPopup('', 'Record added successfully.', 'Add Message', '');
                rowEntity.user_id = data.user_id;
                $scope.gridOptionsUser.data.push(rowEntity);
                $scope.gridOptionsUser.data = $filter('orderBy')($scope.gridOptionsUser.data, "name", false);
                $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.EDIT );
                // $scope.gridApi.grid.refresh();
                // $state.go($state.current, {}, {reload: true});
                setTimeout(function(){
                    $scope.modalInstance.close();
                    // window.location.href = '/drmetrix/userAccount';
                } , 1000 );
            }else{
                $('#authy_add_mobile_add_user').html(data.error);
                $('#authy_add_mobile_add_user').show();
            }
        }, function (response){
            // this function handlers error
        });

        //$scope.email(username);
    }

    $scope.editCompany = function (user_id, company_id, update) {
        if (typeof (update) == 'undefined') {
            update = false;
        }

        var mobile = $('#mobile_edit').val();
        var country_code = $('#edit_country_code').val();
        var video_download_limit = $('#video_download_limit_edit').val();
        var a_limit = '';
        var access_type_p = $("#edit_access_type_p");
        var domain_override = 0;
        if ($("#domain_override_edit").is(":checked")) {
            domain_override = 1;
        }
        $scope.modalInstanceMain.close();
        var price = [];
        $scope.admin.company_result[0].user_id = user_id;
        $scope.admin.company_result[0].company_id = company_id;
        var zoho_account_id     = $scope.admin.company_result[0].zoho_account_id;
        var country_code        = $scope.admin.company_result[0].country_code;
        var company_name        = $scope.admin.company_result[0].company_name;
        var company_type        = $scope.admin.company_result[0].company_type;
        var company_size        = $scope.admin.company_result[0].company_size;
        var first_name          = $scope.admin.company_result[0].first_name;
        var last_name           = $scope.admin.company_result[0].last_name;
        var revenue             = $scope.admin.company_result[0].revenue;
        var account_owner       = $scope.admin.company_result[0].account_owner;
        var username            = $scope.admin.company_result[0].username;
        var client              = $scope.admin.company_result[0].client;
        var domain_override     = domain_override;

        var old_company_name    = $('#hidden_company_name').val();
        var old_email_address   = $('#hidden_username').val();
        var old_firstname          = $scope.admin.company_result[0].old_firstname;
        var old_lastname          = $scope.admin.company_result[0].old_lastname;
        var zoho_contact_id   =  $scope.admin.company_result[0].zoho_contact_id;
        var account_owner_zoho_id = $("#account_owner_edit option:selected").attr("cust-attr-id");
        var mobile = mobile;
        access_type = 'F';
        if (access_type_p.is(':checked') == true) {
            var access_type = 'P';
        }
        for (var i = 0; i < $scope.admin.pricing_result.length; i++) {
            var billing = $scope.admin.pricing_result[i].frequency;
            var tier = $scope.admin.pricing_result[i].tier;
            var users_limit = $scope.admin.pricing_result[i].users_limit;
            var amount = $scope.admin.pricing_result[i].amount;
            var id = $scope.admin.pricing_result[i].id;

            price.push({ 'billing': billing, 'tier': tier, 'users_limit': users_limit, 'amount': amount, 'price_id': id });
        }

        $scope.admin.ads_admin_id = '';
        var postEdit = { 'company_name': company_name, 'company_type': company_type, 'company_size': company_size, 'revenue': revenue, 'username': username, 'mobile': mobile,'download_limit': video_download_limit, 'user_id': user_id, 'price': price, 'company_id': company_id, 'country_code': country_code, 'first_name': first_name, 'last_name': last_name, 'access_type': access_type, 'monthly_cap': a_limit, 'client': client, 'account_owner': account_owner, 'account_owner_zoho_id': account_owner_zoho_id, 'domain_override': domain_override, 'company_zoho_id' : zoho_account_id , 'old_company_name' : old_company_name, 'old_email_address' : old_email_address, 'zoho_contact_id' : zoho_contact_id ,'update' : update, 'old_firstname' :old_firstname , 'old_lastname' : old_lastname };

        $("#edit_company_btn").prop("disabled", true);
        apiService.post('/edit_company', postEdit)
            .then(function (response) {
                let data = response.data;
                $("#edit_company_btn").prop("disabled", false);
                if (data.status) {
                    if(data.warning == 1) {
                        $scope.admin.matching_account_id = data.zoho_account_id;
                        if(data.admin_id != '') {
                            $scope.admin.ads_admin_id = data.admin_id;
                        }

                        $scope.openModal('./templates/modals/accountNameMatch.html');
                        $scope.modalInstance.close();
                    } else if(data.warning == 2) {
                        $scope.openModal('./templates/modals/accountNameNoMatch.html');
                        $scope.modalInstance.close();
                    } else if (data.warning == 3){
                        $scope.openModal('./templates/modals/errorMsgModal.html');
                        $scope.modalInstance.close();
                        $('#error_msg_span').text(data.error_msg);
                    }else if (data.warning == 4 ){
                        $('.email_exists_id').text(data.userInfo.username);
                        $(".user_zoho_id").html(data.userInfo.id);
                        $(".user_ads_id").html(data.userInfo.ADS_Record_ID);
                        $scope.openModal('./templates/modals/adminUserExists.html');
                        $scope.modalInstance.close();
                    }
                    else {
                        if (data.valid == 'no') {
                            $scope.showPopup('', 'You are not allowed to update Maximum user limit.', 'Max Limit Message', '');
                            setTimeout(function () { $scope.modalInstance.close(); $scope.showCompany(); }, 1000);
                        } else {
                            $scope.showPopup('', 'Record updated successfully.', 'Edit Message', '');
                            setTimeout(function () {
                                $scope.modalInstance.close();
                            }, 1000);
                        }
                    }

                } else if(data.status == 4) {
                    if(data.domain.account_name != '') {
                        $('#domain_found_account').text(' '+data.domain.account_name);
                    }else{
                        $('#domain_found_account').text(data.domain.account_name);
                    }

                    $scope.openModal('./templates/modals/domainFound.html');
                } else if(data.status == 5) {
                    $('#email_exists_id').text(data.username);
                    $(".user_zoho_id").html(data.userInfo.id);
                    $(".user_ads_id").html(data.userInfo.ADS_Record_ID);
                    $scope.openModal('./templates/modals/inactivateUserExists.html');
                    $scope.modalInstance.close();

                }else {
                    $('#authy_edit_mobile').html(data.error);
                    $('#authy_edit_mobile').show();
                }
            }, function (response){
                // this function handlers error
            });
    }

    $scope.deactivate = function (user_id, status) {
        apiService.post('/deactivate_user', { 'user_id': user_id, 'status': status })
            .then(function (response) {
                let data = response.data;
                if($('#advancedModalEditUser').hasClass('is-active')) {
                    $scope.modalInstance.close();
                }
                if (data.status) {
                    // $scope.modalInstance.close();
                    if (status == 'active') { $scope.showPopup('', 'Record deactivated successfully.', 'Deactivated Message', ''); }
                    if (status == 'inactive') { $scope.showPopup('', 'Record activated successfully.', 'Activated Message', ''); }

                    if (status == 'inactive') { setTimeout(function () {
                        $scope.modalInstance.close();
                    }, 1000); }
                    if (status == 'active') { setTimeout(function () {
                        $scope.modalInstance.close();
                    }, 1000); }
                    $scope.showAdmins();
                }
            }, function (response){
                // this function handlers error
            });
    }

    // Delete Users
    $scope.confirmUserDeletion = function (rowEntity) {
        $scope.userRowForAction = rowEntity;
        $scope.openModal('./templates/modals/deleteConfirm.html');
    }
    $scope.deleteUser = function () {
        let user_id = $scope.userRowForAction.user_id;
        let  company_id = $scope.userRowForAction. company_id;
        // modalConfirmService.hideModal();
        $scope.modalInstanceMain.close();
            $("#options_div" + user_id ).css("visibility", "hidden");
            apiService.post('/delete_company', { 'company_id': company_id,  'user_id': user_id })
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    $scope.showPopup('', 'Record deleted successfully.', 'Delete Message', '');
                    // $state.go($state.current, {}, {reload: true});
                    var index = $scope.gridOptionsAdmin.data.indexOf($scope.userRowForAction);
                    $scope.gridOptionsAdmin.data.splice(index, 1);
                    setTimeout(function () {
                        $scope.modalInstance.close();
                    }, 1000);
                }
            }, function (response){
                // this function handlers error
            });
    }

    $scope.getCompany = function (rowEntity) {
        $scope.userRowForAction = rowEntity;
        let user_id = $scope.userRowForAction.user_id;
        $scope.mobileValid = 0;
        $scope.usernameValid = 0;
        $scope.usernameValidInCompany = 0;
        $scope.usr = user_id;
        // $("#options_div" + user_id).css("visibility", "hidden");
        $scope.openModal('./templates/modals/advancedCompanyModalEdit.html');
        apiService.post('/get_company', { 'user_id': user_id })
            .then(function (response) {
                let data = response.data;
                if (data.status) {
                    $scope.admin.company_result = data.result;
                    $scope.admin.company_result[0].zoho_contact_id = data.result[0].zoho_contact_id;
                    $scope.admin.company_result[0].old_company_name = data.result[0].company_name;
                    $scope.admin.company_result[0].old_username     = data.result[0].email;
                    $scope.admin.company_result[0].old_firstname    = data.result[0].first_name;
                    $scope.admin.company_result[0].old_lastname     = data.result[0].last_name;
                    $scope.admin.company_result[0].mobile = data.result[0].phone_number;
                    $scope.admin.pricing_result = data.pricing;
                }
            }, function (response) {
                // this function handles error
            });
    }

    $scope.openAddCompany = function () {
        $rootScope.usernameValidInCompany = 0;
        $rootScope.usernameValid = 0;
        modalConfirmService.hideModal();
        $scope.openModal('./templates/modals/addAdmin.html');
        $scope.pricing = [{ id: 'pricing0' }];
        $scope.admin.company_name = '';
        $scope.admin.account_owner = '';
        $scope.admin.first_name = '';
        $scope.admin.last_name = '';
        $scope.admin.username = '';
        $scope.admin.mobile = '';
        $scope.admin.download_limit = 100;
        $scope.admin.domain_override = false;
        $('#err_owner').hide();
        // $scope.admin.add_company.$setPristine();
    }

    $scope.openModal = function(templateUrl, controller, size, backdrop) {
        $scope.modalInstanceMain =  modalConfirmService.showModal({
            backdrop: true,
            keyboard: true,
            modalFade: true,
            templateUrl: templateUrl,
            controller: 'AdminModalController',
            scope: $scope,
            size: size ? size : 'md modal-dialog-centered',
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

    $scope.sendPassword = function (passphrase, user_id) {
        if (passphrase == '') {
            return false;
        }
        $('#resend_link_' + user_id).addClass('resend_email');
        apiService.post('/regenerate_password', { 'user_id': user_id })
            .then(function (response) {
                let data = response.data;
                if (data.status) {
                    $scope.showPopup('', 'New password link sent successfully.', 'Regenerate Message', '');
                    setTimeout(function () { $('#regenerate').modal('hide'); }, 1000);
                    setTimeout(function () { $('#resend_link_' + user_id).removeClass('resend_email'); }, 120000);
                }
            }, function (response) {
                // this function handlers error
            });
            return true;
    }

    $scope.getAuthyCountries();
    $scope.getRevenue();
    $scope.getAccountOwner();
});

angular.module('drmApp').controller('AdminModalController', function($scope, $rootScope, $timeout, $uibModalInstance, $state, apiService, modalConfirmService) {

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