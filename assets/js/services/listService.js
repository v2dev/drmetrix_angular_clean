(function() {
    "use strict";
    angular
        .module("drmApp")
        .factory("listService", ['$rootScope', "$log", listService]);

    function listService($rootScope, $log, REST_END_POINT) {
        var listModel = {
            idsOfSelectedRows: []
        };

        return {
            listModel: listModel
        };
    }
}());