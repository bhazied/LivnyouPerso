/** 
  * declare 'livn-you' module with dependencies
*/
'use strict';
angular.module('livn-you', [
	'ngAnimate',
	'ngCookies',
	'ngStorage',
	'ngSanitize',
	'ngResource',
	'ngTouch',
	'ngTable',
	'ui.router',
	'ui.bootstrap',
	'oc.lazyLoad',
	'cfp.loadingBar',
	'ncy-angular-breadcrumb',
	'duScroll',
	'pascalprecht.translate',
	'angular-bind-html-compile',
    'slugifier',
    'toaster',
	'colorpicker.module',
	'com.2fdevs.videogular',
	'com.2fdevs.videogular.plugins.controls',
	'com.2fdevs.videogular.plugins.buffering',
	'com.2fdevs.videogular.plugins.overlayplay',
	'com.2fdevs.videogular.plugins.poster',
	'at.multirange-slider'
]);

var app = angular.module('livnYouApp', ['livn-you', 'ngSanitize', 'ngCsv']);

var languages = {
    'en' : 'English',
    'fr' : 'Français',/*
    'es' : 'Español',
    'it' : 'Italiano',
    'de' : 'Deutsch'*/
};

app.run(['$rootScope', '$state', '$stateParams', '$localStorage', '$timeout',
    function ($rootScope, $state, $stateParams, $localStorage, $timeout) {

    $rootScope.languages = languages;

    // Attach Fastclick for eliminating the 300ms delay between a physical tap and the firing of a click event on mobile browsers
    FastClick.attach(document.body);

    // Set some reference to access them from any scope
    $rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;

    // GLOBAL APP SCOPE
    // set below basic information
    $rootScope.app = {
        name: 'LivnYou', // name of your project
        description: 'LivnYou', // brief description
        keywords: 'LivnYou, santé, measurement', // brief description
        author: 'ContinuousNet', // author's name or company name
        version: '1.0', // current version
        year: ((new Date()).getFullYear()), // automatic current year (for copyright information)
        isMobile: (function () {// true if the browser is a mobile device
            var check = false;
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                check = true;
            };
            return check;
        })(),
        apiURL: '/api/', // rest api url
        apiVersion: 'v1/', // rest version url
        thumbURL: '/thumb?image=', // rest version url
        layout: {
            isNavbarFixed: true, //true if you want to initialize the template with fixed header
            isSidebarFixed: true, // true if you want to initialize the template with fixed sidebar
            isSidebarClosed: false, // true if you want to initialize the template with closed sidebar
            isFooterFixed: false, // true if you want to initialize the template with fixed footer
            theme: 'theme-1', // indicate the theme chosen for your project
            logo: '/assets/images/logo.png' // relative path of the project logo
        }
    };

    $rootScope.seo = {
        meta_title: '',
        meta_keywords: '',
        meta_description: ''
    };

    $rootScope.pageTitle = function() {
        return ($rootScope.seo.meta_title || $rootScope.app.name);
    };

    $rootScope.pageDescription = function() {
        return ($rootScope.seo.meta_description || $rootScope.app.description);
    };

    $rootScope.pageKeywords = function() {
        return ($rootScope.seo.meta_keywords || $rootScope.app.keywords);
    };

    $rootScope.createTree = function (items, parentField, labelField, parentId, level) {
        var tree = [];
        for (var i in items) {
            var addToTree = false;
            if (parentId == null && items[i][parentField] == null) {
                addToTree = true;
            } else if (items[i][parentField] != null) {
                if (items[i][parentField].id == parentId) {
                    addToTree = true;
                }
            }
            if (addToTree) {
                if (level > 0) {
                    var newLabel = '╚';
                    newLabel += '═'.repeat(level);
                    newLabel += ' '+items[i][labelField];
                    items[i][labelField] = newLabel;
                }
                tree.push(items[i]);
                var children = $rootScope.createTree(items, parentField, labelField, items[i].id, level+1);
                for (var j in children) {
                    tree.push(children[j]);
                }
            }
        }
        return tree;
    };

    $rootScope.checkStatePermission = function (state) {
        if ($rootScope.currentUser.roles.join('').indexOf('ADM') > -1) {
            return true;
        } else {
            if (
                state.indexOf('supplierproduct') > -1 ||
                state.indexOf('supplier') > -1 ||
                state.indexOf('buyer') > -1 ||
                state.indexOf('tender') > -1 ||
                state.indexOf('bid') > -1
            ) {
                return true;
            } else {
                return false;
            }
        }
    };

    if (angular.isDefined($localStorage.user)) {
        $rootScope.user = $rootScope.currentUser = $localStorage.user;
    } else {
        $rootScope.user = $rootScope.currentUser = {
            firstName: 'User',
            job: 'Webmaster',
            picture: 'app/img/user/02.jpg',
            roles: []
        };
    }
    
}]);

// translate config
app.config(['$translateProvider',
function ($translateProvider) {


    // prefix and suffix information  is required to specify a pattern
    // You can simply use the static-files loader with this pattern:
    $translateProvider.useStaticFilesLoader({
        prefix: '/assets/i18n/',
        suffix: '.json'
    });

    var currentLanguage = null;
    if (typeof localStorage['ngStorage-language'] != 'undefined') {
        currentLanguage = JSON.parse(localStorage['ngStorage-language']);
    }
    for (var languageKey in languages) {
        if (currentLanguage == null) {
            currentLanguage = languageKey;
        }
        if (window.location.hash.endsWith('/' + languageKey)) {
            currentLanguage = languageKey;
        }
    }
    localStorage['NG_TRANSLATE_LANG_KEY'] = currentLanguage;
    localStorage['ngStorage-language'] = '"'+currentLanguage+'"';

    // Since you've now registered more then one translation table, angular-translate has to know which one to use.
    // This is where preferredLanguage(langKey) comes in.
    $translateProvider.preferredLanguage(currentLanguage);

    // Store the language in the local storage
    $translateProvider.useLocalStorage();
    
    // Enable sanitize
    $translateProvider.useSanitizeValueStrategy('escape'); // sanitize

}]);

// Angular-Loading-Bar
// configuration
app.config(['cfpLoadingBarProvider',
function (cfpLoadingBarProvider) {
    cfpLoadingBarProvider.includeBar = true;
    cfpLoadingBarProvider.includeSpinner = false;
}]);

//  This binding is brought you by [[ ]] interpolation symbols. 
app.config(function($interpolateProvider) {
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
});

// Angular-Breadcrumb
// configuration
app.config(function($breadcrumbProvider) {
    $breadcrumbProvider.setOptions({
        templateUrl: '/assets/views/partials/breadcrumb.html'
    });
});

// ngTable Filter
// configuration
app.config(function(ngTableFilterConfigProvider) {

    ngTableFilterConfigProvider.setConfig({
        aliasUrls: {
            'checkboxes': '/assets/views/partials/checkboxes.html'
        }
    });

});

if (!String.prototype.endsWith) {

    String.prototype.endsWith = function(searchString, position) {
        var subjectString = this.toString();
        if (typeof position !== 'number' || !isFinite(position) || Math.floor(position) !== position || position > subjectString.length) {
            position = subjectString.length;
        }
        position -= searchString.length;
        var lastIndex = subjectString.indexOf(searchString, position);
        return lastIndex !== -1 && lastIndex === position;
    };

}

'use strict';

/**
 * Config constant
 */
app.constant('APP_MEDIAQUERY', {
    'desktopXL': 1200,
    'desktop': 992,
    'tablet': 768,
    'mobile': 480
});

app.constant('DIAL_COUNTRIES', '/assets/js/resources/country-dial-code.json');
app.constant('JS_REQUIRES', {   
    //*** Scripts
    scripts: {
        //*** Javascript Plugins
        'modernizr': ['/assets/bower_components/components-modernizr/modernizr.js'],
        'moment': ['/assets/bower_components/moment/min/moment.min.js'],
        'spin': '/assets/bower_components/spin.js/spin.js',

        //*** jQuery Plugins
        'perfect-scrollbar-plugin': ['/assets/bower_components/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js', '/assets/bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css'],
        'ladda': ['/assets/bower_components/ladda/dist/ladda.min.js', '/assets/bower_components/ladda/dist/ladda-themeless.min.css'],
        'sweet-alert': ['/assets/bower_components/sweetalert/lib/sweet-alert.min.js', '/assets/bower_components/sweetalert/lib/sweet-alert.css'],
        'chartjs': '/assets/bower_components/chartjs/Chart.min.js',
        'jquery-sparkline': '/assets/bower_components/jquery.sparkline.build/dist/jquery.sparkline.min.js',
        'ckeditor-plugin': ['/assets/bower_components/ckeditor/ckeditor.js'],
        'jquery-nestable-plugin': ['/assets/bower_components/jquery-nestable/jquery.nestable.js'],
        'touchspin-plugin': ['/assets/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js', '/assets/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css'],

        //*** Controllers
        'dashboardCtrl': '/assets/js/controllers/dashboardCtrl.js',
        'signInCtrl': '/assets/js/controllers/signInCtrl.js',
        'iconsCtrl': '/assets/js/controllers/iconsCtrl.js',
        'vAccordionCtrl': '/assets/js/controllers/vAccordionCtrl.js',
        'ckeditorCtrl': '/assets/js/controllers/ckeditorCtrl.js',
        'laddaCtrl': '/assets/js/controllers/laddaCtrl.js',
        'ngTableCtrl': '/assets/js/controllers/ngTableCtrl.js',
        'cropCtrl': '/assets/js/controllers/cropCtrl.js',
        'asideCtrl': '/assets/js/controllers/asideCtrl.js',
        'toasterCtrl': '/assets/js/controllers/toasterCtrl.js',
        'sweetAlertCtrl': '/assets/js/controllers/sweetAlertCtrl.js',
        'mapsCtrl': '/assets/js/controllers/mapsCtrl.js',
        'chartsCtrl': '/assets/js/controllers/chartsCtrl.js',
        'calendarCtrl': '/assets/js/controllers/calendarCtrl.js',
        'nestableCtrl': '/assets/js/controllers/nestableCtrl.js',
        'validationCtrl': ['/assets/js/controllers/validationCtrl.js'],
        'userCtrl': ['/assets/js/controllers/userCtrl.js'],
        'selectCtrl': '/assets/js/controllers/selectCtrl.js',
        'wizardCtrl': '/assets/js/controllers/wizardCtrl.js',
        'uploadCtrl': '/assets/js/controllers/uploadCtrl.js',
        'treeCtrl': '/assets/js/controllers/treeCtrl.js',
        'inboxCtrl': '/assets/js/controllers/inboxCtrl.js',
        'xeditableCtrl': '/assets/js/controllers/xeditableCtrl.js',
        'chatCtrl': '/assets/js/controllers/chatCtrl.js',
        'dynamicTableCtrl': '/assets/js/controllers/dynamicTableCtrl.js',
        'NotificationIconsCtrl': '/assets/js/controllers/notificationIconsCtrl.js',
        
        //*** Filters
        'htmlToPlaintext': '/assets/js/filters/htmlToPlaintext.js'
    },
    //*** angularJS Modules
    modules: [{
        name: 'angularMoment',
        files: ['/assets/bower_components/angular-moment/angular-moment.min.js']
    }, {
        name: 'toaster',
        files: ['/assets/bower_components/AngularJS-Toaster/toaster.js', '/assets/bower_components/AngularJS-Toaster/toaster.css']
    }, {
        name: 'angularBootstrapNavTree',
        files: ['/assets/bower_components/angular-bootstrap-nav-tree/dist/abn_tree_directive.js', '/assets/bower_components/angular-bootstrap-nav-tree/dist/abn_tree.css']
    }, {
        name: 'angular-ladda',
        files: ['/assets/bower_components/angular-ladda/dist/angular-ladda.min.js']
    }, {
        name: 'ui.select',
        files: ['/assets/bower_components/angular-ui-select/dist/select.min.js', '/assets/bower_components/angular-ui-select/dist/select.min.css', '/assets/bower_components/select2/dist/css/select2.min.css', '/assets/bower_components/select2-bootstrap-css/select2-bootstrap.min.css', '/assets/bower_components/selectize/dist/css/selectize.bootstrap3.css']
    }, {
        name: 'ui.mask',
        files: ['/assets/bower_components/angular-ui-utils/mask.min.js']
    }, {
        name: 'ngImgCrop',
        files: ['/assets/bower_components/ngImgCrop/compile/minified/ng-img-crop.js', '/assets/bower_components/ngImgCrop/compile/minified/ng-img-crop.css']
    }, {
        name: 'angularFileUpload',
        files: ['/assets/bower_components/angular-file-upload/angular-file-upload.min.js']
    }, {
        name: 'ngAside',
        files: ['/assets/bower_components/angular-aside/dist/js/angular-aside.min.js', '/assets/bower_components/angular-aside/dist/css/angular-aside.min.css']
    }, {
        name: 'truncate',
        files: ['/assets/bower_components/angular-truncate/src/truncate.js']
    }, {
        name: 'oitozero.ngSweetAlert',
        files: ['/assets/bower_components/angular-sweetalert-promised/SweetAlert.min.js']
    }, {
        name: 'monospaced.elastic',
        files: ['/assets/bower_components/angular-elastic/elastic.js']
    }, {
        name: 'ngMap',
        files: ['/assets/bower_components/ngmap/build/scripts/ng-map.min.js']
    }, {
        name: 'tc.chartjs',
        files: ['/assets/bower_components/tc-angular-chartjs/dist/tc-angular-chartjs.min.js']
    }, {
        name: 'flow',
        files: ['/assets/bower_components/ng-flow/dist/ng-flow-standalone.min.js']
    }, {
        name: 'uiSwitch',
        files: ['/assets/bower_components/angular-ui-switch/angular-ui-switch.min.js', '/assets/bower_components/angular-ui-switch/angular-ui-switch.min.css']
    }, {
        name: 'ckeditor',
        files: ['/assets/bower_components/angular-ckeditor/angular-ckeditor.min.js']
    }, {
        name: 'mwl.calendar',
        files: ['/assets/bower_components/angular-bootstrap-calendar/dist/js/angular-bootstrap-calendar-tpls.js', '/assets/bower_components/angular-bootstrap-calendar/dist/css/angular-bootstrap-calendar.min.css', '/assets/js/config/config-calendar.js']
    }, {
        name: 'ng-nestable',
        files: ['/assets/bower_components/ng-nestable/src/angular-nestable.js']
    }, {
        name: 'vAccordion',
        files: ['/assets/bower_components/v-accordion/dist/v-accordion.min.js', '/assets/bower_components/v-accordion/dist/v-accordion.min.css']
    }, {
        name: 'xeditable',
        files: ['/assets/bower_components/angular-xeditable/dist/js/xeditable.min.js', '/assets/bower_components/angular-xeditable/dist/css/xeditable.css', '/assets/js/config/config-xeditable.js']
    }, {
        name: 'checklist-model',
        files: ['/assets/bower_components/checklist-model/checklist-model.js']
    }, {
        name: 'angular-notification-icons',
        files: ['/assets/bower_components/angular-notification-icons/dist/angular-notification-icons.min.js', '/assets/bower_components/angular-notification-icons/dist/angular-notification-icons.min.css']
    },{
        name: 'angular-slider',
        files: ['/assets/bower_components/angularjs-slider/dist/rzslider.min.js', '/assets/bower_components/angularjs-slider/dist/rzslider.min.css']
    },{
        name: 'tree-grid-directive',
        files: ['/assets/bower_components/angular-bootstrap-nav-tree/dist/abn_tree_directive.js', '/assets/bower_components/angular-bootstrap-nav-tree/dist/abn_tree.css']
    },{
        name: 'ng-csv',
        files: ['/assets/bower_components/ng-csv/src/ng-csv.js']
    }]
});

app.constant('APP_JS_REQUIRES', {
    //*** Scripts
    scripts: {
        //*** Controllers
        'LoginCtrl': '/bundles/livnyou/js/components/Auth/LoginCtrl.js',
        'LockScreenCtrl': '/bundles/livnyou/js/components/Auth/LockScreenCtrl.js',
        'RegisterCtrl': '/bundles/livnyou/js/components/Auth/RegisterCtrl.js',
        'EmailConfirmCtrl': '/bundles/livnyou/js/components/Auth/EmailConfirmCtrl.js',
        'ResetPasswordCtrl': '/bundles/livnyou/js/components/Auth/ResetPasswordCtrl.js',
        'ResetCtrl': '/bundles/livnyou/js/components/Auth/ResetCtrl.js',
        'ChangePasswordCtrl': '/bundles/livnyou/js/components/Auth/ChangePasswordCtrl.js',
        'ProfileCtrl': '/bundles/livnyou/js/components/Auth/ProfileCtrl.js',
        'DashboardCtrl': '/bundles/livnyou/js/components/Main/DashboardCtrl.js',
        'ReportingCtrl': '/bundles/livnyou/js/components/Reporting/ReportingCtrl.js',
        'CountriesCtrl': '/bundles/livnyou/js/components/Country/CountriesCtrl.js',
        'CountryFormCtrl': '/bundles/livnyou/js/components/Country/CountryFormCtrl.js',
        'CountryCtrl': '/bundles/livnyou/js/components/Country/CountryCtrl.js',
        'GroupsCtrl': '/bundles/livnyou/js/components/Group/GroupsCtrl.js',
        'GroupFormCtrl': '/bundles/livnyou/js/components/Group/GroupFormCtrl.js',
        'GroupCtrl': '/bundles/livnyou/js/components/Group/GroupCtrl.js',
        'LanguagesCtrl': '/bundles/livnyou/js/components/Language/LanguagesCtrl.js',
        'LanguageFormCtrl': '/bundles/livnyou/js/components/Language/LanguageFormCtrl.js',
        'LanguageCtrl': '/bundles/livnyou/js/components/Language/LanguageCtrl.js',
        'LogsCtrl': '/bundles/livnyou/js/components/Log/LogsCtrl.js',
        'LogFormCtrl': '/bundles/livnyou/js/components/Log/LogFormCtrl.js',
        'LogCtrl': '/bundles/livnyou/js/components/Log/LogCtrl.js',
        'MeasurementsCtrl': '/bundles/livnyou/js/components/Measurement/MeasurementsCtrl.js',
        'MeasurementFormCtrl': '/bundles/livnyou/js/components/Measurement/MeasurementFormCtrl.js',
        'MeasurementCtrl': '/bundles/livnyou/js/components/Measurement/MeasurementCtrl.js',
        'PathologiesCtrl': '/bundles/livnyou/js/components/Pathology/PathologiesCtrl.js',
        'PathologyFormCtrl': '/bundles/livnyou/js/components/Pathology/PathologyFormCtrl.js',
        'PathologyCtrl': '/bundles/livnyou/js/components/Pathology/PathologyCtrl.js',
        'PhysicalActivitiesCtrl': '/bundles/livnyou/js/components/PhysicalActivity/PhysicalActivitiesCtrl.js',
        'PhysicalActivityFormCtrl': '/bundles/livnyou/js/components/PhysicalActivity/PhysicalActivityFormCtrl.js',
        'PhysicalActivityCtrl': '/bundles/livnyou/js/components/PhysicalActivity/PhysicalActivityCtrl.js',
        'SessionsCtrl': '/bundles/livnyou/js/components/Session/SessionsCtrl.js',
        'SessionFormCtrl': '/bundles/livnyou/js/components/Session/SessionFormCtrl.js',
        'SessionCtrl': '/bundles/livnyou/js/components/Session/SessionCtrl.js',
        'TemplatesCtrl': '/bundles/livnyou/js/components/Template/TemplatesCtrl.js',
        'TemplateFormCtrl': '/bundles/livnyou/js/components/Template/TemplateFormCtrl.js',
        'TemplateCtrl': '/bundles/livnyou/js/components/Template/TemplateCtrl.js',
        'TemplateAssignCtrl': '/bundles/livnyou/js/components/Template/TemplateAssignCtrl.js',
        'TranslationCountriesCtrl': '/bundles/livnyou/js/components/TranslationCountry/TranslationCountriesCtrl.js',
        'TranslationCountryFormCtrl': '/bundles/livnyou/js/components/TranslationCountry/TranslationCountryFormCtrl.js',
        'TranslationCountryCtrl': '/bundles/livnyou/js/components/TranslationCountry/TranslationCountryCtrl.js',
        'TranslationPathologiesCtrl': '/bundles/livnyou/js/components/TranslationPathology/TranslationPathologiesCtrl.js',
        'TranslationPathologyFormCtrl': '/bundles/livnyou/js/components/TranslationPathology/TranslationPathologyFormCtrl.js',
        'TranslationPathologyCtrl': '/bundles/livnyou/js/components/TranslationPathology/TranslationPathologyCtrl.js',
        'TranslationPhysicalActivitiesCtrl': '/bundles/livnyou/js/components/TranslationPhysicalActivity/TranslationPhysicalActivitiesCtrl.js',
        'TranslationPhysicalActivityFormCtrl': '/bundles/livnyou/js/components/TranslationPhysicalActivity/TranslationPhysicalActivityFormCtrl.js',
        'TranslationPhysicalActivityCtrl': '/bundles/livnyou/js/components/TranslationPhysicalActivity/TranslationPhysicalActivityCtrl.js',
        'UsersCtrl': '/bundles/livnyou/js/components/User/UsersCtrl.js',
        'UserFormCtrl': '/bundles/livnyou/js/components/User/UserFormCtrl.js',
        'UserCtrl': '/bundles/livnyou/js/components/User/UserCtrl.js'
    },
    modules: [{
        name: 'LoginService',
        files: ['/bundles/livnyou/js/components/Auth/LoginService.js']
    },{
        name: 'RegisterService',
        files: ['/bundles/livnyou/js/components/Auth/RegisterService.js']
    },{
        name: 'ResetPasswordService',
        files: ['/bundles/livnyou/js/components/Auth/ResetPasswordService.js']
    },{
        name: 'ProfileService',
        files: ['/bundles/livnyou/js/components/Auth/ProfileService.js']
    },{
        name: 'DashboardService',
        files: ['/bundles/livnyou/js/components/Main/DashboardService.js']
    },{
        name: 'ReportingService',
        files: ['/bundles/livnyou/js/components/Reporting/ReportingService.js']
    },{
        name: 'countryService',
        files: ['/bundles/livnyou/js/components/Country/CountryService.js']
    },{
        name: 'groupService',
        files: ['/bundles/livnyou/js/components/Group/GroupService.js']
    },{
        name: 'languageService',
        files: ['/bundles/livnyou/js/components/Language/LanguageService.js']
    },{
        name: 'logService',
        files: ['/bundles/livnyou/js/components/Log/LogService.js']
    },{
        name: 'measurementService',
        files: ['/bundles/livnyou/js/components/Measurement/MeasurementService.js']
    },{
        name: 'pathologyService',
        files: ['/bundles/livnyou/js/components/Pathology/PathologyService.js']
    },{
        name: 'physicalActivityService',
        files: ['/bundles/livnyou/js/components/PhysicalActivity/PhysicalActivityService.js']
    },{
        name: 'sessionService',
        files: ['/bundles/livnyou/js/components/Session/SessionService.js']
    },{
        name: 'templateService',
        files: ['/bundles/livnyou/js/components/Template/TemplateService.js']
    },{
        name: 'TemplateAssignService',
        files: ['/bundles/livnyou/js/components/Template/TemplateAssignService.js']
    },{
        name: 'translationCountryService',
        files: ['/bundles/livnyou/js/components/TranslationCountry/TranslationCountryService.js']
    },{
        name: 'translationPathologyService',
        files: ['/bundles/livnyou/js/components/TranslationPathology/TranslationPathologyService.js']
    },{
        name: 'translationPhysicalActivityService',
        files: ['/bundles/livnyou/js/components/TranslationPhysicalActivity/TranslationPhysicalActivityService.js']
    },{
        name: 'userService',
        files: ['/bundles/livnyou/js/components/User/UserService.js']
    }]
});

'use strict';

app.factory('httpRequestInterceptor', ['$q', '$localStorage', '$location', '$filter', '$timeout', 'toaster',
function ($q, $localStorage, $location, $filter, $timeout, toaster) {
    return {
        request: function (config) {
            if ($localStorage.access_token) {
                config.headers['Authorization'] = 'Bearer ' + $localStorage.access_token ;
            }
            return config;
        },
        responseError: function (response) {
            if ( response.status === 401) {
                delete $localStorage.access_token;
                $location.path('/login/signin');
            } else if (response.status === 403) {
                toaster.pop('warning', $filter('translate')('content.common.WARNING'), $filter('translate')('login.ACCESSDENEID'));
                $timeout(function(){
                    $location.path('/app/dashboard');
                }, 1000);
            }
            return $q.reject(response);
        }
    };
}]);

// Generates a resolve object previously configured in constant.JS_REQUIRES or in constant.APP_JS_REQUIRES (config.constant.js)
function loadSequence() {
    var _args = arguments;
    return {
        deps: ['$ocLazyLoad', '$q', 'JS_REQUIRES', 'APP_JS_REQUIRES',
        function ($ocLL, $q, jsRequires, appJsRequires) {
            var promise = $q.when(1);
            for (var i = 0, len = _args.length; i < len; i++) {
                promise = promiseThen(_args[i]);
            }
            return promise;

            function promiseThen(_arg) {
                if (typeof _arg == 'function')
                    return promise.then(_arg);
                else
                    return promise.then(function () {
                        var nowLoad = requiredData(_arg);
                        if (!nowLoad)
                            return $.error('Route resolve: Bad resource name [' + _arg + ']');
                        return $ocLL.load(nowLoad);
                    });
            }

            function requiredData(name) {
                if (jsRequires.modules)
                    for (var m in jsRequires.modules)
                        if (jsRequires.modules[m].name && jsRequires.modules[m].name === name)
                            return jsRequires.modules[m];
                if (appJsRequires.modules)
                    for (var m in appJsRequires.modules)
                        if (appJsRequires.modules[m].name && appJsRequires.modules[m].name === name)
                            return appJsRequires.modules[m];
                return (jsRequires.scripts && jsRequires.scripts[name]) || (appJsRequires.scripts && appJsRequires.scripts[name]);
            }
        }]
    };
}

/**
 * Config for the router
 */
app.config(['$stateProvider', '$httpProvider', '$urlRouterProvider', '$controllerProvider', '$compileProvider', '$filterProvider', '$provide', '$ocLazyLoadProvider', 'JS_REQUIRES', 'APP_JS_REQUIRES',
function ($stateProvider, $httpProvider, $urlRouterProvider, $controllerProvider, $compileProvider, $filterProvider, $provide, $ocLazyLoadProvider, jsRequires, appJsRequires) {

    app.controller = $controllerProvider.register;
    app.directive = $compileProvider.directive;
    app.filter = $filterProvider.register;
    app.factory = $provide.factory;
    app.service = $provide.service;
    app.constant = $provide.constant;
    app.value = $provide.value;
    
    $httpProvider.interceptors.push('httpRequestInterceptor');

    // LAZY MODULES

    $ocLazyLoadProvider.config({
        debug: false,
        events: true,
        modules: jsRequires.modules.concat(appJsRequires)
    });


    // APPLICATION ROUTES
    // -----------------------------------
    // For any unmatched url, redirect to /auth/login
    $urlRouterProvider.otherwise('/auth/login');
    //
    // Set up the states
    $stateProvider.state('app', {
        url: '/app',
        templateUrl: '/assets/views/app.html',
        resolve: loadSequence('modernizr', 'moment', 'angularMoment', 'uiSwitch', 'perfect-scrollbar-plugin', 'toaster', 'ngAside', 'vAccordion', 'sweet-alert', 'chartjs', 'tc.chartjs', 'oitozero.ngSweetAlert', 'truncate', 'htmlToPlaintext', 'angular-notification-icons'),
        abstract: true
    }).state('error', {
        url: '/error',
        template: '<div ui-view class="fade-in-up"></div>'
    }).state('error.404', {
        url: '/404',
        templateUrl: '/assets/views/utility_404.html',
    }).state('error.500', {
        url: '/500',
        templateUrl: '/assets/views/utility_500.html',
    });
    
}]);

/**
 * Config for the app router
 */
app.config(['$stateProvider',
function ($stateProvider) {

    $stateProvider.state('auth', {
        url: '/auth',
        template: '<div ui-view class="fade-in-right-big smooth"></div>',
        title: 'sidebar.nav.auth.MAIN',
        ncyBreadcrumb: {
            label: 'sidebar.nav.auth.MAIN'
        }
    }).state('auth.login', {
        url: '/login',
        templateUrl: '/bundles/livnyou/js/components/Auth/login.html',
        title: 'content.list.LOGIN',
        ncyBreadcrumb: {
            label: 'content.list.LOGIN'
        },
        data: {
            appClasses: 'bg-white usersession',
            contentClasses: 'full-height'
        },
        resolve: loadSequence('LoginCtrl', 'LoginService')
    }).state('auth.register', {
        url: '/register',
        templateUrl: '/bundles/livnyou/js/components/Auth/register.html',
        title: 'content.list.REGISTER',
        ncyBreadcrumb: {
            label: 'content.list.REGISTER'
        },
        data: {
            appClasses: 'bg-white usersession',
            contentClasses: 'full-height'
        },
        resolve: loadSequence('sweet-alert', 'oitozero.ngSweetAlert', 'RegisterCtrl', 'RegisterService')
    }).state('auth.resetpassword', {
        url: '/reset-password',
        templateUrl: '/bundles/livnyou/js/components/Auth/reset_password.html',
        title: 'content.list.RESETPAWSSWORD',
        ncyBreadcrumb: {
            label: 'content.list.RESETPAWSSWORD'
        },
        data: {
            appClasses: 'bg-white usersession',
            contentClasses: 'full-height'
        },
        resolve: loadSequence('ResetPasswordCtrl', 'ResetPasswordService')
    }).state('auth.emailconfirm', {
        url: '/email-confirm/:token/:language',
        templateUrl: '/bundles/livnyou/js/components/Auth/email_confirm.html',
        title: 'content.list.EMAILCONFIRM',
        ncyBreadcrumb: {
            label: 'content.list.EMAILCONFIRM'
        },
        data: {
            appClasses: 'bg-white usersession',
            contentClasses: 'full-height'
        },
        resolve: loadSequence('EmailConfirmCtrl', 'RegisterService')
    }).state('auth.reset', {
        url: '/reset/:token/:language',
        templateUrl: '/bundles/livnyou/js/components/Auth/reset.html',
        title: 'content.list.RESET',
        ncyBreadcrumb: {
            label: 'content.list.RESET'
        },
        data: {
            appClasses: 'bg-white usersession',
            contentClasses: 'full-height'
        },
        resolve: loadSequence('ResetCtrl', 'ResetPasswordService')
    }).state('auth.lockscreen', {
        url: '/lock-screen',
        templateUrl: '/bundles/livnyou/js/components/Auth/lock_screen.html',
        title: 'content.list.LOCKSCREEN',
        ncyBreadcrumb: {
            label: 'content.list.LOCKSCREEN'
        },
        data: {
            appClasses: 'bg-white usersession',
            contentClasses: 'full-height'
        },
        resolve: loadSequence('LockScreenCtrl', 'LoginService')
    }).state('app.profile', {
        url: '/profile',
        templateUrl: '/bundles/livnyou/js/components/Auth/profile.html',
        title: 'topbar.user.PROFILE',
        ncyBreadcrumb: {
            label: 'topbar.user.PROFILE'
        },
        resolve: loadSequence('ProfileCtrl', 'ProfileService', 'countryService')
    }).state('app.changepassword', {
        url: '/change-password',
        templateUrl: '/bundles/livnyou/js/components/Auth/change_password.html',
        title: 'topbar.user.CHANGEPASSWORD',
        ncyBreadcrumb: {
            label: 'topbar.user.CHANGEPASSWORD'
        },
        resolve: loadSequence('ChangePasswordCtrl', 'ProfileService')
    }).state('app.dashboard', {
        url: '/dashboard',
        templateUrl: '/bundles/livnyou/js/components/Main/dashboard.html',
        title: 'content.list.DASHBOARD',
        ncyBreadcrumb: {
            label: 'content.list.DASHBOARD'
        },
        resolve: loadSequence('DashboardCtrl', 'DashboardService')
    }).state('app.reporting', {
        url: '/reporting',
        templateUrl: '/bundles/livnyou/js/components/Reporting/reporting.html',
        title: 'content.list.REPORTING',
        ncyBreadcrumb: {
            label: 'content.list.REPORTING'
        },
        resolve: loadSequence('ReportingCtrl', 'ReportingService')
    }).state('app.systemsettings', {
        url: '/system-settings',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'sidebar.nav.systemsettings.MAIN',
        ncyBreadcrumb: {
            label: 'sidebar.nav.systemsettings.MAIN'
        }
    }).state('app.systemsettings.pathologies', {
        url: '/pathologies',
        templateUrl: '/bundles/livnyou/js/components/Pathology/pathologies.html',
        title: 'content.list.PATHOLOGIES',
        ncyBreadcrumb: {
            label: 'content.list.PATHOLOGIES'
        },
        params: {
            'pathologiesIsFiltersVisible': null,
            'pathologiesPage': null,
            'pathologiesCount': null,
            'pathologiesSorting': null,
            'pathologiesFilter': null
        },
        resolve: loadSequence('PathologiesCtrl', 'pathologyService', 'userService')
    }).state('app.systemsettings.pathologiesnew', {
        url: '/pathologies/new',
        templateUrl: '/bundles/livnyou/js/components/Pathology/pathology_form.html',
        title: 'content.list.NEWPATHOLOGY',
        ncyBreadcrumb: {
            label: 'content.list.NEWPATHOLOGY'
        },
        params: {
        },
        resolve: loadSequence('PathologyFormCtrl', 'pathologyService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.systemsettings.pathologiesedit', {
        url: '/pathologies/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/Pathology/pathology_form.html',
        title: 'content.list.EDITPATHOLOGY',
        ncyBreadcrumb: {
            label: 'content.list.EDITPATHOLOGY'
        },
        resolve: loadSequence('PathologyFormCtrl', 'pathologyService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.systemsettings.pathologiesdetails', {
        url: '/pathologies/details/:id',
        templateUrl: '/bundles/livnyou/js/components/Pathology/pathology.html',
        ncyBreadcrumb: {
            label: 'content.list.PATHOLOGYDETAILS'
        },
        resolve: loadSequence('PathologyCtrl', 'pathologyService')
    }).state('app.systemsettings.physicalactivities', {
        url: '/physical-activities',
        templateUrl: '/bundles/livnyou/js/components/PhysicalActivity/physical_activities.html',
        title: 'content.list.PHYSICALACTIVITIES',
        ncyBreadcrumb: {
            label: 'content.list.PHYSICALACTIVITIES'
        },
        params: {
            'physicalActivitiesIsFiltersVisible': null,
            'physicalActivitiesPage': null,
            'physicalActivitiesCount': null,
            'physicalActivitiesSorting': null,
            'physicalActivitiesFilter': null
        },
        resolve: loadSequence('PhysicalActivitiesCtrl', 'physicalActivityService', 'userService')
    }).state('app.systemsettings.physicalactivitiesnew', {
        url: '/physical-activities/new',
        templateUrl: '/bundles/livnyou/js/components/PhysicalActivity/physical_activity_form.html',
        title: 'content.list.NEWPHYSICALACTIVITY',
        ncyBreadcrumb: {
            label: 'content.list.NEWPHYSICALACTIVITY'
        },
        params: {
        },
        resolve: loadSequence('PhysicalActivityFormCtrl', 'physicalActivityService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.systemsettings.physicalactivitiesedit', {
        url: '/physical-activities/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/PhysicalActivity/physical_activity_form.html',
        title: 'content.list.EDITPHYSICALACTIVITY',
        ncyBreadcrumb: {
            label: 'content.list.EDITPHYSICALACTIVITY'
        },
        resolve: loadSequence('PhysicalActivityFormCtrl', 'physicalActivityService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.systemsettings.physicalactivitiesdetails', {
        url: '/physical-activities/details/:id',
        templateUrl: '/bundles/livnyou/js/components/PhysicalActivity/physical_activity.html',
        ncyBreadcrumb: {
            label: 'content.list.PHYSICALACTIVITYDETAILS'
        },
        resolve: loadSequence('PhysicalActivityCtrl', 'physicalActivityService')
    }).state('app.systemsettings.countries', {
        url: '/countries',
        templateUrl: '/bundles/livnyou/js/components/Country/countries.html',
        title: 'content.list.COUNTRIES',
        ncyBreadcrumb: {
            label: 'content.list.COUNTRIES'
        },
        params: {
            'countriesIsFiltersVisible': null,
            'countriesPage': null,
            'countriesCount': null,
            'countriesSorting': null,
            'countriesFilter': null
        },
        resolve: loadSequence('CountriesCtrl', 'countryService', 'userService')
    }).state('app.systemsettings.countriesnew', {
        url: '/countries/new',
        templateUrl: '/bundles/livnyou/js/components/Country/country_form.html',
        title: 'content.list.NEWCOUNTRY',
        ncyBreadcrumb: {
            label: 'content.list.NEWCOUNTRY'
        },
        params: {
        },
        resolve: loadSequence('CountryFormCtrl', 'countryService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.systemsettings.countriesedit', {
        url: '/countries/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/Country/country_form.html',
        title: 'content.list.EDITCOUNTRY',
        ncyBreadcrumb: {
            label: 'content.list.EDITCOUNTRY'
        },
        resolve: loadSequence('CountryFormCtrl', 'countryService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.systemsettings.countriesdetails', {
        url: '/countries/details/:id',
        templateUrl: '/bundles/livnyou/js/components/Country/country.html',
        ncyBreadcrumb: {
            label: 'content.list.COUNTRYDETAILS'
        },
        resolve: loadSequence('CountryCtrl', 'countryService')
    }).state('app.accesscontrol', {
        url: '/access-control',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'sidebar.nav.accesscontrol.MAIN',
        ncyBreadcrumb: {
            label: 'sidebar.nav.accesscontrol.MAIN'
        }
    }).state('app.accesscontrol.users', {
        url: '/users',
        templateUrl: '/bundles/livnyou/js/components/User/users.html',
        title: 'content.list.USERS',
        ncyBreadcrumb: {
            label: 'content.list.USERS'
        },
        params: {
            'usersIsFiltersVisible': null,
            'usersPage': null,
            'usersCount': null,
            'usersSorting': null,
            'usersFilter': null
        },
        resolve: loadSequence('UsersCtrl', 'userService', 'countryService', 'languageService', 'groupService')
    }).state('app.accesscontrol.usersnew', {
        url: '/users/new',
        templateUrl: '/bundles/livnyou/js/components/User/user_form.html',
        title: 'content.list.NEWUSER',
        ncyBreadcrumb: {
            label: 'content.list.NEWUSER'
        },
        params: {
            'user_country': null,
            'user_language': null
        },
        resolve: loadSequence('UserFormCtrl', 'userService', 'countryService', 'languageService', 'groupService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.accesscontrol.usersedit', {
        url: '/users/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/User/user_form.html',
        title: 'content.list.EDITUSER',
        ncyBreadcrumb: {
            label: 'content.list.EDITUSER'
        },
        resolve: loadSequence('UserFormCtrl', 'userService', 'countryService', 'languageService', 'groupService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.accesscontrol.usersdetails', {
        url: '/users/details/:id',
        templateUrl: '/bundles/livnyou/js/components/User/user.html',
        ncyBreadcrumb: {
            label: 'content.list.USERDETAILS'
        },
        resolve: loadSequence('UserCtrl', 'userService')
    }).state('app.accesscontrol.groups', {
        url: '/groups',
        templateUrl: '/bundles/livnyou/js/components/Group/groups.html',
        title: 'content.list.GROUPS',
        ncyBreadcrumb: {
            label: 'content.list.GROUPS'
        },
        params: {
            'groupsIsFiltersVisible': null,
            'groupsPage': null,
            'groupsCount': null,
            'groupsSorting': null,
            'groupsFilter': null
        },
        resolve: loadSequence('GroupsCtrl', 'groupService', 'userService')
    }).state('app.accesscontrol.groupsnew', {
        url: '/groups/new',
        templateUrl: '/bundles/livnyou/js/components/Group/group_form.html',
        title: 'content.list.NEWGROUP',
        ncyBreadcrumb: {
            label: 'content.list.NEWGROUP'
        },
        params: {
        },
        resolve: loadSequence('GroupFormCtrl', 'groupService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.accesscontrol.groupsedit', {
        url: '/groups/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/Group/group_form.html',
        title: 'content.list.EDITGROUP',
        ncyBreadcrumb: {
            label: 'content.list.EDITGROUP'
        },
        resolve: loadSequence('GroupFormCtrl', 'groupService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.accesscontrol.groupsdetails', {
        url: '/groups/details/:id',
        templateUrl: '/bundles/livnyou/js/components/Group/group.html',
        ncyBreadcrumb: {
            label: 'content.list.GROUPDETAILS'
        },
        resolve: loadSequence('GroupCtrl', 'groupService')
    }).state('app.accesscontrol.sessions', {
        url: '/sessions',
        templateUrl: '/bundles/livnyou/js/components/Session/sessions.html',
        title: 'content.list.SESSIONS',
        ncyBreadcrumb: {
            label: 'content.list.SESSIONS'
        },
        params: {
            'sessionsIsFiltersVisible': null,
            'sessionsPage': null,
            'sessionsCount': null,
            'sessionsSorting': null,
            'sessionsFilter': null
        },
        resolve: loadSequence('SessionsCtrl', 'sessionService', 'userService')
    }).state('app.accesscontrol.sessionsnew', {
        url: '/sessions/new',
        templateUrl: '/bundles/livnyou/js/components/Session/session_form.html',
        title: 'content.list.NEWSESSION',
        ncyBreadcrumb: {
            label: 'content.list.NEWSESSION'
        },
        params: {
        },
        resolve: loadSequence('SessionFormCtrl', 'sessionService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.accesscontrol.sessionsedit', {
        url: '/sessions/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/Session/session_form.html',
        title: 'content.list.EDITSESSION',
        ncyBreadcrumb: {
            label: 'content.list.EDITSESSION'
        },
        resolve: loadSequence('SessionFormCtrl', 'sessionService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.accesscontrol.sessionsdetails', {
        url: '/sessions/details/:id',
        templateUrl: '/bundles/livnyou/js/components/Session/session.html',
        ncyBreadcrumb: {
            label: 'content.list.SESSIONDETAILS'
        },
        resolve: loadSequence('SessionCtrl', 'sessionService')
    }).state('app.accesscontrol.logs', {
        url: '/logs',
        templateUrl: '/bundles/livnyou/js/components/Log/logs.html',
        title: 'content.list.LOGS',
        ncyBreadcrumb: {
            label: 'content.list.LOGS'
        },
        params: {
            'logsIsFiltersVisible': null,
            'logsPage': null,
            'logsCount': null,
            'logsSorting': null,
            'logsFilter': null
        },
        resolve: loadSequence('LogsCtrl', 'logService', 'sessionService', 'userService')
    }).state('app.accesscontrol.logsnew', {
        url: '/logs/new',
        templateUrl: '/bundles/livnyou/js/components/Log/log_form.html',
        title: 'content.list.NEWLOG',
        ncyBreadcrumb: {
            label: 'content.list.NEWLOG'
        },
        params: {
            'log_session': null
        },
        resolve: loadSequence('LogFormCtrl', 'logService', 'sessionService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.accesscontrol.logsedit', {
        url: '/logs/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/Log/log_form.html',
        title: 'content.list.EDITLOG',
        ncyBreadcrumb: {
            label: 'content.list.EDITLOG'
        },
        resolve: loadSequence('LogFormCtrl', 'logService', 'sessionService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.accesscontrol.logsdetails', {
        url: '/logs/details/:id',
        templateUrl: '/bundles/livnyou/js/components/Log/log.html',
        ncyBreadcrumb: {
            label: 'content.list.LOGDETAILS'
        },
        resolve: loadSequence('LogCtrl', 'logService')
    }).state('app.translation', {
        url: '/translation',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'sidebar.nav.translation.MAIN',
        ncyBreadcrumb: {
            label: 'sidebar.nav.translation.MAIN'
        }
    }).state('app.translation.languages', {
        url: '/languages',
        templateUrl: '/bundles/livnyou/js/components/Language/languages.html',
        title: 'content.list.LANGUAGES',
        ncyBreadcrumb: {
            label: 'content.list.LANGUAGES'
        },
        params: {
            'languagesIsFiltersVisible': null,
            'languagesPage': null,
            'languagesCount': null,
            'languagesSorting': null,
            'languagesFilter': null
        },
        resolve: loadSequence('LanguagesCtrl', 'languageService', 'userService')
    }).state('app.translation.languagesnew', {
        url: '/languages/new',
        templateUrl: '/bundles/livnyou/js/components/Language/language_form.html',
        title: 'content.list.NEWLANGUAGE',
        ncyBreadcrumb: {
            label: 'content.list.NEWLANGUAGE'
        },
        params: {
        },
        resolve: loadSequence('LanguageFormCtrl', 'languageService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.translation.languagesedit', {
        url: '/languages/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/Language/language_form.html',
        title: 'content.list.EDITLANGUAGE',
        ncyBreadcrumb: {
            label: 'content.list.EDITLANGUAGE'
        },
        resolve: loadSequence('LanguageFormCtrl', 'languageService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.translation.languagesdetails', {
        url: '/languages/details/:id',
        templateUrl: '/bundles/livnyou/js/components/Language/language.html',
        ncyBreadcrumb: {
            label: 'content.list.LANGUAGEDETAILS'
        },
        resolve: loadSequence('LanguageCtrl', 'languageService')
    }).state('app.translation.translationphysicalactivities', {
        url: '/translation-physical-activities',
        templateUrl: '/bundles/livnyou/js/components/TranslationPhysicalActivity/translation_physical_activities.html',
        title: 'content.list.TRANSLATIONPHYSICALACTIVITIES',
        ncyBreadcrumb: {
            label: 'content.list.TRANSLATIONPHYSICALACTIVITIES'
        },
        params: {
            'translationPhysicalActivitiesIsFiltersVisible': null,
            'translationPhysicalActivitiesPage': null,
            'translationPhysicalActivitiesCount': null,
            'translationPhysicalActivitiesSorting': null,
            'translationPhysicalActivitiesFilter': null
        },
        resolve: loadSequence('TranslationPhysicalActivitiesCtrl', 'translationPhysicalActivityService', 'physicalActivityService', 'userService')
    }).state('app.translation.translationphysicalactivitiesnew', {
        url: '/translation-physical-activities/new',
        templateUrl: '/bundles/livnyou/js/components/TranslationPhysicalActivity/translation_physical_activity_form.html',
        title: 'content.list.NEWTRANSLATIONPHYSICALACTIVITY',
        ncyBreadcrumb: {
            label: 'content.list.NEWTRANSLATIONPHYSICALACTIVITY'
        },
        params: {
            'translation_physical_activity_physical_activity': null
        },
        resolve: loadSequence('TranslationPhysicalActivityFormCtrl', 'translationPhysicalActivityService', 'physicalActivityService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.translation.translationphysicalactivitiesedit', {
        url: '/translation-physical-activities/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/TranslationPhysicalActivity/translation_physical_activity_form.html',
        title: 'content.list.EDITTRANSLATIONPHYSICALACTIVITY',
        ncyBreadcrumb: {
            label: 'content.list.EDITTRANSLATIONPHYSICALACTIVITY'
        },
        resolve: loadSequence('TranslationPhysicalActivityFormCtrl', 'translationPhysicalActivityService', 'physicalActivityService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.translation.translationphysicalactivitiesdetails', {
        url: '/translation-physical-activities/details/:id',
        templateUrl: '/bundles/livnyou/js/components/TranslationPhysicalActivity/translation_physical_activity.html',
        ncyBreadcrumb: {
            label: 'content.list.TRANSLATIONPHYSICALACTIVITYDETAILS'
        },
        resolve: loadSequence('TranslationPhysicalActivityCtrl', 'translationPhysicalActivityService')
    }).state('app.translation.translationcountries', {
        url: '/translation-countries',
        templateUrl: '/bundles/livnyou/js/components/TranslationCountry/translation_countries.html',
        title: 'content.list.TRANSLATIONCOUNTRIES',
        ncyBreadcrumb: {
            label: 'content.list.TRANSLATIONCOUNTRIES'
        },
        params: {
            'translationCountriesIsFiltersVisible': null,
            'translationCountriesPage': null,
            'translationCountriesCount': null,
            'translationCountriesSorting': null,
            'translationCountriesFilter': null
        },
        resolve: loadSequence('TranslationCountriesCtrl', 'translationCountryService', 'countryService', 'userService')
    }).state('app.translation.translationcountriesnew', {
        url: '/translation-countries/new',
        templateUrl: '/bundles/livnyou/js/components/TranslationCountry/translation_country_form.html',
        title: 'content.list.NEWTRANSLATIONCOUNTRY',
        ncyBreadcrumb: {
            label: 'content.list.NEWTRANSLATIONCOUNTRY'
        },
        params: {
            'translation_country_country': null
        },
        resolve: loadSequence('TranslationCountryFormCtrl', 'translationCountryService', 'countryService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.translation.translationcountriesedit', {
        url: '/translation-countries/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/TranslationCountry/translation_country_form.html',
        title: 'content.list.EDITTRANSLATIONCOUNTRY',
        ncyBreadcrumb: {
            label: 'content.list.EDITTRANSLATIONCOUNTRY'
        },
        resolve: loadSequence('TranslationCountryFormCtrl', 'translationCountryService', 'countryService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.translation.translationcountriesdetails', {
        url: '/translation-countries/details/:id',
        templateUrl: '/bundles/livnyou/js/components/TranslationCountry/translation_country.html',
        ncyBreadcrumb: {
            label: 'content.list.TRANSLATIONCOUNTRYDETAILS'
        },
        resolve: loadSequence('TranslationCountryCtrl', 'translationCountryService')
    }).state('app.translation.translationpathologies', {
        url: '/translation-pathologies',
        templateUrl: '/bundles/livnyou/js/components/TranslationPathology/translation_pathologies.html',
        title: 'content.list.TRANSLATIONPATHOLOGIES',
        ncyBreadcrumb: {
            label: 'content.list.TRANSLATIONPATHOLOGIES'
        },
        params: {
            'translationPathologiesIsFiltersVisible': null,
            'translationPathologiesPage': null,
            'translationPathologiesCount': null,
            'translationPathologiesSorting': null,
            'translationPathologiesFilter': null
        },
        resolve: loadSequence('TranslationPathologiesCtrl', 'translationPathologyService', 'pathologyService', 'userService')
    }).state('app.translation.translationpathologiesnew', {
        url: '/translation-pathologies/new',
        templateUrl: '/bundles/livnyou/js/components/TranslationPathology/translation_pathology_form.html',
        title: 'content.list.NEWTRANSLATIONPATHOLOGY',
        ncyBreadcrumb: {
            label: 'content.list.NEWTRANSLATIONPATHOLOGY'
        },
        params: {
            'translation_pathology_pathology': null
        },
        resolve: loadSequence('TranslationPathologyFormCtrl', 'translationPathologyService', 'pathologyService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.translation.translationpathologiesedit', {
        url: '/translation-pathologies/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/TranslationPathology/translation_pathology_form.html',
        title: 'content.list.EDITTRANSLATIONPATHOLOGY',
        ncyBreadcrumb: {
            label: 'content.list.EDITTRANSLATIONPATHOLOGY'
        },
        resolve: loadSequence('TranslationPathologyFormCtrl', 'translationPathologyService', 'pathologyService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.translation.translationpathologiesdetails', {
        url: '/translation-pathologies/details/:id',
        templateUrl: '/bundles/livnyou/js/components/TranslationPathology/translation_pathology.html',
        ncyBreadcrumb: {
            label: 'content.list.TRANSLATIONPATHOLOGYDETAILS'
        },
        resolve: loadSequence('TranslationPathologyCtrl', 'translationPathologyService')
    }).state('app.measurementmanager', {
        url: '/measurement-manager',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'sidebar.nav.measurementmanager.MAIN',
        ncyBreadcrumb: {
            label: 'sidebar.nav.measurementmanager.MAIN'
        }
    }).state('app.measurementmanager.measurements', {
        url: '/measurements',
        templateUrl: '/bundles/livnyou/js/components/Measurement/measurements.html',
        title: 'content.list.MEASUREMENTS',
        ncyBreadcrumb: {
            label: 'content.list.MEASUREMENTS'
        },
        params: {
            'measurementsIsFiltersVisible': null,
            'measurementsPage': null,
            'measurementsCount': null,
            'measurementsSorting': null,
            'measurementsFilter': null
        },
        resolve: loadSequence('MeasurementsCtrl', 'measurementService', 'countryService', 'physicalActivityService', 'userService')
    }).state('app.measurementmanager.measurementsnew', {
        url: '/measurements/new',
        templateUrl: '/bundles/livnyou/js/components/Measurement/measurement_form.html',
        title: 'content.list.NEWMEASUREMENT',
        ncyBreadcrumb: {
            label: 'content.list.NEWMEASUREMENT'
        },
        params: {
            'measurement_country': null,
            'measurement_physical_activity': null
        },
        resolve: loadSequence('MeasurementFormCtrl', 'measurementService', 'countryService', 'physicalActivityService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.measurementmanager.measurementsedit', {
        url: '/measurements/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/Measurement/measurement_form.html',
        title: 'content.list.EDITMEASUREMENT',
        ncyBreadcrumb: {
            label: 'content.list.EDITMEASUREMENT'
        },
        resolve: loadSequence('MeasurementFormCtrl', 'measurementService', 'countryService', 'physicalActivityService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.measurementmanager.measurementsdetails', {
        url: '/measurements/details/:id',
        templateUrl: '/bundles/livnyou/js/components/Measurement/measurement_custom.html',
        ncyBreadcrumb: {
            label: 'content.list.MEASUREMENTDETAILS'
        },
        resolve: loadSequence('MeasurementCtrl', 'measurementService')
    }).state('app.templatemanager', {
        url: '/template-manager',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'sidebar.nav.templatemanager.MAIN',
        ncyBreadcrumb: {
            label: 'sidebar.nav.templatemanager.MAIN'
        }
    }).state('app.templatemanager.templates', {
        url: '/templates',
        templateUrl: '/bundles/livnyou/js/components/Template/templates.html',
        title: 'content.list.TEMPLATES',
        ncyBreadcrumb: {
            label: 'content.list.TEMPLATES'
        },
        params: {
            'templatesIsFiltersVisible': null,
            'templatesPage': null,
            'templatesCount': null,
            'templatesSorting': null,
            'templatesFilter': null
        },
        resolve: loadSequence('TemplatesCtrl', 'templateService', 'userService')
    }).state('app.templatemanager.templatesnew', {
        url: '/templates/new',
        templateUrl: '/bundles/livnyou/js/components/Template/template_form.html',
        title: 'content.list.NEWTEMPLATE',
        ncyBreadcrumb: {
            label: 'content.list.NEWTEMPLATE'
        },
        params: {
        },
        resolve: loadSequence('TemplateFormCtrl', 'templateService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.templatemanager.templatesedit', {
        url: '/templates/edit/:id',
        templateUrl: '/bundles/livnyou/js/components/Template/template_form.html',
        title: 'content.list.EDITTEMPLATE',
        ncyBreadcrumb: {
            label: 'content.list.EDITTEMPLATE'
        },
        resolve: loadSequence('TemplateFormCtrl', 'templateService', 'userService', 'ui.select', 'monospaced.elastic', 'touchspin-plugin', 'checklist-model', 'ckeditor-plugin', 'ckeditor')
    }).state('app.templatemanager.templatesdetails', {
        url: '/templates/details/:id',
        templateUrl: '/bundles/livnyou/js/components/Template/template.html',
        ncyBreadcrumb: {
            label: 'content.list.TEMPLATEDETAILS'
        },
        resolve: loadSequence('TemplateCtrl', 'templateService')
    }).state('app.templatemanager.templatesassign',{
        url: '/assign',
        templateUrl: '/bundles/livnyou/js/components/Template/assign.html',
        title: 'content.list.ASSIGN',
        ncyBreadcrumb: {
            label:'content.list.ASSIGN'
        },
        resolve: loadSequence('TemplateAssignCtrl', 'TemplateServiceCtrl', 'patientGroupService', 'templateService')
    })
;

}]);
