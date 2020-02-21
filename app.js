var drmApp = angular.module('drmApp', ['ui.router','starter.services','ngCookies','ui.bootstrap']);

drmApp.config(function($stateProvider, $urlRouterProvider) {
    
    $urlRouterProvider.otherwise('/home');
    
    $stateProvider
        
        // HOME STATES AND NESTED VIEWS ========================================
        .state('home', {
            url: '/home',
            templateUrl: 'templates/home.html',
			controller: 'LoginController'
		})
		.state('about', {
			url: '/about',
			templateUrl: 'templates/about.html',
            // we'll get to this in a bit       
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
        });
});


// mainApp.controller('StudentController', function($scope) {
// 	$scope.students = [
// 		{name: 'Mark Waugh', city:'New York'},
// 		{name: 'Steve Jonathan', city:'London'},
// 		{name: 'John Marcus', city:'Paris'}
// 	];

// 	$scope.message = "Click on the hyper link to view the students list.";
// });
