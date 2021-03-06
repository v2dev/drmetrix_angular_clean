//https://www.js-tutorials.com/angularjs-tutorial/angular-bootstrap-modal-example/
"use strict";
angular.module('drmApp').service('modalConfirmService', ['$uibModal',
    function ($modal) {
        var modalDefaults = {
            backdrop: true,
            keyboard: true,
            modalFade: true,
            templateUrl: './assets/js/shared/confirmBox/confirmBox.html'
        };

        var modalOptions = {
            closeButtonText: '',
            actionButtonText: '',
            headerText: '',
            bodyText:  '',
            closeReq: 0
        };
        this.showModal = function (customModalDefaults, customModalOptions) {
            if (!customModalDefaults) customModalDefaults = {};
            customModalDefaults.backdrop = 'static';
            //Create temp objects to work with since we're in a singleton service
            var tempModalDefaults = {};
            var tempModalOptions = {};

            //Map angular-ui modal custom defaults to modal defaults defined in service
            angular.extend(tempModalDefaults, modalDefaults, customModalDefaults);

            //Map modal.html $scope custom properties to defaults defined in service
            angular.extend(tempModalOptions, modalOptions, customModalOptions);
            this.modalOptions = tempModalOptions;

            if (!tempModalDefaults.controller) {
                tempModalDefaults.controller = function ($scope, $uibModalInstance) {
                    $scope.modalOptions = tempModalOptions;
                    $scope.modalOptions.ok = function (result) {
                        $uibModalInstance.close(result);
                    };
                    $scope.modalOptions.close = function (result) {
                        // $uibModalInstance.dismiss('cancel');
                        $uibModalInstance.close(result);
                    };
                }
            }
            return $modal.open(tempModalDefaults);
        };

        this.hideModal = function() {
            var tempModalDefaults = {};
            var tempModalOptions = {};
            if (!tempModalDefaults.controller) {
                tempModalDefaults.controller = function ($scope, $uibModalInstance) {
                    $scope.modalOptions = tempModalOptions;
                    // $uibModalInstance.dismiss('cancel');
                    $uibModalInstance.close(result);
                }
            }
        }
    }
]);
