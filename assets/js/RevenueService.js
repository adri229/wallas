'use strict';

var wallas = angular.module('wallasApp');

wallas.factory('RevenueService', ['$http', function($http){

	var revenueService = {};

	revenueService.create = function(revenue) {
		return $http.post('rest/revenues', {
			quantity: revenue.quantity,
			name: revenue.name
		}); 
	}

	revenueService.update = function(revenue) {
		console.log(revenue);
		return $http.put('rest/revenues/'.concat(revenue.idRevenue), {
			quantity: revenue.quantity,
			name: revenue.name
		});
	}

	revenueService.getByOwner = function(login) {
		return $http.get('rest/revenues/'.concat(login) + '?startDate=0000-00-00&endDate=2016-05-13');
	}


	revenueService.delete = function(id) {
		return $http.delete('rest/revenues/' + id);
	}

	return revenueService;

}]);
