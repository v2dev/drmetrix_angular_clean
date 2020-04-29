angular.module('drmApp').controller('RefineModalController', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.refine_by = '';
    $scope.search_by_tfn = '';

    $scope.applyModal = function() {
        $rootScope.$broadcast("CallParentMethod", {'refine_by' : $scope.refineBy, 'search_by_tfn' : $scope.search_by_tfn });
        $uibModalInstance.dismiss();
    }

    $scope.resetModal = function() {
        $rootScope.$broadcast("CallParentMethod", {'refine_by' : ''});
        $uibModalInstance.dismiss();
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    $scope.applyRefineByFilter = function () {
        refine_apply_filter = 1;
        export_refine_apply_filter = 1;
        $scope.refine_by = 'All';
        $scope.search_by_tfn = '';
        var lbl_name = $scope.refine_by ? $scope.refine_by == '800' ? 'TFN' : $scope.refine_by == 'url' ? 'URL' : 'All' : 'All';
        // $("#lbl_outter_tfn_url_new").html(lbl_name);
        // $('#refine_by').html(lbl_name);
        // $('#refine_by_text').html($scope.search_by_tfn);
        $scope.filter($scope.type, $rootScope.active_flag);
        // $("#tnf-url").modal('hide');
        // $('#uiview').css('overflow', '');
    }

    $scope.resetRefineFilter = function () {
        $scope.ranking.refine_by = 'All';
        $scope.ranking.search_by_tfn = '';
        $scope.closeModal();
    }
});
