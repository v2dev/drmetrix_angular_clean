//Application services used commonly throughout the app
angular.module('starter.services', [])
    .constant('myConfig', {
        'apiUrl': window.location.pathname +'/api/index.php',
        'apiVersion': 'QA 2.6.3'
    })
    //API service for all GET and POST calls to QA and PROD Server
    .service('apiService', function($http, myConfig, $rootScope, $location) {
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
            isUserLogged: function isUserLogged($scope){
               var pdf =  sessionStorage.pdf;
               var username = $rootScope.getCookie("loggedIn");
               var user_role = $rootScope.getCookie("userrole");
      
                if(pdf == 1){
                    username = true;
                }
                if(username){
                    //$scope.user.loggedIn = true;
                    var prototype= Object.getPrototypeOf($scope); //$scope.__proto__
                    //console.log(prototype);
                    prototype.user.loggedIn = true;
                    prototype.user.name = username;
                    prototype.user.notLoggedIn = false;
                    prototype.user.assistant_admin = sessionStorage.assistant_admin;
                    localStorage.superadmin = 0;
                    localStorage.admin = 0;
                    localStorage.login_user = 0;
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
        };
    })
    ;