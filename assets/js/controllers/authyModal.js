var app = angular.module("drmApp", []);
app.controller("AuthyModalController", ["$scope", function ($scope) {
   $scope.someValueField = "";
   
   var dlgElem = angular.element("#auhtyModalDlg");
   if (dlgElem) {
      dlgElem.on("hide.bs.modal", function() {
         $scope.someValueField = "";
         console.log("reset data model..");
      });
   }
   
   $scope.modalButtonClick = function () {
      console.log("do action on Modal");
      console.log("Current 'someValueField' value is [[" + $scope.someValueField + "]]");
   };
}]);