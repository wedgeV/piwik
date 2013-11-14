var customInterpolationApp = angular.module('piwikApp', []);

customInterpolationApp.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('!?');
    $interpolateProvider.endSymbol('?!');
});

var customApp = angular.module('app', []);
customInterpolationApp.filter('startFrom', function() {
    return function(input, start) {
        start = +start; //parse to int
        return input.slice(start);
    }
});