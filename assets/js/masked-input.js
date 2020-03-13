var maskedInput = function() {
    var directive = {
      restrict: 'EA',
      require: 'ngModel',
      link: function(scope, element, attrs, ngModel) {
        console.log("In link function");

        var addSpaces = function(value) {
          if (typeof(value) == typeof(undefined))
            return value;
          var x = value.toString()
          .replace(/\D/g, '')
          .match(/(\d{3})(\d{3})(\d{4})/);
          parsedValue = '' + x[1] + '-' + x[2] + '-' + x[3];
          return parsedValue;
        }

        var removeSpaces = function(value) {
          if (typeof(value) == typeof(undefined))
            return value;
          var parsedValue = value.toString().replace(/\s/g, '').replace(/-/g, '');
          return parsedValue;
        }

        var parseViewValue = function(value) {
          var viewValue = addSpaces(value);
          ngModel.$viewValue = viewValue;
          ngModel.$render();

          // Return what we want the model value to be
          return removeSpaces(viewValue);
        }

        var formatModelValue = function(value) {
          var modelValue = removeSpaces(value);
          ngModel.$modelValue = modelValue;
          return addSpaces(modelValue);
        }

        ngModel.$parsers.push(parseViewValue);
        ngModel.$formatters.push(formatModelValue);
      }
    };
    return directive;
  }


  maskedInput.$inject = [];
  angular.module("drmApp.directives")
    .directive('maskedInput', maskedInput);