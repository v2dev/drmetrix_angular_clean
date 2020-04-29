"use strict";
angular.module('drmApp').controller('ConfigureEmailsController', function ($scope, $timeout, $state, $stateParams, $filter, $interval, uiGridConstants, $rootScope, apiService, modalConfirmService, $uibModal, $cookies) {
    $rootScope.correctTotalPaginationTemplate =
        "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"prev-page\"></div></button> Page <input ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\"> of </abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"next-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-page\"></div></button></div></div><div class=\"ui-grid-pager-count-container\"></div></div>";
    $scope.uigridConfigEmails = function () {
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }

        formData._search = 'false';
        formData.rows = '20';
        formData.page = '1';
        formData.sidx = 'triggered_on';
        formData.sord = 'desc';
        formData.from = 'grid';

        vm.gridConfigEmails = {
            expandableRowTemplate: '/drmetrix_angular_clean/templates/expandableAiringRowtmpt.html',
            expandableRowHeight: 285,
            showTreeExpandNoChildren: true,
            enableGridMenu: true,
            enableSorting: true,
            enableExpandableRowHeader: false,
            //Pagination
            paginationPageSizes: [20],
            paginationPageSize: 20,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
        };

        vm.gridConfigEmails.columnDefs = [
            // { name: 'id', displayName:'id', width:'50' },

            { name: 'alert_type', pinnedLeft: true, displayName: 'Alert Type' },

            { name: 'source', displayName: 'source' },

            { name: 'triggered_on', pinnedLeft: true, displayName: 'Created On' },

            { name: 'classification', pinnedLeft: true, displayName: 'Classification', cellTemplate: '<span ng-if=row.entity.classification != \'\' custom-attr="config_alert_classification_row.entity.type_id">row.entity.classification-data</span><span ng-if=row.entity.classification == \'\' custom-attr="config_alert_classification_row.entity.type_id">row.entity.classification-data</span>' },

            { name: 'frequency', pinnedLeft: true, displayName: 'Frequency', cellTemplate: '<span custom-attr="config_alert_frequency_row.entity.type_id">{{COL_FIELD}}</span>' },

            // { name: 'status', pinnedLeft:true, displayName:'Tracking Status', cellTemplate: '<span ng-if=(row.entity.status == \'active\')><i class="fa fa-eye blue-eye" custom-attr="config_alert_row.entity.alert_type_row.entity.type_id" title="Tracking Active"></i></span><span ng-if=(row.entity.status!=\'active\')<i class="fa grey-eye fa-eye-slash" custom-attr="config_alert_row.entity.alert_type_row.entity.type_id" title="Tracking Inactive"></i></span>' },

            { name: 'operation', pinnedLeft: true, displayName: 'Operation', cellTemplate: '<a href="" ng-click="grid.appScope.viewTrackingDialogue(row.entity.alert_type,row.entity.type_id,row.entity.source, row.entity.email_schedulable_direct, row.entity.showMonthlyOption);"><i class="fa fa-pencil edit-icon" title="Edit"></i></a><a href="" ng-click="grid.appScope.setDeleteTrackingBtn(row.entity.alert_type_filter, row.entity.alert_type);"><i class="fa fa-trash-o delete-icon" title="Delete"></i></a>' },
        ];
        apiService.post('/get_alerts_list', formData, config)
            .then(function (data) {
                $scope.PostDataResponse = formData;
                vm.gridConfigEmails.data = data.data.rows;
            }, function (response) {
                // this function handlers error
            });
    }

    $scope.uigridConfigEmails();

    $scope.viewTrackingDialogue = function (alert_type, type_id, name, email_schedulable_direct, show_monthly) {
        $scope.alert_type = alert_type;
        $scope.type_id = type_id;
        $scope.name = name;
        $scope.email_schedulable_direct = email_schedulable_direct;
        $("#success_alert_setup_msg").hide();
        $("#error_alert_setup_msg").hide();
        $("#track_advertiser_input").prop('checked', false);
        $("#track_brand_input").prop('checked', false);
        $("#track_creative_input").prop('checked', false);
        $("input[name=alert-frequency][value='daily']").prop('checked', false);
        $("input[name=alert-frequency][value='weekly']").prop('checked', false);
        $scope.openModal('./templates/modals/configEmailModalEdit.html', 'ConfigureEmailsController', 'xl modal-dialog-centered');
        if (alert_type == 'filter') {
            $('#following_occurs,#brand_classification_div').hide();

            setAlertClose(1, type_id, email_schedulable_direct);

            if (show_monthly /*filter_criteria.toLowerCase().indexOf('current quarter') != -1*/) {
                scope.show_monthly = 1;
            } else {
                scope.show_monthly = 0;
            }
        } else {
            $('#following_occurs,#brand_classification_div').show();
        }
        $scope.showTrackingDialogue(alert_type, type_id, name);
        // Get Users
        apiService.post('/get_alerts_list', { 'user_id': user_id })
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    $scope.admin_user.user_result = data.result;
                    $scope.admin_user.user_result[0].mobile = data.result[0].phone_number;
                    $scope.admin_user.user_result[0].hidden_email = data.result[0].email;
                    if ($scope.admin_user.user_result[0].assistant_admin == '1') {
                        $('#assistant_admin_edit').prop('checked', 'checked');
                    }
                    $('#authy-countries').attr('id', 'authy_country_removed');
                    $('select[name=edit_country_code]').attr('id', 'authy-countries');
                }
            }, function (response) {
                // this function handles error
            });
    }

    $scope.setDeleteTrackingBtn = function (tracking_id, alert_type) {
        $scope.tracking_id = tracking_id;
        $scope.alert_type = alert_type;
        $scope.openModal('./templates/modals/confirmTrackDelete.html');
    }

    $scope.deleteTrackUser = function () {
        let tracking_id = $scope.tracking_id;
        let alert_type = $scope.alert_type;
        $scope.modalInstanceMain.close();
        // if(confirm("Are you sure want to delete the record?")) {
        $("#options_div" + tracking_id).css("visibility", "hidden");
        apiService.post('/delete_tracking_alerts', { 'tracking_id': tracking_id, 'alert_type': alert_type })
            .then(function (response) {
                var data = response.data;
                if (data.status) {
                    $scope.showPopup('', 'Record deleted successfully.', 'Delete Message', '');
                    // $state.go($state.current, {}, {reload: true});
                    var index = $scope.gridConfigEmails.data.indexOf($scope.userRowForAction);
                    $scope.gridConfigEmails.data.splice(index, 1);
                    setTimeout(function () {
                        $scope.modalInstance.close();
                    }, 1000);
                }
            }, function (response) {
                // this function handlers error
            });
        // }
    }

    $scope.applyModal = function () {
        $scope.modalInstance.dismiss();
    }

    $scope.closeModal = function () {
        $scope.modalInstance.dismiss();
    }

    $scope.openModal = function (templateUrl, controller, size, backdrop) {
        $scope.modalInstanceMain = modalConfirmService.showModal({
            backdrop: false,
            keyboard: true,
            modalFade: true,
            templateUrl: templateUrl,
            controller: controller,
            scope: $scope,
            size: size ? size : 'xl modal-dialog-centered',
        });

        $scope.modalInstanceMain.result.then(function (response) {
            $scope.result = `${response} button hitted`;
        });

        $scope.modalInstanceMain.result.catch(function error(error) {
            if (error === "backdrop click") {
                // do nothing
            } else {
                // throw error;
            }
        });
    };

    $scope.displayDeleteBox = function (delete_all) {
        if (typeof (delete_all) == 'undefined') {
            delete_all = false;
        }
        for (var i = 0; i < idsOfSelectedTrackingRows.length; i++) {
            $('#cb_manage_tracking').prop('checked', false);
            $('#jqg_manage_tracking_' + idsOfSelectedTrackingRows[i]).prop('checked', false);
        }
        if (delete_all == 'all') {
            $('.delete_popup_header').html('Delete All Alerts');
            $('#tracker_alert_text').html('Are you sure you want to delete <b>everything</b> from tracking alerts?');
        } else if (delete_all == 'selected') {
            $('.delete_popup_header').html('Delete Alert');
            $('#tracker_alert_text').html('Are you sure you want to delete selected tracking alert?');
        } else {
            $('.delete_popup_header').html('Delete Alert');
            $('#tracker_alert_text').html('Are you sure you want to delete this tracking alert?');
        }
        // FoundationApi.publish('openDeleteTrackModal', 'show');
        $('#openDeleteTrackModal').modal('show');

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
});