
angular.module("drmApp").controller("RankingController", function($scope, $state, $rootScope, apiService,  $uibModal){
    if (!apiService.isUserLogged($scope)) {
        $state.go('home');
        return;
    }

    $scope.initialisation = function() {
        $rootScope.complete_name = localStorage.complete_name;
        $rootScope.cateorySideBar = 0; // hide category section on load
    }
    $scope.initialisation() ;

    $scope.showCategoryList = function () {
        if (localStorage.cachedCategoriesData != null) {
            $scope.setCategoriesHTML();
        } else {
			$scope.categories_called = 1;
            apiService.post('/get_cats_list', {})
                .then(function (data) {
                    localStorage.cachedCategoriesData = JSON.stringify(data);
                    $scope.setCategoriesHTML();
                })
                ,(function (data, status, headers, config) {
                    console.log('Errooor');
				});
        }
        return 1;
    }

    $scope.setCategoriesHTML = function(){
        var cat = JSON.parse(localStorage.cachedCategoriesData).data.result;
        
        // var cat = data.result;
        for (var i in cat) {
            cat[i].isSelected = true;
            for (var j in cat[i].subcategory) {
                cat[i].subcategory[j].isSelected = true;
            }
        }
        $scope.allcategory = true;
        $rootScope.category_list = cat;
    }

      
    $scope.selectedValue = function (item) {
        var count = 0;
        angular.forEach(item.subcategory, function (data) {
            if (!data.isSelected)
                count++;
        });
        return count > 0 ? count == item.subcategory.length ? 'empty' : 'half-filled' : 'full-filled';
    }
    
    $scope.selectCategory = function (item, value, type) {
        if (type == 'all') {
            angular.forEach(item, function (data) {
                data.isSelected = value;
                angular.forEach(data.subcategory, function (cat) {
                    cat.isSelected = value;
                });
            })
        } else if (type == 'subCategory') {
            $scope.changeCategory(item, value);
        } else {
            angular.forEach(item.subcategory, function (data) {
                data.isSelected = value;
            });
            $scope.changeCategory($scope.category_list, value);
        }
        $scope.checkCategoryValidation(type, value)
        
    }

    $scope.checkCategoryValidation = function(type, value) {
        // $scope.ranking.main_categories = $scope.getMainCategories();
        $scope.category_error = false;
        $scope.no_category_error = false;
        if(type == 'all' && !value) {
            $scope.no_category_error = true;
            // $scope.disableApplyButton();
        } else {
            // if($scope.ranking.main_categories.length > sessionStorage.lifetime_cat_restrict_count ) {
            //     $scope.disableApplyButton();
            //     $scope.lifetime_error = 1;
            // } else {
            //     $scope.enableApplyButton();
            //     $scope.lifetime_error = 0; 
                $scope.category_error = true;
            // }
               
        }
    }

    $scope.changeCategory = function (item, value) {
        if (!value) {
            $scope.allcategory = false;
        } else {
            var count = 0;
            angular.forEach(item, function (data) {
                angular.forEach(data.subcategory, function (cat) {
                    if (!cat.isSelected)
                        count++;
                });
            });
            $scope.allcategory = count == 0 ? true : false;
        }
    }

    $scope.showTab = function(tab) {
        $scope.type = tab;
        if ((typeof($rootScope.category_list) == 'undefined' || $rootScope.category_list.length == 0)) { $scope.showCategoryList(); }
    }

    $scope.call_filter_list = function(menu) {
        var modalInstance = $uibModal.open({
            templateUrl: './templates/ranking-modals.html',
            controller: "FilterCtrl",
            backdrop:'static',
            size :'lg',
            keyboard:false,
        });
    }
    
    feather.replace();

});

angular.module('drmApp').controller('FilterCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService) {
    console.log('filter called');
  });
  