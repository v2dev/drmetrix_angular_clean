angular.module('drmApp.directives', []);

var drmApp = angular.module('drmApp', ['ui.router', 'starter.services', 'ngCookies', 'ui.bootstrap', 'ngTouch', 'ui.grid', 'ui.grid.pagination', 'ui.grid.autoResize', 'ui.grid.expandable',
    'ui.grid.selection', 'ui.grid.treeView', 'ui.grid.exporter', 'ui.grid.edit', 'ui.bootstrap', 'drmApp.directives']);

drmApp.config(function ($stateProvider, $urlRouterProvider) {

    $urlRouterProvider.otherwise('/home');

    $stateProvider
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

        .state('forgotPassword', {
            url: '/forgotPassword',
            templateUrl: 'templates/forgot-password.html',
			controller: 'LoginController'
        })
        .state('network', {
            url: '/network',
            templateUrl: 'templates/network.html',
            controller: 'NetworkController'
        })
        // .state('airingDetail', {
        //     url: '/airingDetail',
        //     templateUrl: 'templates/modals/airingDetail.html',
        //     controller: 'RankingController'
        // });
        .state('globalSearch', {
            url: '/globalSearch',
            templateUrl: 'templates/globalSearch.html',
            controller: 'GlobalSearchController'
        })
        .state('configureEmails', {
            url: '/configureEmails',
            templateUrl: 'templates/configureEmails.html',
            controller: 'ConfigureEmailsController'
        });
});
