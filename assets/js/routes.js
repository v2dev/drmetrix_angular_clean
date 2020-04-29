states =
[
    {
        name: 'login',
        url: '/login',
        templateUrl: 'templates/login.html',
        controller: 'LoginController',
        data: {
          roles: []
        },
        
      },
      {
        name: 'ranking',
        url: '/ranking',
        templateUrl: 'templates/ranking.html',
        controller: 'RankingController',
        data: {
          roles: ['User','Admin']
        },
        // resolve: { authenticate: apiService.authenticate() }
      },
      {
        name: 'accesdenied',
        url: '/accesdenied',
        templateUrl: 'templates/access-denied.html',
        // controller: 'AuthyController'
      },
      {
        name: 'authy',
        url: '/authy',
        templateUrl: 'templates/authy.html',
        controller: 'AuthyController'
      },
      {
        name: 'eulaAgreement',
        url: '/eulaAgreement',
        templateUrl: 'templates/eulaAgreement.html',
        controller: 'EulaAgreementController'
      }, 
      {
        name: 'userAccount',
        url: '/userAccount',
        templateUrl: 'templates/userAccount.html',
        controller: 'UserController'
      },
      {
        name: 'adminConsole',
        url: '/adminConsole',
        templateUrl: 'templates/adminConsole.html',
        controller: 'AdminController'
      },
      {
        name: 'forgotPassword',
        url: '/forgotPassword',
        templateUrl: 'templates/forgot-password.html',
        controller: 'LoginController'
      },
      {
        name: 'network',
        url: '/network',
        templateUrl: 'templates/network.html',
        controller: 'NetworkController',
        // resolve: { authenticate: apiService.authenticate() }
      },
      {
        name: 'globalSearch',
        url: '/globalSearch',
        templateUrl: 'templates/globalSearch.html',
        controller: 'GlobalSearchController'
      },
      {
        name: 'configureEmails',
        url: '/configureEmails',
        templateUrl: 'templates/configureEmails.html',
        controller: 'ConfigureEmailsController'
      }
];