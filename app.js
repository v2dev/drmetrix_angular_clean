
var drmApp = angular.module('drmApp', ['ngRoute','starter.services','ngCookies']);
drmApp.config(function($routeProvider){
	$routeProvider
		.when('/', {
			templateUrl: 'templates/home.html',
			controller: 'LoginController'
		})
		.when('/red', {
			templateUrl: 'templates/red.html',
			// controller: 'StudentController'
		});
	}
);

// mainApp.controller('StudentController', function($scope) {
// 	$scope.students = [
// 		{name: 'Mark Waugh', city:'New York'},
// 		{name: 'Steve Jonathan', city:'London'},
// 		{name: 'John Marcus', city:'Paris'}
// 	];

// 	$scope.message = "Click on the hyper link to view the students list.";
// });
