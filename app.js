var drmApp = angular.module('drmApp', ['ui.router','starter.services','ngCookies','ui.bootstrap','ngPatternRestrict']);
drmApp.config(function($stateProvider, $urlRouterProvider) {
    
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
        .state('forgotPassword', {
			url: '/forgotPassword',
            templateUrl: 'templates/forgot-password.html',
			controller: 'LoginController'
        })
        .state('ranking', {
			url: '/ranking',
            templateUrl: 'templates/ranking.html',
            controller: 'RankingController'
        }).state('network', {
            url: '/network',
            templateUrl: 'templates/network.html',
            controller: 'NetworkController'
        });
});
