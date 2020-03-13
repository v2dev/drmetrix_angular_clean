angular.module('drmApp').filter('monthName', [function() {
    return function (monthNumber) { //1 = January
        var monthNames = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];
        return monthNames[monthNumber - 1];
    }
}]);