//Application services used commonly throughout the app
"use strict";
angular.module('starter.services', [])
    .constant('myConfig', {
        'apiUrl': './api/index.php',
        'apiVersion': 'QA 2.6.3'
    })
    //API service for all GET and POST calls to QA and PROD Server
    .service('apiService', function($http, myConfig, $rootScope, $location, $cookies) {
        var category;
        return {
            getext: function(path) {
                return $http.get(path);
            },
            get: function(path) {
                return $http.get(myConfig.apiUrl + path,{timeout :120000});
            },
            post: function(path, postData) {
                return $http.post(myConfig.apiUrl + path, postData,{timeout :120000});
            },
           authenticate : function($q, $state, $timeout) {
                if (this.isUserLogged()) {
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
              },
            isUserLogged: function isUserLogged($scope){
               var username = $cookies.get('loggedIn');
               var user_role = $cookies.get('userrole');
                if(username){
                    localStorage.superadmin =  localStorage.admin =  localStorage.login_user = 0;
                    if(user_role == 'superadmin'){
                        localStorage.superadmin = 1;
                    }else if(user_role == 'admin'){
                        localStorage.admin = 1;
                    }else{
                        localStorage.login_user = 1;
                    }
                    return 1;
                }
                return 0;
            },
            validate_mobile: function (v) {
                console.log(v);
                v = v
                    .match(/\d*/g).join('')
                    .match(/(\d{0,3})(\d{0,3})(\d{0,12})/).slice(1).join('-')
                    .replace(/-*$/g, '');
                return v;
            }
        };
    });