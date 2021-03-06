'use strict';

var wallas = angular.module('wallasApp');

wallas.controller('StockController', ['$scope', '$cookies', '$uibModal', 'StockService',
	function($scope, $cookies, $uibModal, StockService) {

	var user = $cookies.getObject('globals');
    var login = user.currentUser.login;

    $scope.hide = false;

    $scope.changeSort = function(sort) {
        switch (sort) {
            case 1: 
                if ($scope.sortStock == 'total') {
                    $scope.sortStock = '-total';
                    $scope.classSort = 'fa fa-caret-up';
                } else {
                    $scope.sortStock = 'total';
                    $scope.classSort = 'fa fa-caret-down';
                }
                break;
            case 2:
                if ($scope.sortStock == 'date') {
                    $scope.sortStock = '-date';
                    $scope.classSort = 'fa fa-caret-up';
                } else {
                    $scope.sortStock = 'date';
                    $scope.classSort = 'fa fa-caret-down';
                }
                break;
            default:
                break;
        }
        

    }

    $scope.intervalDate = function(option) {
            var defaultDate = new Date();
            var defaultStartDate = new Date();
            var defaultEndDate = new Date();
            switch (option) {
                case 1:
                    defaultStartDate.setDate(1);
                    defaultEndDate.setMonth(defaultEndDate.getMonth()+1);
                    defaultEndDate.setDate(1);
                    break;
                case 2:
                    defaultStartDate.setDate(1);
                    defaultStartDate.setMonth(defaultStartDate.getMonth()-2);
                    defaultEndDate.setMonth(defaultEndDate.getMonth()+1);
                    defaultEndDate.setDate(1);
                    break;
                case 3:
                    defaultStartDate.setFullYear(defaultStartDate.getFullYear()-1);
                    defaultStartDate.setDate(1);
                    defaultEndDate.setMonth(defaultEndDate.getMonth()+1);
                    defaultEndDate.setDate(1);
                    break;
                default:
                    break;
            }
            refreshStocks(defaultStartDate, defaultEndDate);
        }

    function notification(msg, type) {
        $scope.message = msg;
        $scope.type = type;
        $scope.hide = true;
        $scope.show = true;
    }

    function refreshStocks(startDate, endDate) {
        $scope.sortStock = 'total';
        $scope.classSort = 'fa fa-caret-down';
        
    	StockService.getByOwner(login, startDate, endDate).then(
	  		function(response) {
				$scope.stocks = response;
			},
			function(response) {
				notification("You don't have any stocks in this range of dates", "info");
	        	$scope.stocks = null;
			}
		);	
    }

    function defaultIntervalDate() {
        $scope.intervalDate(1);
    }
    defaultIntervalDate();


    $scope.changeIntervalStocks = function() {
        if ($scope.startDate != null && $scope.endDate != null) {
            refreshStocks($scope.startDate, $scope.endDate);      
        }
    }

    $scope.create = function() {
    	var uibmodalInstance = $uibModal.open({
  			templateUrl: 'assets/html/modalNewStock.html',
  			controller: 'StockModalController',
            animation : true,
            backdrop: false,
  			scope: $scope,
            resolve: {
                stocks: function() {

                }    
            }
  		});

  		uibmodalInstance.result.then(
  			function(response) {
                notification("New stock was added successfully", "success");
  				if ($scope.startDate == null || $scope.endDate == null) {
                    defaultIntervalDate();
                } else {
                    refreshStocks($scope.startDate, $scope.endDate);  
                }
  			},
  			function(response) {
  				if (response.localeCompare("cancel") != 0  && response.localeCompare("escape key press")) {
                    notification("An error ocurred!", "danger");
                }
  			}
  		)
    };

    
	
	$scope.update = function(stock) {
		var uibmodalInstance = $uibModal.open({
            templateUrl: 'assets/html/modalUpdateStock.html',
            controller: 'StockModalController',
            animation : true,
            backdrop: false,
            scope: $scope,
            resolve: {
                stocks: function() {
                    return {
                        stock: stock
                    }
                     
                }
            }
        });

        uibmodalInstance.result.then(
            function(response) {
                notification("Stock updated successfully", "success");
                if ($scope.startDate == null || $scope.endDate == null) {
                    defaultIntervalDate();
                } else {
                    refreshStocks($scope.startDate, $scope.endDate);  
                }
            },
            function(response) {
                if (response.localeCompare("cancel") != 0 && response.localeCompare("escape key press")) {
                    notification("An error ocurred!", "danger");
                } else {
                    if ($scope.startDate == null || $scope.endDate == null) {
                       defaultIntervalDate();
                    } else {
                        refreshStocks($scope.startDate, $scope.endDate);  
                    }      
                }
            }
        )
	}

  	$scope.delete = function(stock) {
  		var uibmodalInstance = $uibModal.open({
            templateUrl: 'assets/html/modalDeleteStock.html',
            controller: 'StockModalController',
            animation : true,
            backdrop: false,
            scope: $scope,
            resolve: {
                stocks: function() {
                    return {
                        idStock: stock.idStock
                    }
                     
                }
            }
        });

        uibmodalInstance.result.then(
            function(response) {
                notification("Stock deleted successfully", "success");
                if ($scope.startDate == null || $scope.endDate == null) {
                    defaultIntervalDate();
                } else {
                    refreshStocks($scope.startDate, $scope.endDate);  
                }
            },
            function(response) {
                if (response.localeCompare("cancel") != 0 && response.localeCompare("escape key press")) {
                    notification("An error ocurred!", "danger");
                }
            }
        )
  	};

    $scope.dateOptions = {
            formatYear: 'yy',
            maxDate: new Date(2020, 5, 22),
            minDate: new Date(1982, 7, 21),
            startingDay: 1
        };

        
        $scope.openInitDate = function() {
            $scope.datepopupInitOpened = true;
        };
        $scope.openEndDate = function() {
            $scope.datepopupEndOpened = true;
        };

    $scope.show = true;
  
    $scope.closeAlert = function(index) {
        $scope.show = false;
    };

}]);