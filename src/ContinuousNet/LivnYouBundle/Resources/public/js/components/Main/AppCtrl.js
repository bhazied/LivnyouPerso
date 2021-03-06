'use strict';

/**
 * LivnYou Back Office Main Controller
 */
app.controller('AppCtrl', ['$rootScope', '$scope', '$state', '$translate', '$localStorage', '$window', '$document', '$timeout', 'cfpLoadingBar', '$filter', '$stateParams',
function($rootScope, $scope, $state, $translate, $localStorage, $window, $document, $timeout, cfpLoadingBar, $filter, $stateParams) {

	$scope.anonymousStates = ['auth.login', 'auth.register', 'auth.resetpassword', 'auth.reset', 'auth.lockscreen', 'auth.emailconfirm'];

	$timeout(function() {
		if ($scope.anonymousStates.indexOf($state.current.name) == -1 && !angular.isDefined($localStorage.access_token)) {
			$timeout(function() {
				console.warn('no access token for ('+$state.current.name+') > redirection');
				$state.go('auth.login');
			});
		}
	}, 2000);

	// Loading bar transition
	// -----------------------------------
	var $win = $($window);

	$rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams) {
		//start loading bar on stateChangeStart
		cfpLoadingBar.start();

	});
	
	$rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {

		//stop loading bar on stateChangeSuccess
		event.targetScope.$watch("$viewContentLoaded", function() {

			cfpLoadingBar.complete();
		});

		// scroll top the page on change state
		$('#app .main-content').css({
			position : 'relative',
			top : 'auto'
		});
		
		$('footer').show();
		
		window.scrollTo(0, 0);

		if (angular.element('.email-reader').length) {
			angular.element('.email-reader').animate({
				scrollTop : 0
			}, 0);
		}

		// Save the route title
		$rootScope.currTitle = $filter('translate')($state.current.title);
	});

	// State not found
	$rootScope.$on('$stateNotFound', function(event, unfoundState, fromState, fromParams) {
		//$rootScope.loading = false;
		console.log(unfoundState.to);
		// "lazy.state"
		console.log(unfoundState.toParams);
		// {a:1, b:2}
		console.log(unfoundState.options);
		// {inherit:false} + default options
	});

	// save settings to local storage
	if (angular.isDefined($localStorage.layout)) {
		$scope.app.layout = $localStorage.layout;
	} else {
		$localStorage.layout = $scope.app.layout;
	}
	$scope.$watch('app.layout', function() {
		// save to local storage
		$localStorage.layout = $scope.app.layout;
	}, true);

	//global function to scroll page up
	$scope.toTheTop = function() {

		$document.scrollTopAnimated(0, 600);

	};

	// angular translate
	// ----------------------

	$scope.language = {
		// Handles language dropdown
		listIsOpen : false,
		// list of available languages
		available : $rootScope.languages,
		// display always the current ui language
		init : function() {
			if (angular.isDefined($stateParams.language)) {
				$scope.language.selected = $scope.language.available[$stateParams.language];
				$localStorage.language = $stateParams.language;
			} else {
				var proposedLanguage = $translate.proposedLanguage() || $translate.use();
				var preferredLanguage = $translate.preferredLanguage();
				for (var lang in $scope.language.available) {
					if (window.location.hash.endsWith('/'+lang)) {
						proposedLanguage = lang;
					}
				}
				// we know we have set a preferred one in app.config
				$scope.language.selected = $scope.language.available[(proposedLanguage || preferredLanguage)];
				$localStorage.language = (proposedLanguage || preferredLanguage);
			}
		},
		set : function(localeId, ev) {
			$translate.use(localeId);
			$scope.language.selected = $scope.language.available[localeId];
			$scope.language.listIsOpen = !$scope.language.listIsOpen;
			$localStorage.language = localeId;
			$rootScope.$broadcast('languageChange', [localeId]);
		}
	};
    console.warn($scope.language);

	$scope.language.init();

	// Function that find the exact height and width of the viewport in a cross-browser way
	var viewport = function() {
		var e = window, a = 'inner';
		if (!('innerWidth' in window)) {
			a = 'client';
			e = document.documentElement || document.body;
		}
		return {
			width : e[a + 'Width'],
			height : e[a + 'Height']
		};
	};

	// function that adds information in a scope of the height and width of the page
	$scope.getWindowDimensions = function() {
		return {
			'h' : viewport().height,
			'w' : viewport().width
		};
	};

	// Detect when window is resized and set some variables
	$scope.$watch($scope.getWindowDimensions, function(newValue, oldValue) {
		$scope.windowHeight = newValue.h;
		$scope.windowWidth = newValue.w;
		
		if (newValue.w >= 992) {
			$scope.isLargeDevice = true;
		} else {
			$scope.isLargeDevice = false;
		}
		if (newValue.w < 992) {
			$scope.isSmallDevice = true;
		} else {
			$scope.isSmallDevice = false;
		}
		if (newValue.w <= 768) {
			$scope.isMobileDevice = true;
		} else {
			$scope.isMobileDevice = false;
		}
	}, true);

	// Apply on resize
	$win.on('resize', function() {
		
		$scope.$apply();
		if ($scope.isLargeDevice) {
			$('#app .main-content').css({
				position : 'relative',
				top : 'auto',
				width: 'auto'
			});
			$('footer').show();
		}
	});
    
}]);
