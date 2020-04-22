'use strict';

angular.module('drmApp').filter('ucfirst', function () {
    return function (input) {
        if(typeof(input) != 'undefined') {
            return input.charAt(0).toUpperCase() + input.substr(1).toLowerCase();
        }
    }
});