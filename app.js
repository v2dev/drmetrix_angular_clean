angular.module('drmApp.directives', []);

var drmApp = angular.module('drmApp', ['ui.router', 'starter.services', 'ngCookies', 'ui.bootstrap', 'ngTouch', 'ui.grid', 'ui.grid.pagination', 'ui.grid.autoResize', 'ui.grid.expandable',
    'ui.grid.selection', 'ui.grid.treeView', 'ui.grid.exporter', 'ui.grid.edit','ui.grid.cellNav', 'ui.bootstrap', 'drmApp.directives']);

drmApp.config(function ($stateProvider, $urlRouterProvider) {
    // states.forEach((state) => $stateProvider.state(state)); // for dyncamic routes
        $urlRouterProvider.otherwise('/login');
        $stateProvider.state('login',{
            url: '/login',
            templateUrl: 'templates/login.html',
            controller: 'LoginController', 
          })
          .state('ranking', {
              url: '/ranking',
              templateUrl: 'templates/ranking.html',
              controller: 'RankingController',
            //   resolve: { authenticate: authenticate}
          })
          .state('airing_detail', {
            url: '/airing_detail/:id/:area/:tab',
            templateUrl: 'templates/airingDetail.html',
            controller: 'AiringsDetailController',
            resolve: { authenticate: authenticate}
        })
        .state('advertiser_detail', {
            url: '/advertiser_detail/:id/:tab',
            templateUrl: 'templates/advertiserDetail.html',
            controller: 'AdvertiserDetailController',
            resolve: { authenticate: authenticate}
        })
        .state('rosdaypart_detail', {
            url: '/rosdaypart_detail/:id/:area/:tab',
            templateUrl: 'templates/airingDetail.html',
            controller: 'AiringsDetailController',
            resolve: { authenticate: authenticate}
        })
          .state('network',{
              url: '/network',
              templateUrl: 'templates/network.html',
              controller: 'NetworkController',
              resolve: { authenticate: authenticate }
          }) 
          .state('authy',{
              url: '/authy',
              templateUrl: 'templates/authy.html',
              controller: 'AuthyController',
              resolve: { authenticate: authenticate }
          })
          .state('eulaAgreement',{
              url: '/eulaAgreement',
              templateUrl: 'templates/eulaAgreement.html',
              controller: 'EulaAgreementController',
              resolve: { authenticate: authenticate }
          })
          .state('userAccount', {
              url: '/userAccount',
              templateUrl: 'templates/userAccount.html',
              controller: 'UserController',
              resolve: { authenticate: authenticate }
          })
          .state('adminConsole' ,{
              url: '/adminConsole',
              templateUrl: 'templates/adminConsole.html',
              controller: 'AdminController',
              resolve: { authenticate: authenticate }
          })
          .state('forgotPassword', {
              url: '/forgotPassword',
              templateUrl: 'templates/forgot-password.html',
              controller: 'LoginController'
          })
          .state('globalSearch', {
              url: '/globalSearch',
              templateUrl: 'templates/globalSearch.html',
              controller: 'GlobalSearchController',
              resolve: { authenticate: authenticate }
          })
          .state('configureEmails', {
              url: '/configureEmails',
              templateUrl: 'templates/configureEmails.html',
              controller: 'ConfigureEmailsController',
               resolve: { authenticate: authenticate }
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
                $state.go('login')
              })
      
              // Reject the authentication promise to prevent the state from loading
              return $q.reject()
            }
          }
          
});