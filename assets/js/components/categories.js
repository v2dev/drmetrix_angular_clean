angular.
  module('drmApp').
  component('categoryPart', { 
    bindings: {
        category_list: '<'
    },
    template:
        `<nav class="sidebar">
        <a  href="" class="open-left-sidebar-nav slideBtn" id="slidebtn" ng-click="catgeorySideBar = !catgeorySideBar;"> 
            <i data-feather="align-justify"></i>
            <span>Categories</span>
        </a>
        <div class="sidebar-sticky sidenav scrollbar" id="sidenav"  ng-class="catgeorySideBar == 1 ? 'addWidthAndLeft' : 'minusWidthAndLeft'">
            <div class="d-flex justify-content-between align-items-center position-relative pb-1">
                <div class="checkbox-normal">
                    <input class="checkbox-custom cat_all"  type="checkbox" id="checkbox5_all"  ng-click="$ctrl.allcategory = !$ctrl.allcategory;$ctrl.selectCategory($ctrl.category_list, $ctrl.allcategory, 'all')" ng-model="$ctrl.allcategory" />
                    <label for="checkbox5_all" class="checkbox-custom-label">
                        All Categories</label>
                </div>
                <a class="btn btn-black category-track-btn"  id="category_track_btn" ng-class="category_data_flag ? 'btn-black': ''"><i class="fa fa-eye" title="Track"></i>
                    <span>Track</span>
                </a>
                <a href="" class="close-left-sidebar-nav slideBtn" id="slidebtn" ng-click="catgeorySideBar = !catgeorySideBar;">
                    <i data-feather="align-justify"></i>
                </a>
            </div>
            <ul ng-repeat="item in $ctrl.category_list" class="menu-bar vertical no-bullet" id="categories_list">
                <li>
                    <div class="checkbox-normal" data-toggle="collapse" href="#collapseExample" role="button"
                        aria-expanded="false" aria-controls="collapseExample">
                        <input class="checkbox-custom" custom-attr="subcat_cust_attr" type="checkbox" id="checkbox_{{item.category_id}}" ng-click="item.isSelected = !item.isSelected;$ctrl.selectCategory(item, item.isSelected)" ng-model="item.isSelected">
                        <label for="checkbox_{{item.category_id}}" class="checkbox-custom-label" ng-class="$ctrl.selectedValue(item)">
                            <a href="" ng-click="$ctrl.subCategory = !$ctrl.subCategory" class="toggle-arrow">
                                <i class="fa fa-lg" ng-class="$ctrl.subCategory ? 'fa-caret-down' : 'fa-caret-right'"></i>
                            </a>
                            {{item.category}}
                        </label>
                    </div>
                    <ul ng-show="$ctrl.subCategory">
                        <li id="all_categories" ng-repeat="subitem in item.subcategory" class="checkbox-normal">
                            <input id="label_{{subitem.sub_category_id}}" custom-attr="subcat_cust_attr" class="checkbox-custom" type="checkbox" ng-click="$ctrl.selectCategory(category_list, subitem.isSelected, 'subCategory')" ng-model="subitem.isSelected">
                            <label for="label_{{subitem.sub_category_id}}" custom-id="label_{{subitem.sub_category_id}}" class="checkbox-custom-label">
                                {{subitem.sub_category}}
                            </label>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>`,
    controller: function CategoryController($scope, $rootScope, apiService) {
        var self = this;
       
        self.showCategoryList = function () {
            if (localStorage.cachedCategoriesData) {
                self.setCategoriesHTML();
            } else {
                self.categories_called = 1;
                apiService.post('/get_cats_list', {})
                    .then(function (data) {
                        localStorage.cachedCategoriesData = JSON.stringify(data);
                        self.setCategoriesHTML();
                    })
                    ,(function (data, status, headers, config) {
                        console.log('Errooor');
                    });
            }
            return 1;
        }
    
        self.setCategoriesHTML = function(){
            var cat = JSON.parse(localStorage.cachedCategoriesData).data.result;
            for (var i in cat) {
                cat[i].isSelected = true;
                for (var j in cat[i].subcategory) {
                    cat[i].subcategory[j].isSelected = true;
                }
            }
            self.allcategory = true;
            self.category_list = $rootScope.category_list = cat;
        }
    
          
        self.selectedValue = function (item) {
            var count = 0;
            angular.forEach(item.subcategory, function (data) {
                if (!data.isSelected)
                    count++;
            });
            return count > 0 ? count == item.subcategory.length ? 'empty' : 'half-filled' : 'full-filled';
        }
        
        self.selectCategory = function (item, value, type) {
            if (type == 'all') {
                angular.forEach(item, function (data) {
                    data.isSelected = value;
                    angular.forEach(data.subcategory, function (cat) {
                        cat.isSelected = value;
                    });
                })
            } else if (type == 'subCategory') {
                self.changeCategory(item, value);
            } else {
                angular.forEach(item.subcategory, function (data) {
                    data.isSelected = value;
                });
                self.changeCategory($scope.category_list, value);
            }
            self.checkCategoryValidation(type, value)
            
        }
    
        self.checkCategoryValidation = function(type, value) {
            // $scope.ranking.main_categories = $scope.getMainCategories();
            $rootScope.category_error = false;
            $rootScope.no_category_error = false;
            if(type == 'all' && !value) {
                $rootScope.no_category_error = true;
                // $scope.disableApplyButton();
            } else {
                // if($scope.ranking.main_categories.length > sessionStorage.lifetime_cat_restrict_count ) {
                //     $scope.disableApplyButton();
                //     $scope.lifetime_error = 1;
                // } else {
                //     $scope.enableApplyButton();
                //     $scope.lifetime_error = 0; 
                $rootScope.category_error = true;
                // }
                   
            }
        }
    
        self.changeCategory = function (item, value) {
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
        self.showCategoryList(); 
        // if ((typeof($rootScope.category_list) == 'undefined' || $rootScope.category_list.length == 0)) { }
        

        feather.replace();
    }
  });