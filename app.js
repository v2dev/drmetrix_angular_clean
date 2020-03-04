var drmApp = angular.module('drmApp', ['ui.router', 'starter.services', 'ngCookies', 'ui.bootstrap', 'ngTouch', 'ui.grid', 'ui.grid.pagination', 'ui.grid.autoResize', 'ui.grid.expandable',
    'ui.grid.selection', 'ui.grid.treeView', 'ui.grid.exporter', 'ui.grid.edit', 'ui.bootstrap']);

drmApp.config(function ($stateProvider, $urlRouterProvider) {

    $urlRouterProvider.otherwise('/home');

    $stateProvider

        // HOME STATES AND NESTED VIEWS ========================================
        .state('home', {
            url: '/home',
            templateUrl: 'templates/home.html',
            controller: 'LoginController'
        })
        .state('authy', {
            url: '/authy',
            templateUrl: 'templates/authy.html',
            controller: 'AuthyController'
        })
        .state('eulaAgreement', {
            url: '/eulaAgreement',
            templateUrl: 'templates/eulaAgreement.html',
            controller: 'EulaAgreementController'
        })
        .state('ranking', {
            url: '/ranking',
            templateUrl: 'templates/ranking.html',
            controller: 'RankingController'
        })
        .state('userAccount', {
            url: '/userAccount',
            templateUrl: 'templates/userAccount.html',
            controller: 'UserController'
        })

        // // nested list with custom controller
        // .state('home.list', {
        //     url: '/list',
        //     templateUrl: 'partial-home-list.html',
        //     controller: function($scope) {
        //         $scope.dogs = ['Bernese', 'Husky', 'Goldendoodle'];
        //     }
        // })

        // nested list with just some random string data
        .state('forgotPassword', {
            url: '/forgotPassword',
            templateUrl: 'templates/forgot-password.html',
			controller: 'LoginController'
        })
        .state('network', {
            url: '/network',
            templateUrl: 'templates/network.html',
            controller: 'NetworkController'
        });
});
