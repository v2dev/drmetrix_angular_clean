angular.module('drmApp').controller('AdminController', function ($scope, $timeout, $state, $stateParams, $filter, $interval, uiGridConstants, $rootScope, apiService, modalConfirmService, $uibModal) {
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }
    $scope.admin = {};
    $scope.admin.complete_name =  sessionStorage.complete_name = $rootScope.complete_name = localStorage.complete_name;
    $scope.save_clicked = false;
    $scope.userRowForAction = {};

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

                { name: 'name', displayName: 'Name', width: '100', cellTemplate: '<span title="{{row.entity.name}}" >{{row.entity.name}}</span>' },
                { name: 'username', displayName: 'Admin Username', width: '150', cellTemplate: '<span>{{row.entity.username ? row.entity.username : \'-\'}}</span>' },
                { name: 'phone_number', displayName: 'Admin Mobile#', width: '90', cellTemplate: '<span title="+{{row.entity.country_code}}{{row.entity.phone_number}}" >+{{row.entity.country_code}}{{row.entity.phone_number}}</span>' },

                { name: 'adsphere_authenticate', displayName: 'Adsphere Authenticate', width: '170', cellTemplate: '<span class="{{row.entity.adsphere_authenticate == 0 ? \'user-not-verified\' : \'\'}}"  >{{row.entity.adsphere_authenticate  == 0 ? \'No\' : \'\'}}</span><span class="{{row.entity.adsphere_authenticate  == 0 ? \'user-not-verified-link\' : \'user-verified\'}}" id ="resend_link_{{row.entity.user_id}}" ng-click="grid.appScope.sendPassword(row.entity.passphrase, row.entity.user_id)" >{{row.entity.adsphere_authenticate == 0 ? \'Resend Email\' : \'Yes\'}}</span>' },

                { name: 'authy_cookie', displayName: 'Authy Authenticated', width: '170', cellTemplate: '<span class="{{row.entity.authy_cookie ? \'user-verified\' : \'user-not-verified\'}}" >{{row.entity.authy_cookie ? \'Yes\' : \'No\'}}</span>' },

                { name: 'vdate', displayName: 'Verified Date', width: '80', cellTemplate: '<span>{{row.entity.vdate ? row.entity.vdate : \'-\'}}</span>' },
                { name: 'eula_flag', displayName: 'Eula Overried', width: '60', cellTemplate: '<span><input type="checkbox" class="checkbox-custom" id="{{row.entity.company_id}}" ng-click="eulaOverride(row.entity.company_id)" ng-checked="eula_check(row.entity.eula_flag)" /><label class="checkbox-custom-label"></label></span>' },
                { name: 'network_tab', displayName: 'Network Tab', width: '60', cellTemplate: '<span><input type="checkbox" class="checkbox-custom" id="network_tab_check_{{row.entity.company_id}}" ng-click="manageNetworkTab(row.entity.company_id)" ng-checked="eula_check(row.entity.network_tab)" /><label class="checkbox-custom-label"></label></span>' },
                { name: 'staging_access', displayName: 'Staging Access', width: '80', cellTemplate: '<span><input type="checkbox" class="checkbox-custom" id="staging_access_check_{{row.entity.company_id}}" ng-click="manageStagingAccess(row.entity.company_id)" ng-checked="eula_check(row.entity.staging_access)" /><label class="checkbox-custom-label"></span>' },
                { name: 'skip_authy', displayName: 'Skip Authy', width: '90', cellTemplate: '<span><input type="checkbox" class="checkbox-custom-label" id="skip_authy_check_{{row.entity.company_id}}" ng-click="manageSkipAuthy(row.entity.company_id)" ng-checked="eula_check(row.entity.skip_authy)" /><label class="checkbox-custom-label"></label></span>' },
                { name: 'status', displayName: 'Status', width: '80', cellTemplate: '<span>{{row.entity.status == "active" ? "Active" : "Inactive"}}</span>' },

                { name: 'user_id', displayName: 'Action', width: '80', cellClass: "overflow-visible setting-icon", cellTemplate: '<div class="dropdown"><i class="fa fa-cog fa-2x" data-toggle="dropdown" id="dropdownMenuButton" aria-haspopup="true" aria-expanded="false"></i><ul class="dropdown-menu" aria-labelledby="dropdownMenuButton"><li><a ng-click="grid.appScope.getCompany(row.entity)" >Edit Company</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.confirmUserDeletion(row.entity)">Delete Company</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.deactivate(row.entity.user_id, row.entity.status)" data-toggle="modal">{{row.entity.status == "active" ? "Deactivate" : "Activate"}} Company</a></li><li><a href="#" ng-click="grid.appScope.assignAdminId(row.entity.user_id, row.entity.company_id)">Add User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.getUsers(row.entity)">Edit/Delete/Deactivate/Activate User</a></li><li><a href="javascript:void(0);" ng-click="grid.appScope.getUsers(row.entity)">Change Admin Of Company</a></li></ul></div>' }
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
            .then(function (data) {
                $("#edit_company_btn").prop("disabled", false);
                if (data.status) {
                    if(data.warning == 1) {
                        $scope.admin.matching_account_id = data.zoho_account_id;
                        if(data.admin_id != '') {
                            $scope.admin.ads_admin_id = data.admin_id;
                        }

                        $scope.modalInstance.close();
                        $('#accountNameMatch').modal('show');
                    } else if(data.warning == 2) {
                        $scope.modalInstance.close();
                        $('#accountNameNoMatch').modal('show');
                    } else if (data.warning == 3){
                        $scope.modalInstance.close();
                        $('#errorMsg').modal('show');
                        $('#error_msg_span').text(data.error_msg);
                    }else if (data.warning == 4 ){
                        $('.email_exists_id').text(data.userInfo.username);
                        $(".user_zoho_id").html(data.userInfo.id);
                        $(".user_ads_id").html(data.userInfo.ADS_Record_ID);
                        $('#adminUserExists').modal('show');
                        $scope.modalInstance.close();
                    }
                    else {
                        $('#adminUserExists').modal('hide');
                        if (data.valid == 'no') {
                            $scope.modalInstance.close();
                            $('#maxLimitMessage').modal('show');
                            setTimeout(function () { $('#maxLimitMessage').modal('hide'); $scope.showCompany(); }, 1000);
                        } else {
                            $scope.modalInstance.close();
                            $('#editMessage').modal('show');
                            setTimeout(function () {
                                $('#editMessage').modal('hide');
                                window.location.href = '/drmetrix_angular_clean/#!/adminConsole';
                            }, 1000);
                        }
                    }

                } else if(data.status == 4) {
                    if(data.domain.account_name != '') {
                        $('#domain_found_account').text(' '+data.domain.account_name);
                    }else{
                        $('#domain_found_account').text(data.domain.account_name);
                    }

                    $('#domainFound').modal('show');
                } else if(data.status == 5) {
                    $('#email_exists_id').text(data.username);
                    $(".user_zoho_id").html(data.userInfo.id);
                    $(".user_ads_id").html(data.userInfo.ADS_Record_ID);
                    $('#inactivateUserExists').modal('show');
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
                    $('#advancedModalAddUser').modal('hide');
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
        sessionStorage.company_id = '';
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
        $scope.admin.add_company.$setPristine();
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