angular.module('drmApp.directives', []);

var drmApp = angular.module('drmApp', ['ui.router', 'starter.services', 'ngCookies', 'ui.bootstrap', 'ngTouch', 'ui.grid', 'ui.grid.pagination', 'ui.grid.autoResize', 'ui.grid.expandable',
    'ui.grid.selection', 'ui.grid.treeView', 'ui.grid.exporter', 'ui.grid.edit','ui.grid.cellNav', 'ui.bootstrap', 'drmApp.directives']);
    drmApp.service('myService', function() {

    });

drmApp.config(function ($stateProvider, $urlRouterProvider) {
    // states.forEach((state) => $stateProvider.state(state));
        $urlRouterProvider.otherwise('/home');
        $stateProvider.state('home',{
            url: '/home',
            templateUrl: 'templates/home.html',
            controller: 'LoginController', 
          })
          .state('ranking', {
            url: '/ranking',
            templateUrl: 'templates/ranking.html',
            controller: 'RankingController',
            resolve: { authenticate: authenticate}
          })
          .state('network',{
            url: '/network',
            templateUrl: 'templates/network.html',
            controller: 'NetworkController',
            resolve: { authenticate: authenticate }
          }) .state('authy',{
            url: '/authy',
            templateUrl: 'templates/authy.html',
            controller: 'AuthyController'
          }).state('eulaAgreement',{
            name: 'eulaAgreement',
            url: '/eulaAgreement',
            templateUrl: 'templates/eulaAgreement.html',
            controller: 'EulaAgreementController'
          }).state('userAccount', {
            url: '/userAccount',
            templateUrl: 'templates/userAccount.html',
            controller: 'UserController'
          }).state('adminConsole' ,{
            url: '/adminConsole',
            templateUrl: 'templates/adminConsole.html',
            controller: 'AdminController'
          }).state('forgotPassword', {
            url: '/forgotPassword',
            templateUrl: 'templates/forgot-password.html',
            controller: 'LoginController'
          }).state('globalSearch', {
            url: '/globalSearch',
            templateUrl: 'templates/globalSearch.html',
            controller: 'GlobalSearchController'
          }).state('configureEmails', {
            url: '/configureEmails',
            templateUrl: 'templates/configureEmails.html',
            controller: 'ConfigureEmailsController'
          })
          
        function authenticate($q, apiService, $state, $timeout) {
            if (apiService.isUserLogged()) {
              // Resolve the promise successfully
              return $q.when()
            } else {
              // The next bit of code is asynchronously tricky.
      
              $timeout(function() {
                // This code runs after the authentication promise has been rejected.
                // Go to the log-in page
                $state.go('logInPage')
              })
      
              // Reject the authentication promise to prevent the state from loading
              return $q.reject()
            }
          }
          
});
    // console.log(states);