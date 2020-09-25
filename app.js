angular.module('drmApp.directives', []);

var drmApp = angular.module('drmApp', ['ui.router', 'starter.services', 'ngCookies', 'ui.bootstrap', 'ngTouch', 'ui.grid', 'ui.grid.pagination', 'ui.grid.autoResize', 'ui.grid.expandable',
    'ui.grid.selection', 'ui.grid.treeView', 'ui.grid.exporter', 'ui.grid.edit','ui.grid.cellNav', 'ui.bootstrap', 'drmApp.directives']);

drmApp.config(function ($stateProvider, $urlRouterProvider) {
    // states.forEach((state) => $stateProvider.state(state)); // for dyncamic routes
        $stateProvider.state('login',{
            url: '/login',
            templateUrl: 'templates/login.html',
            controller: 'LoginController', 
            hideNavbar: true
          })
          .state('ranking', {
              url: '/ranking',
              templateUrl: 'templates/ranking.html',
              controller: 'RankingController',
              resolve: { authenticate: authenticate},
              hideNavbar: false
          })
          .state('airing_detail', {
            url: '/airing_detail/:id/:area/:tab',
            templateUrl: 'templates/airingDetail.html',
            controller: 'AiringsDetailController',
            resolve: { authenticate: authenticate},
            hideNavbar: false
        })
        .state('advertiser_detail', {
            url: '/advertiser_detail/:id/:tab',
            templateUrl: 'templates/advertiserDetail.html',
            controller: 'AdvertiserDetailController',
            resolve: { authenticate: authenticate},
            hideNavbar: false
        })
        .state('rosdaypart_detail', {
            url: '/rosdaypart_detail/:id/:area/:tab',
            templateUrl: 'templates/airingDetail.html',
            controller: 'AiringsDetailController',
            resolve: { authenticate: authenticate},
            hideNavbar: false
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
              resolve: { authenticate: authenticate },
              hideNavbar: true
          })
          .state('eulaAgreement',{
              url: '/eulaAgreement',
              templateUrl: 'templates/eulaAgreement.html',
              controller: 'EulaAgreementController',
              resolve: { authenticate: authenticate },
              hideNavbar: true
          })
          .state('userAccount', {
              url: '/userAccount',
              templateUrl: 'templates/userAccount.html',
              controller: 'UserController',
              resolve: { authenticate: authenticate },
              hideNavbar: false
          })
          .state('adminConsole' ,{
              url: '/adminConsole',
              templateUrl: 'templates/adminConsole.html',
              controller: 'AdminController',
              resolve: { authenticate: authenticate },
              hideNavbar: false
          })
          .state('forgotPassword', {
              url: '/forgotPassword',
              templateUrl: 'templates/forgot-password.html',
              controller: 'LoginController',
              hideNavbar: true
          })
          .state('globalSearch', {
              url: '/globalSearch',
              templateUrl: 'templates/globalSearch.html',
              controller: 'GlobalSearchController',
              resolve: { authenticate: authenticate },
              hideNavbar: false
          })
          .state('configureEmails', {
              url: '/configureEmails',
              templateUrl: 'templates/configureEmails.html',
              controller: 'ConfigureEmailsController',
               resolve: { authenticate: authenticate },
               hideNavbar: false
          })
        $urlRouterProvider.otherwise('/login');
          
        function authenticate($q, apiService, $state) {
            var deferred = $q.defer();
            if (apiService.isUserLogged()) {
                deferred.resolve();
            } else {
                deferred.reject();
                $state.go('login');
            }
            return deferred.promise;
          }
          
});