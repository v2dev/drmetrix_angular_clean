angular.module("drmApp").controller("AiringsDetailController", function($scope, $http, $interval,uiGridTreeViewConstants, $state, $rootScope, apiService,  $uibModal, $stateParams){
    $scope.dow_display_text = $scope.hod_display_text = $scope.daypart_display_text = 0;
    $scope.all_day = []; $scope.all_hour = []; $scope.all_dayparts = []; $scope.all_programs = []; $scope.all_networks = [];
    $rootScope.all_program_selected = true;
    $rootScope.headerDisplay = 0;
    $rootScope.if_creative = 0;
    $scope.custom_variable = 0;
    $rootScope.selectedWeekType = 'all'; 
    $rootScope.allHour = true; 
    $rootScope.timeData = [
        {
            'type': 'AM',
            'data': [
                { 'text': '12A', 'value': 0 , 'isSelected'  : true},
                { 'text': '1A', 'value': 1, 'isSelected'  : true },
                { 'text': '2A', 'value': 2, 'isSelected'  : true },
                { 'text': '3A', 'value': 3, 'isSelected'  : true},
                { 'text': '4A', 'value': 4 , 'isSelected'  : true},
                { 'text': '5A', 'value': 5 , 'isSelected'  : true},
                { 'text': '6A', 'value': 6 , 'isSelected'  : true},
                { 'text': '7A', 'value': 7, 'isSelected'  : true },
                { 'text': '8A', 'value': 8 , 'isSelected'  : true},
                { 'text': '9A', 'value': 9 , 'isSelected'  : true},
                { 'text': '10A', 'value': 10 , 'isSelected'  : true},
                { 'text': '11A', 'value': 11 , 'isSelected'  : true}
            ]
        },
        {
            'type': 'PM',
            'data': [
                { 'text': '12P', 'value': 12, 'isSelected'  : true },
                { 'text': '1P', 'value': 13, 'isSelected'  : true },
                { 'text': '2P', 'value': 14 , 'isSelected'  : true},
                { 'text': '3P', 'value': 15, 'isSelected'  : true },
                { 'text': '4P', 'value': 16 , 'isSelected'  : true},
                { 'text': '5P', 'value': 17, 'isSelected'  : true },
                { 'text': '6P', 'value': 18, 'isSelected'  : true },
                { 'text': '7P', 'value': 19, 'isSelected'  : true },
                { 'text': '8P', 'value': 20, 'isSelected'  : true },
                { 'text': '9P', 'value': 21, 'isSelected'  : true },
                { 'text': '10P', 'value': 22 , 'isSelected'  : true},
                { 'text': '11P', 'value': 23, 'isSelected' : true }
            ]
        }
    ];
    $scope.weekType = {
        "all": [1, 2, 3, 4, 5, 6, 7],
        "weekday": [1, 2, 3, 4, 5],
        "weekend": [6, 7],
        "custom": []
    };
    $scope.weeks = [
        { 'id': 1, 'sortName': 'Mon', 'fullName': '', isSelected: true },
        { 'id': 2, 'sortName': 'Tues', 'fullName': '', isSelected: true },
        { 'id': 3, 'sortName': 'Wed', 'fullName': '', isSelected: true },
        { 'id': 4, 'sortName': 'Thurs', 'fullName': '', isSelected: true },
        { 'id': 5, 'sortName': 'Fri', 'fullName': '', isSelected: true },
        { 'id': 6, 'sortName': 'Sat', 'fullName': '', isSelected: true },
        { 'id': 7, 'sortName': 'Sun', 'fullName': '', isSelected: true },
    ];
    $rootScope.allDayparts = true;
    $rootScope.dayparts = [
        { 'id': 1, 'sortName': 'early_morning', 'fullName': 'Early Morning (6AM - 10AM)', isSelected: true },
        { 'id': 2, 'sortName': 'daytime', 'fullName': 'Daytime (10AM - 4.30PM)', isSelected: true },
        { 'id': 3, 'sortName': 'early_fringe', 'fullName': 'Early Fringe (4.30PM - 8PM)', isSelected: true },
        { 'id': 4, 'sortName': 'prime', 'fullName': 'Prime (8PM - 11PM)', isSelected: true },
        { 'id': 5, 'sortName': 'late_fringe', 'fullName': 'Late Fringe (11PM - 1AM)', isSelected: true },
        { 'id': 6, 'sortName': 'overnight', 'fullName': 'Overnight (1AM - 6AM)', isSelected: true },
    ];

    $rootScope.correctTotalPaginationTemplate =
    "<div role=\"contentinfo\" class=\"ui-grid-pager-panel\" ui-grid-pager ng-show=\"grid.options.enablePaginationControls\"><div role=\"navigation\" class=\"ui-grid-pager-container\"><div role=\"menubar\" class=\"ui-grid-pager-control\"><button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-first\" ui-grid-one-bind-title=\"aria.pageToFirst\" ui-grid-one-bind-aria-label=\"aria.pageToFirst\" ng-click=\"pageFirstPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"first-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-previous\" ui-grid-one-bind-title=\"aria.pageBack\" ui-grid-one-bind-aria-label=\"aria.pageBack\" ng-click=\"pagePreviousPageClick()\" ng-disabled=\"cantPageBackward()\"><div class=\"prev-page\"></div></button> Page <input ui-grid-one-bind-title=\"aria.pageSelected\" ui-grid-one-bind-aria-label=\"aria.pageSelected\" class=\"ui-grid-pager-control-input\" ng-model=\"grid.options.paginationCurrentPage\" min=\"1\" max=\"{{ paginationApi.getTotalPages() }}\" required> <span class=\"ui-grid-pager-max-pages-number\" ng-show=\"paginationApi.getTotalPages() > 0\"><abbr ui-grid-one-bind-title=\"paginationOf\"> of </abbr> {{ paginationApi.getTotalPages() }}</span> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-next\" ui-grid-one-bind-title=\"aria.pageForward\" ui-grid-one-bind-aria-label=\"aria.pageForward\" ng-click=\"pageNextPageClick()\" ng-disabled=\"cantPageForward()\"><div class=\"next-page\"></div></button> <button type=\"button\" role=\"menuitem\" class=\"ui-grid-pager-last\" ui-grid-one-bind-title=\"aria.pageToLast\" ui-grid-one-bind-aria-label=\"aria.pageToLast\" ng-click=\"pageLastPageClick()\" ng-disabled=\"cantPageToLast()\"><div class=\"last-page\"></div></button></div></div><div class=\"ui-grid-pager-count-container\"></div></div>";


    
$scope.uigridAiringSpend = function(){
    $rootScope.formdata.hour = $scope.all_hour.join(',');
    $rootScope.formdata.day = $scope.all_day.join(',')
    $rootScope.formdata.dayparts = $scope.all_dayparts.join(',');
    $rootScope.formdata.network_id = $scope.all_networks.join(',');
    $rootScope.formdata.programs_ids = $scope.all_programs.join(',');
    var formData = $rootScope.formdata;
    var vm = this;
    var config = {
        headers : {
            'Content-Type': 'application/json; charset=utf-8'
        }
    }

    formData.sidx = $stateParams.area;
    formData.rows = '10';
    formData.page = '1';
    formData.sord = 'desc';
    formData.brand_id = $scope.id;
    formData.tab = $stateParams.tab;
    formData.brand_name = $rootScope.brand_name;
    formData.cat_id = $rootScope.formdata.cat;
    formData.breaktype = 'A';
  

    // formData.network_code = 'all_networks';
    // formData.hour = 'all_hour';
    // formData.day = 'all_day';
    // formData.dayparts = 'all_dayparts';
    // formData.network_code = 'all_netsworks';
    // formData.network_id = '29';
    // formData.programs_ids = '';

    vm.gridAiringSpend = {
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
        onRegisterApi: function (gridApi) {
            gridApi.expandable.on.rowExpandedStateChanged($scope, function (row) {
                formData.tab = 'brand';
                
                formData.dpi = '';
                formData.is_adv_page  = 0;
                if (row.isExpanded) {
                    row.entity.subAiringGridOptions = {
                        columnDefs: [
                        // { name: 'id', displayName:'id', width:'50' },
                        { name: 'creative_name', displayName:'Creatives' },
                        { name: 'language', displayName:'Type' },
                        { name: 'classification', displayName:'Classification' },
                        { name: 'duration', displayName:'Length' },
                        { name: 'program_count', displayName:'Program', cellTemplate: '<a href="" ng-click="subAiringGridOptions.grid.appScope.getProgramsByNetwork(row.entity.id,\'creative\',\''+$scope.brand_id+'\',row.entity.network_alias,row.entity.program_count);">{{COL_FIELD}}</a>' },
                        { name: 'airings', displayName:'Total Airings', cellTemplate:'<a href="" ng-click="grid.appScope.showAiringSpendGraph(\'airings\',\''+$scope.brand_id+'\', row.entity.id, row.entity.creative_name, row.entity.airings,row.entity.dpi,\''+formData.network_id+'\')">{{COL_FIELD}}</a>' ,defaultSort: {
                            direction: uiGridConstants.ASC,
                            priority: 0
                            } },
                        { name: 'total_spend', displayName:'Total Spend ($)',cellTemplate: '<a href="" ng-click="grid.appScope.showAiringSpendGraph(\'spend\',\''+$scope.brand_id+'\',row.entity.id, row.entity.creative_name, row.entity.total_spend,row.entity.dpi, \''+formData.network_id+'\')">{{COL_FIELD}}</a>' },
                        { name: 'response_type', displayName:'Response Type', cellTemplate:'<span class="response_img"><a href="#" ng-if="(row.entity.response_url==1)" title="URL" ><img src="/drmetrix_angular_clean/assets/images/url-icon.svg" alt="URL" /></a><a href="#" ng-if="(row.entity.response_sms == 1)" title="SMS"><img src="/drmetrix_angular_clean/assets/images/sms-icon.svg" alt="SMS" /></a><a href="#" ng-if="(row.entity.response_tfn == 1)" title="Telephone"><img src="/drmetrix_angular_clean/assets/images/telephone-icon.svg" alt="Telephone" /></a><a href="#" ng-if="(row.entity.response_mar == 1)" title="Mobile"><img src="/drmetrix_angular_clean/assets/images/mobile-icon.svg" alt="Mobile" /></a></span>' },
                        { name: 'national_count', displayName:'National Airings' },
                        { name: 'national', displayName:'National %' },
                        { name: 'national_spend', displayName:'National Spend ($)' },
                        { name: 'local_count', displayName:'DPI Airings' },
                        { name: 'local', displayName:'DPI %' },
                        { name: 'local_spend', displayName:'DPI Spend ($)' },
                        { name: 'first_aired', displayName:'First Aired' },
                        { name: 'last_aired', displayName:'Last Aired' },
                    ],enableGridMenu: true,
                        enableSelectAll: true,
                        paginationPageSize: 10,
                        paginationTemplate: $rootScope.correctTotalPaginationTemplate,
                        exporterCsvFilename: 'mySubFile.csv',
                        exporterCsvLinkElement: angular.element(document.querySelectorAll(".custom-csv-link-location")),
                        exporterExcelFilename: 'mySubFile.xlsx',
                        exporterExcelSheetName: 'Sheet11'
                    };

                    apiService.post('/creatives_networks', formData, config)
                    .then(function (data) {
                        row.entity.subAiringGridOptions.data = data.data.rows;
                    }, function (response) {
                        // this function handlers error
                    });
                }
            });
        },
    };

    vm.gridAiringSpend.columnDefs = [
        // { name: 'id', displayName:'id', width:'50' },
        { name: 'network_code', displayName:'Network', cellTemplate:'<a href=""><i class="clickable ng-scope ui-grid-icon-plus-squared" ng-if="!(row.groupHeader==true || row.entity.subAiringGridOptions.disableRowExpandable)" ng-class="{\'ui-grid-icon-plus-squared\' : !row.isExpanded, \'ui-grid-icon-minus-squared\' : row.isExpanded }" ng-click="grid.api.expandable.toggleRowExpansion(row.entity, $event)"></i>{{COL_FIELD}}</a>' },

        { name: 'dpi', pinnedLeft:true, displayName:'ID', cellTemplate:'<span ng-if="row.entity.dpi!=\'\'" class="dpi-symbol" style="width:30px;"><img src="assets/images/dpi_symbol.png"/></span><span ng-if="row.entity.dpi==\'\'"> - </span>'},

        { name: 'creatives', pinnedLeft:true, displayName:'Creatives' },

        { name: 'program_count', pinnedLeft:true, displayName:'Program', cellTemplate: '<a href="" ng-click="grid.appScope.getProgramsByNetwork(row.entity.id,\'brand\',\''+$scope.brand_id+'\',row.entity.network_alias,row.entity.program_count);">{{COL_FIELD}}</a>' },

        { name: 'airings', pinnedLeft:true, displayName:'Total Airings', cellTemplate:'<a href="#" ng-click="grid.appScope.showAiringSpendGraph(\'airings\',row.entity.id, \'\', row.entity.network_alias, row.entity.airings, row.entity.dpi, row.entity.id)">{{COL_FIELD}}</a>' },

        { name: 'total_spend', pinnedLeft:true, displayName:'Total Spend', cellTemplate: '<a href="#" ng-click="grid.appScope.showAiringSpendGraph(\'spend\',row.entity.id, \'\', \'row.entity.network_alias\', \'row.entity.total_spend\',row.entity.dpi,row.entity.id)">{{COL_FIELD}}</a>' },

        { name: 'national_count', pinnedLeft:true, displayName:'National Airings' },
        { name: 'national', pinnedLeft:true, displayName:'National' },
        { name: 'nat_spend', pinnedLeft:true, displayName:'National Spend' },
        { name: 'local_count', pinnedLeft:true, displayName:'DPI Airings' },
        { name: 'local_spend', pinnedLeft:true, displayName:'DPI Spend ($)' },
        { name: 'local', pinnedLeft:true, displayName:'DPI % ' },
        { name: 'duration', pinnedLeft:true, displayName:'ASD' },
    ];
    apiService.post('/brand_networks', formData, config)
    .then(function (data) {
        $scope.PostDataResponse = formData;
        vm.gridAiringSpend.data = data.data.rows;
    }, function (response) {
        // this function handlers error
    });
}

$scope.getAllNetworks = function () {
    apiService.post('/get_networks_list', $rootScope.formdata)
    .then(function (data) {
        var response = data.data;
        $rootScope.networks_loading = false;
        $rootScope.all_networks_selected = true;
        $rootScope.all_program_selected = true;
        if (response.programs != 'undefined') {
            $rootScope.programs = response.programs
            $rootScope.checkedPrograms = response.checkedPrograms;
            $scope.copyCheckedPrograms = angular.copy($rootScope.checkedPrograms);
        }
        if (response.resp_code == 1) {
            $rootScope.network_lists = response.result;
            angular.forEach($rootScope.network_lists, function(network) {
                network.isSelected = true;
            });
        } else {
           
        }
    });
}

$scope.defaultPageLoad = function() {
    $scope.id = $stateParams.id;
    $scope.uigridAiringSpend();
    $scope.getAllNetworks();
    $rootScope.networks_loading = true;
}
    
$scope.defaultPageLoad();

$scope.openModalDialog = function(name) {
    var templateUrl;
    $scope.modal_name = name;
    switch(name) {
        case 'network': {
            templateUrl = './templates/modals/networkDialog.html';
            controllerName = 'NetworkCtrl';
            break;
        }
        case 'dow': {
            templateUrl = './templates/modals/dowDialog.html';
            controllerName = 'DowCtrl';
            break;
        }
        case 'hod' : {
            templateUrl = './templates/modals/hodDialog.html';
            controllerName = 'HodCtrl';
            break;
        }
        case 'dayparts' : {
            templateUrl = './templates/modals/daypartDialog.html';
            controllerName = 'DaypartCtrl';
            break;
        }
        case 'programs' : {
            templateUrl = './templates/modals/programsDialog.html';
            controllerName = 'NetworkCtrl';
            break;
        }
    }

    $scope.openModal(templateUrl, controllerName);
}

$scope.filter_graph = function() {
    $scope.all_day = []; $scope.all_hour = []; $scope.all_dayparts = []; $scope.all_programs = []; $scope.all_networks = [];
    if ($rootScope.selectedWeekType == 'all' ) {
        $scope.dow_display_text = 0;
    } else {
        $scope.dow_display_text = 1;
        $scope.custom_variable++;
        angular.forEach($scope.weeks, function (week) {
            if(week.isSelected) {
                $scope.all_day.push(week.id);
            }
          })
    }

    if ($rootScope.allHour == true) {
        $scope.hod_display_text = 0;
    } else {
        $scope.hod_display_text = 1;
        $scope.custom_variable++;
        for (var i in $rootScope.timeData) {
            for(var j in $rootScope.timeData[i].data) {
                if($rootScope.timeData[i].data[j].isSelected)
                    $scope.all_hour.push($rootScope.timeData[i].data[j].value);
            }
        }
    }

    if ($rootScope.allDayparts == true) {
        $scope.daypart_display_text = 0;
    } else {
        $scope.daypart_display_text = 1;
        $scope.custom_variable++;
        angular.forEach($rootScope.dayparts, function (daypart) {
            if(daypart.isSelected) {
                $scope.all_dayparts.push(daypart.id);
            }
          })
    }
    
    for (var i in $rootScope.network_lists) {
        if($rootScope.network_lists[i].isSelected) {
            $scope.all_networks.push($rootScope.network_lists[i].network_id)
        }
    }
  
    angular.forEach($rootScope.programs, function (p) {
        angular.forEach(p, function(prg , prg_id) {
            if(prg.isSelected) {
                $scope.all_programs.push(prg_id);
            }
        });
        
    });
    $scope.uigridAiringSpend();

    // $rootScope.formdata.network_code = 'all_networks';
}
$scope.getProgramsByNetwork = function(network_id,brand,brand_id,network_alias,program_count) {
    $rootScope.network_id = network_id;
    $scope.brand = brand;
    $rootScope.brand_id = brand_id;
    $scope.network_alias = network_alias;
    $scope.program_count = program_count;s
    $scope.openProgramModal();
}

$scope.openProgramModal = function() {
    $scope.modalInstance =  $uibModal.open({
        templateUrl: "./templates/modals/ProgramDialog.html",
        controller: "ProgramModalCtrl",
        size: 'lg modal-dialog-centered',
      });
}

});

angular.module('drmApp').controller('NetworkCtrl', function($scope, $rootScope, $uibModalInstance){
    $scope.forNetworkList = function (value) {
        $rootScope.all_networks_selected = value;
        for (var i in $rootScope.network_lists) {
            $rootScope.network_lists[i].isSelected = $rootScope.all_networks_selected;
        }
        // $rootScope.all_program_selected = $rootScope.all_networks_selected;
      
        angular.forEach($rootScope.programs, function (p) {
            angular.forEach(p, function(prg) {
                prg.isSelected = $rootScope.all_program_selected;
            });
            
        });
    }

    $scope.forProgramList = function() {
        $rootScope.all_program_selected = !$rootScope.all_program_selected;
        if($rootScope.all_program_selected) {
            $rootScope.checkedPrograms = $rootScope.copyCheckedPrograms;
        } else {
            $rootScope.checkedPrograms = [];
        }

        angular.forEach($rootScope.programs, function (p) {
            angular.forEach(p, function(prg) {
                prg.isSelected = $rootScope.all_program_selected;
            });
            
        });
    }

    $scope.forSingleNetwork = function() {
        var temp = true;
        $rootScope.network_selection = [];
        angular.forEach($rootScope.network_lists, function (network) {
            if(network.isSelected) {
                $rootScope.network_selection.push(network.network_id);
                temp = false;
            }
        });
        $rootScope.all_networks_selected = temp;

        angular.forEach($rootScope.programs, function (p) {
            angular.forEach(p, function(prg) {
                if($rootScope.network_selection.indexOf(prg.network_id) !== -1)
                    prg.isSelected = $rootScope.all_program_selected;
            });
        });

    }

    $scope.forSingleProgram = function() {
        var temp = true;
        angular.forEach($rootScope.programs, function (p) {
            if(!p.isSelected) {
                temp = false;
            }
        });
        $rootScope.all_program_selected = temp;
    }

    $scope.checkChildIsHide = function(id) {
        return $('#'+id+ ' div ').is(':visible');
    }
    
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
});

angular.module('drmApp').controller('DowCtrl', function($scope, $rootScope, $uibModalInstance){
    $scope.adow = function (type, items) {
        $rootScope.selectedWeekType = type;
        for (var i in items) {
            if ($rootScope.weekType[type].indexOf(items[i].id) != -1) {
                items[i].isSelected = true;
            } else {
                items[i].isSelected = false;
            }
        }
    }

    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

});

angular.module('drmApp').controller('HodCtrl', function($scope, $rootScope, $uibModalInstance, $state, apiService){
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    $scope.changeHod = function (value, items) {
        var temp = true;
        for (var i in items) {
            for(var j in items[i].data) {
                if(!items[i].data[j].isSelected)
                    temp = false
            }
        }
        $rootScope.allHour = temp;
    }

    $scope.forHod = function (value, items) {
        for (var i in items) {
            for(var j in items[i].data) {
                items[i].data[j].isSelected = value;
            }
        }
    }
});

angular.module('drmApp').controller('DaypartCtrl', function($scope, $rootScope, $uibModalInstance){
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }
    

    $scope.adayparts = function (value, items) {
        for (var i in items) {
                items[i].isSelected = value;
        }
    }

    $scope.changedaypart= function (value, items) {
        var temp = true;
        for (var i in items) {
                if(!items[i].isSelected)
                    temp = false
        }
        $rootScope.allDayparts = temp;
    }


});

angular.module('drmApp').controller('ProgramModalCtrl', function($scope, $http, $interval, uiGridTreeViewConstants, $uibModal, $rootScope, $uibModalInstance, $state, apiService) {
    $scope.closeModal = function() {
        $uibModalInstance.dismiss();
    }

    // Call brand List ui Grid
    $scope.uigridProgramModdal = function() {
        var formData = $rootScope.formdata;
        var vm = this;
        var config = {
            headers : {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }
        var c_dir = '6';
        formData.network_id = $rootScope.network_id;
        formData.id = $rootScope.brand_id;
        formData.area = 'brand';

        vm.gridOptionsProgram = {
            enableGridMenu: true,
            enableSelectAll: true,
            enableSorting: true,
            paginationPageSize: 10,
            paginationTemplate: $rootScope.correctTotalPaginationTemplate,
        };

        vm.gridOptionsProgram.columnDefs = [
            { name: 'program', pinnedLeft:true, displayName:'Program'},
            { name: 'start_time', pinnedLeft:true, displayName:'Star Time (EST)'},
            { name: 'total_airings', pinnedLeft:true, displayName:'Total Airings'},
            { name: 'total_spend', pinnedLeft:true, displayName:'Total Spend'},
            { name: 'national_airings', pinnedLeft:true, displayName:'National Airings'},
            { name: 'national_percent', pinnedLeft:true, displayName:'National %'},
            { name: 'national_spend', pinnedLeft:true, displayName:'National Spend($)'},
            { name: 'local_airings', pinnedLeft:true, displayName:'DPI Airings'},
            { name: 'local_percent', pinnedLeft:true, displayName:'DPI %'},
            { name: 'local_spend', pinnedLeft:true, displayName:'DPI Spend($)'},
        ];
        apiService.post('/get_programs_by_network', formData, config)
        .then(function (data) {
            $scope.PostDataResponse = formData;
            vm.gridOptionsProgram.data = data.data.rows;
        }, function (response) {
            // this function handlers error
        });
    }

    $scope.show_Program_list = function () {
        //ui grid code
        $scope.uigridProgramModdal();
    }
    $scope.show_Program_list();
});