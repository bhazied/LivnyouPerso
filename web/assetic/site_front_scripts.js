/** 
  * declare 'ubid-electricity' module with dependencies
*/
'use strict';
angular.module('ubid-electricity', [
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

'use strict'
var app = angular.module('UbidElectricityFront', ['ubid-electricity', 'bw.paging', 'isteven-multi-select', 'angularFileUpload']);

var languages = {
    'en' : 'English'/*,
    'fr' : 'Français',
    'es' : 'Español',
    'it' : 'Italiano',
    'de' : 'Deutsch'*/
};

app.run(['$rootScope', '$state', '$stateParams', '$localStorage', '$sessionStorage', '$timeout', '$interval',
    function ($rootScope, $state, $stateParams, $localStorage, $sessionStorage, $timeout, $interval) {

        $rootScope.languages = languages;
        $rootScope.countLanguages = Object.keys(languages).length;

        $rootScope.phonePattern= /^\+?\d+$/;

        // Attach Fastclick for eliminating the 300ms delay between a physical tap and the firing of a click event on mobile browsers
        FastClick.attach(document.body);

        // Set some reference to access them from any scope
        $rootScope.$state = $state;
        $rootScope.$stateParams = $stateParams;

        // left right side to be shown or not
        $rootScope.leftrightside = false;
        // GLOBAL APP SCOPE
        // set below basic information
        $rootScope.app = {
            name: 'E-electricity', // name of your project
            description: 'Electricity Tenders web site', // brief description
            keywords: 'Electricity, Tenders, Buyers, Suppliers, Products', // some keywords
            author: 'ContinuousNet', // author's name or company name
            version: '2.0', // current version
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
                logo: '/assets/images/big_logo.png', // relative path of the project logo
            }
        };

        if (angular.isDefined($localStorage.user)) {
            $rootScope.user = $rootScope.currentUser = $localStorage.user;

        } else {
            $rootScope.user = $rootScope.currentUser = {
                //firstName: 'Guest',
                //job: 'Visitor',
                //picture: 'app/img/user/02.jpg',
                //roles: []
            };
        }
        $rootScope.loggedIn = angular.isDefined($localStorage.access_token);

        $rootScope.seo = {
            meta_title: '',
            meta_description: '',
            meta_keywords: ''
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

        $timeout(function(){

            if (window.location.href.indexOf('reset') == -1 && window.location.href.indexOf('email-confirm') == -1) {

                $rootScope.underPage = true;
                if (angular.isDefined($sessionStorage.underPage)) {
                    $rootScope.underPage = false;
                } else {
                    $rootScope.initialTime = $rootScope.timer = 6;
                    $rootScope.circleRadius = 66;
                    $sessionStorage.underPage = true;
                    $rootScope.interval = $interval(function() {
                        $rootScope.timer--;
                        if ($rootScope.timer < 0) {
                            $rootScope.timer = 0;
                            $interval.cancel($rootScope.interval);
                            $rootScope.underPage = false;
                        }
                        var angle = Math.PI*($rootScope.circleRadius*2);
                        var percent = (($rootScope.initialTime-$rootScope.timer)/$rootScope.initialTime)*angle;
                        $('.circle_animation').css({strokeDashoffset: percent});
                    }, 1000);
                }

            } else {
                $rootScope.underPage = false;
                $sessionStorage.underPage = true;
            }
        });

    }]);

// translate config
app.config(['$translateProvider',   
    function ($translateProvider) {

    // prefix and suffix information  is required to specify a pattern
    // You can simply use the static-files loader with this pattern:
    $translateProvider.useStaticFilesLoader({
        prefix: '/assets/i18n/front/',
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
        cfpLoadingBarProvider.includeBar = false;
        cfpLoadingBarProvider.includeSpinner = true;
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

// location
// configuration
app.config(function($locationProvider) {
    $locationProvider.html5Mode({
        enabled: true,
        requireBase: true
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
        'perfect-scrollbar-plugin': ['/assets/bower_components/perfect-scrollbar/js/min/perfect-scrollbar.jquery.min.js', '/assets/bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css'],
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

app.factory('httpRequestInterceptor', ['$q', '$localStorage', '$location', '$filter', '$timeout', 'toaster', '$rootScope',
    function ($q, $localStorage, $location, $filter, $timeout, toaster, $rootScope) {
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
                    delete $localStorage.user;
                    delete $rootScope.user;
                    $location.path('/login');
                } else if (response.status === 403) {
                    toaster.pop('warning', $filter('translate')('content.common.WARNING'), $filter('translate')('login.ACCESSDENEID'));
                    $timeout(function(){
                        $location.path('/');
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
        // For any unmatched url, redirect to /
        $urlRouterProvider.otherwise('/');
        //
        // Set up the states
        $stateProvider.state('front', {
            templateUrl: '/assets/views/front/front.html',
            resolve: loadSequence(
                'modernizr',
                'moment', 
                'angularMoment', 
                'uiSwitch', 
                'perfect-scrollbar-plugin', 
                'toaster', 
                'ngAside', 
                'vAccordion', 
                'sweet-alert', 
                'chartjs', 
                'tc.chartjs', 
                'oitozero.ngSweetAlert',
                'truncate', 
                'htmlToPlaintext', 
                'angular-notification-icons',
                'SearchFormCtrl',
                'searchService',
                'languageService',
                'countryService',
                'tenderFrontService',
                'checklist-model',
                'MyNotification',
                'notificationService',
                'notificationFrontService'
            ),
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
/*! 
* angular-paging v2.2.2 by Brant Wills - MIT licensed 
* https://github.com/brantwills/Angular-Paging.git 
*/
angular.module("bw.paging",[]).directive("paging",function(){function a(a,b,c){a.$watchCollection("[page,pageSize,total,disabled]",function(){l(a,c)})}function b(a,b){return'<ul data-ng-hide="Hide" data-ng-class="ulClass"> <li title="{{Item.title}}" data-ng-class="Item.liClass" data-ng-repeat="Item in List"> <a '+(b.pgHref?'data-ng-href="{{Item.pgHref}}" ':"href ")+'data-ng-class="Item.aClass" data-ng-click="Item.action()" data-ng-bind="Item.value"></a> </li></ul>'}function c(a,b){a.List=[],a.Hide=!1,a.page=parseInt(a.page)||1,a.total=parseInt(a.total)||0,a.adjacent=parseInt(a.adjacent)||2,a.pgHref=a.pgHref||"",a.dots=a.dots||"...",a.ulClass=a.ulClass||"pagination",a.activeClass=a.activeClass||"active",a.disabledClass=a.disabledClass||"disabled",a.textFirst=a.textFirst||"<<",a.textLast=a.textLast||">>",a.textNext=a.textNext||">",a.textPrev=a.textPrev||"<",a.textFirstClass=a.textFirstClass||"",a.textLastClass=a.textLastClass||"",a.textNextClass=a.textNextClass||"",a.textPrevClass=a.textPrevClass||"",a.textTitlePage=a.textTitlePage||"Page {page}",a.textTitleFirst=a.textTitleFirst||"First Page",a.textTitleLast=a.textTitleLast||"Last Page",a.textTitleNext=a.textTitleNext||"Next Page",a.textTitlePrev=a.textTitlePrev||"Previous Page",a.hideIfEmpty=d(a,b.hideIfEmpty),a.showPrevNext=d(a,b.showPrevNext),a.showFirstLast=d(a,b.showFirstLast),a.scrollTop=d(a,b.scrollTop),a.isDisabled=d(a,b.disabled)}function d(a,b){return angular.isDefined(b)?!!a.$parent.$eval(b):!1}function e(a,b){a.page>b&&(a.page=b),a.page<=0&&(a.page=1),a.adjacent<=0&&(a.adjacent=2),1>=b&&(a.Hide=a.hideIfEmpty)}function f(a,b){a.page!=b&&(a.isDisabled||(a.page=b,a.pagingAction({page:a.page,pageSize:a.pageSize,total:a.total}),a.scrollTop&&scrollTo(0,0)))}function g(a,b,c){if(!(!a.showPrevNext&&!a.showFirstLast||1>b)){var d,e,g;if("prev"===c){d=a.page-1<=0;var h=a.page-1<=0?1:a.page-1;a.showFirstLast&&(e={value:a.textFirst,title:a.textTitleFirst,aClass:a.textFirstClass,page:1}),a.showPrevNext&&(g={value:a.textPrev,title:a.textTitlePrev,aClass:a.textPrevClass,page:h})}else{d=a.page+1>b;var i=a.page+1>=b?b:a.page+1;a.showPrevNext&&(e={value:a.textNext,title:a.textTitleNext,aClass:a.textNextClass,page:i}),a.showFirstLast&&(g={value:a.textLast,title:a.textTitleLast,aClass:a.textLastClass,page:b})}var j=function(b,c){return{title:b.title,aClass:b.aClass,value:b.aClass?"":b.value,liClass:c?a.disabledClass:"",pgHref:c?"":a.pgHref.replace(m,b.page),action:function(){c||f(a,b.page)}}};if(a.isDisabled&&(d=!0),e){var k=j(e,d);a.List.push(k)}if(g){var l=j(g,d);a.List.push(l)}}}function h(a,b,c){var d=0;for(d=a;b>=d;d++){var e=c.pgHref.replace(m,d),g=c.page==d?c.activeClass:"";c.isDisabled&&(e="",g=c.disabledClass),c.List.push({value:d,title:c.textTitlePage.replace(m,d),liClass:g,pgHref:e,action:function(){f(c,this.value)}})}}function i(a){a.List.push({value:a.dots,liClass:a.disabledClass})}function j(a,b){h(1,2,a),3!=b&&i(a)}function k(a,b,c){c!=a-2&&i(b),h(a-1,a,b)}function l(a,b){(!a.pageSize||a.pageSize<=0)&&(a.pageSize=1);var d=Math.ceil(a.total/a.pageSize);c(a,b),e(a,d);var f,i,l=2*a.adjacent+2;g(a,d,"prev"),l+2>=d?(f=1,h(f,d,a)):a.page-a.adjacent<=2?(f=1,i=1+l,h(f,i,a),k(d,a,i)):a.page<d-(a.adjacent+2)?(f=a.page-a.adjacent,i=a.page+a.adjacent,j(a,f),h(f,i,a),k(d,a,i)):(f=d-l,i=d,j(a,f),h(f,i,a)),g(a,d,"next")}var m=/\{page\}/g;return{restrict:"EA",link:a,template:b,scope:{page:"=",pageSize:"=",total:"=",disabled:"@",dots:"@",ulClass:"@",activeClass:"@",disabledClass:"@",adjacent:"@",pagingAction:"&",pgHref:"@",textFirst:"@",textLast:"@",textNext:"@",textPrev:"@",textFirstClass:"@",textLastClass:"@",textNextClass:"@",textPrevClass:"@",textTitlePage:"@",textTitleFirst:"@",textTitleLast:"@",textTitleNext:"@",textTitlePrev:"@"}}});
/* 
 * Angular JS Multi Select
 * Creates a dropdown-like button with checkboxes. 
 *
 * Project started on: Tue, 14 Jan 2014 - 5:18:02 PM
 * Current version: 4.0.0
 * 
 * Released under the MIT License
 * --------------------------------------------------------------------------------
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Ignatius Steven (https://github.com/isteven)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), to deal 
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is 
 * furnished to do so, subject to the following conditions: 
 *
 * The above copyright notice and this permission notice shall be included in all 
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
 * SOFTWARE.
 * --------------------------------------------------------------------------------
 */

'use strict'

angular.module( 'isteven-multi-select', ['ng'] ).directive( 'istevenMultiSelect' , [ '$sce', '$timeout', '$templateCache', function ( $sce, $timeout, $templateCache ) {
    return {
        restrict: 
            'AE',

        scope: 
        {   
            // models
            inputModel      : '=',
            outputModel     : '=',

            // settings based on attribute
            isDisabled      : '=',

            // callbacks
            onClear         : '&',  
            onClose         : '&',
            onSearchChange  : '&',  
            onItemClick     : '&',            
            onOpen          : '&', 
            onReset         : '&',  
            onSelectAll     : '&',  
            onSelectNone    : '&',  

            // i18n
            translation     : '='   
        },
        
        /* 
         * The rest are attributes. They don't need to be parsed / binded, so we can safely access them by value.
         * - buttonLabel, directiveId, helperElements, itemLabel, maxLabels, orientation, selectionMode, minSearchLength,
         *   tickProperty, disableProperty, groupProperty, searchProperty, maxHeight, outputProperties
         */
                                                         
         templateUrl: 
            'isteven-multi-select.htm',                            

        link: function ( $scope, element, attrs ) {                       

            $scope.backUp           = [];
            $scope.varButtonLabel   = '';               
            $scope.spacingProperty  = '';
            $scope.indexProperty    = '';                        
            $scope.orientationH     = false;
            $scope.orientationV     = true;
            $scope.filteredModel    = [];
            $scope.inputLabel       = { labelFilter: '' };                        
            $scope.tabIndex         = 0;            
            $scope.lang             = {};
            $scope.helperStatus     = {
                all     : true,
                none    : true,
                reset   : true,
                filter  : true
            };

            var 
                prevTabIndex        = 0,
                helperItems         = [],
                helperItemsLength   = 0,
                checkBoxLayer       = '',
                scrolled            = false,
                selectedItems       = [],
                formElements        = [],
                vMinSearchLength    = 0,
                clickedItem         = null                

            // v3.0.0
            // clear button clicked
            $scope.clearClicked = function( e ) {                
                $scope.inputLabel.labelFilter = '';
                $scope.updateFilter();
                $scope.select( 'clear', e );                
            }

            // A little hack so that AngularJS ng-repeat can loop using start and end index like a normal loop
            // http://stackoverflow.com/questions/16824853/way-to-ng-repeat-defined-number-of-times-instead-of-repeating-over-array
            $scope.numberToArray = function( num ) {
                return new Array( num );   
            }

            // Call this function when user type on the filter field
            $scope.searchChanged = function() {                                                
                if ( $scope.inputLabel.labelFilter.length < vMinSearchLength && $scope.inputLabel.labelFilter.length > 0 ) {
                    return false;
                }                
                $scope.updateFilter();
            }

            $scope.updateFilter = function()
            {      
                // we check by looping from end of input-model
                $scope.filteredModel = [];
                var i = 0;

                if ( typeof $scope.inputModel === 'undefined' ) {
                    return false;                   
                }

                for( i = $scope.inputModel.length - 1; i >= 0; i-- ) {

                    // if it's group end, we push it to filteredModel[];
                    if ( typeof $scope.inputModel[ i ][ attrs.groupProperty ] !== 'undefined' && $scope.inputModel[ i ][ attrs.groupProperty ] === false ) {
                        $scope.filteredModel.push( $scope.inputModel[ i ] );
                    }
                    
                    // if it's data 
                    var gotData = false;
                    if ( typeof $scope.inputModel[ i ][ attrs.groupProperty ] === 'undefined' ) {                        
                        
                        // If we set the search-key attribute, we use this loop. 
                        if ( typeof attrs.searchProperty !== 'undefined' && attrs.searchProperty !== '' ) {

                            for (var key in $scope.inputModel[ i ]  ) {
                                if ( 
                                    typeof $scope.inputModel[ i ][ key ] !== 'boolean'
                                    && String( $scope.inputModel[ i ][ key ] ).toUpperCase().indexOf( $scope.inputLabel.labelFilter.toUpperCase() ) >= 0                                     
                                    && attrs.searchProperty.indexOf( key ) > -1
                                ) {
                                    gotData = true;
                                    break;
                                }
                            }                        
                        }
                        // if there's no search-key attribute, we use this one. Much better on performance.
                        else {
                            for ( var key in $scope.inputModel[ i ]  ) {
                                if ( 
                                    typeof $scope.inputModel[ i ][ key ] !== 'boolean'
                                    && String( $scope.inputModel[ i ][ key ] ).toUpperCase().indexOf( $scope.inputLabel.labelFilter.toUpperCase() ) >= 0                                     
                                ) {
                                    gotData = true;
                                    break;
                                }
                            }                        
                        }

                        if ( gotData === true ) {    
                            // push
                            $scope.filteredModel.push( $scope.inputModel[ i ] );
                        }
                    }

                    // if it's group start
                    if ( typeof $scope.inputModel[ i ][ attrs.groupProperty ] !== 'undefined' && $scope.inputModel[ i ][ attrs.groupProperty ] === true ) {

                        if ( typeof $scope.filteredModel[ $scope.filteredModel.length - 1 ][ attrs.groupProperty ] !== 'undefined' 
                                && $scope.filteredModel[ $scope.filteredModel.length - 1 ][ attrs.groupProperty ] === false ) {
                            $scope.filteredModel.pop();
                        }
                        else {
                            $scope.filteredModel.push( $scope.inputModel[ i ] );
                        }
                    }
                }                

                $scope.filteredModel.reverse();  
                
                $timeout( function() {                    

                    $scope.getFormElements();               
                    
                    // Callback: on filter change                      
                    if ( $scope.inputLabel.labelFilter.length > vMinSearchLength ) {

                        var filterObj = [];

                        angular.forEach( $scope.filteredModel, function( value, key ) {
                            if ( typeof value !== 'undefined' ) {                   
                                if ( typeof value[ attrs.groupProperty ] === 'undefined' ) {                                                                    
                                    var tempObj = angular.copy( value );
                                    var index = filterObj.push( tempObj );                                
                                    delete filterObj[ index - 1 ][ $scope.indexProperty ];
                                    delete filterObj[ index - 1 ][ $scope.spacingProperty ];      
                                }
                            }
                        });

                        $scope.onSearchChange({ 
                            data: 
                            {
                                keyword: $scope.inputLabel.labelFilter, 
                                result: filterObj 
                            } 
                        });
                    }
                },0);
            };

            // List all the input elements. We need this for our keyboard navigation.
            // This function will be called everytime the filter is updated. 
            // Depending on the size of filtered mode, might not good for performance, but oh well..
            $scope.getFormElements = function() {                                     
                formElements = [];

                var 
                    selectButtons   = [],
                    inputField      = [],
                    checkboxes      = [],
                    clearButton     = [];
                
                // If available, then get select all, select none, and reset buttons
                if ( $scope.helperStatus.all || $scope.helperStatus.none || $scope.helperStatus.reset ) {                                                       
                    selectButtons = element.children().children().next().children().children()[ 0 ].getElementsByTagName( 'button' );                    
                    // If available, then get the search box and the clear button
                    if ( $scope.helperStatus.filter ) {                                            
                        // Get helper - search and clear button. 
                        inputField =    element.children().children().next().children().children().next()[ 0 ].getElementsByTagName( 'input' );                    
                        clearButton =   element.children().children().next().children().children().next()[ 0 ].getElementsByTagName( 'button' );                        
                    }
                }
                else {
                    if ( $scope.helperStatus.filter ) {   
                        // Get helper - search and clear button. 
                        inputField =    element.children().children().next().children().children()[ 0 ].getElementsByTagName( 'input' );                    
                        clearButton =   element.children().children().next().children().children()[ 0 ].getElementsByTagName( 'button' );
                    }
                }
               
                // Get checkboxes
                if ( !$scope.helperStatus.all && !$scope.helperStatus.none && !$scope.helperStatus.reset && !$scope.helperStatus.filter ) {
                    checkboxes = element.children().children().next()[ 0 ].getElementsByTagName( 'input' );
                }
                else {
                    checkboxes = element.children().children().next().children().next()[ 0 ].getElementsByTagName( 'input' );
                }

                // Push them into global array formElements[] 
                for ( var i = 0; i < selectButtons.length ; i++ )   { formElements.push( selectButtons[ i ] );  }
                for ( var i = 0; i < inputField.length ; i++ )      { formElements.push( inputField[ i ] );     }
                for ( var i = 0; i < clearButton.length ; i++ )     { formElements.push( clearButton[ i ] );    }
                for ( var i = 0; i < checkboxes.length ; i++ )      { formElements.push( checkboxes[ i ] );     }                                
            }            

            // check if an item has attrs.groupProperty (be it true or false)
            $scope.isGroupMarker = function( item , type ) {
                if ( typeof item[ attrs.groupProperty ] !== 'undefined' && item[ attrs.groupProperty ] === type ) return true; 
                return false;
            }

            $scope.removeGroupEndMarker = function( item ) {
                if ( typeof item[ attrs.groupProperty ] !== 'undefined' && item[ attrs.groupProperty ] === false ) return false; 
                return true;
            }                       

            // call this function when an item is clicked
            $scope.syncItems = function( item, e, ng_repeat_index ) {                                      

                e.preventDefault();
                e.stopPropagation();

                // if the directive is globaly disabled, do nothing
                if ( typeof attrs.disableProperty !== 'undefined' && item[ attrs.disableProperty ] === true ) {                                        
                    return false;
                }

                // if item is disabled, do nothing
                if ( typeof attrs.isDisabled !== 'undefined' && $scope.isDisabled === true ) {                        
                    return false;
                }                                

                // if end group marker is clicked, do nothing
                if ( typeof item[ attrs.groupProperty ] !== 'undefined' && item[ attrs.groupProperty ] === false ) {
                    return false;
                }                

                var index = $scope.filteredModel.indexOf( item );       

                // if the start of group marker is clicked ( only for multiple selection! )
                // how it works:
                // - if, in a group, there are items which are not selected, then they all will be selected
                // - if, in a group, all items are selected, then they all will be de-selected                
                if ( typeof item[ attrs.groupProperty ] !== 'undefined' && item[ attrs.groupProperty ] === true ) {                                  

                    // this is only for multiple selection, so if selection mode is single, do nothing
                    if ( typeof attrs.selectionMode !== 'undefined' && attrs.selectionMode.toUpperCase() === 'SINGLE' ) {
                        return false;
                    }
                    
                    var i,j,k;
                    var startIndex = 0;
                    var endIndex = $scope.filteredModel.length - 1;
                    var tempArr = [];

                    // nest level is to mark the depth of the group.
                    // when you get into a group (start group marker), nestLevel++
                    // when you exit a group (end group marker), nextLevel--
                    var nestLevel = 0;                    

                    // we loop throughout the filtered model (not whole model)
                    for( i = index ; i < $scope.filteredModel.length ; i++) {  

                        // this break will be executed when we're done processing each group
                        if ( nestLevel === 0 && i > index ) 
                        {
                            break;
                        }
                    
                        if ( typeof $scope.filteredModel[ i ][ attrs.groupProperty ] !== 'undefined' && $scope.filteredModel[ i ][ attrs.groupProperty ] === true ) {
                            
                            // To cater multi level grouping
                            if ( tempArr.length === 0 ) {
                                startIndex = i + 1; 
                            }                            
                            nestLevel = nestLevel + 1;
                        }                                                

                        // if group end
                        else if ( typeof $scope.filteredModel[ i ][ attrs.groupProperty ] !== 'undefined' && $scope.filteredModel[ i ][ attrs.groupProperty ] === false ) {

                            nestLevel = nestLevel - 1;                            

                            // cek if all are ticked or not                            
                            if ( tempArr.length > 0 && nestLevel === 0 ) {                                

                                var allTicked = true;       

                                endIndex = i;

                                for ( j = 0; j < tempArr.length ; j++ ) {                                
                                    if ( typeof tempArr[ j ][ $scope.tickProperty ] !== 'undefined' &&  tempArr[ j ][ $scope.tickProperty ] === false ) {
                                        allTicked = false;
                                        break;
                                    }
                                }                                                                                    

                                if ( allTicked === true ) {
                                    for ( j = startIndex; j <= endIndex ; j++ ) {
                                        if ( typeof $scope.filteredModel[ j ][ attrs.groupProperty ] === 'undefined' ) {
                                            if ( typeof attrs.disableProperty === 'undefined' ) {
                                                $scope.filteredModel[ j ][ $scope.tickProperty ] = false;
                                                // we refresh input model as well
                                                inputModelIndex = $scope.filteredModel[ j ][ $scope.indexProperty ];
                                                $scope.inputModel[ inputModelIndex ][ $scope.tickProperty ] = false;
                                            }
                                            else if ( $scope.filteredModel[ j ][ attrs.disableProperty ] !== true ) {
                                                $scope.filteredModel[ j ][ $scope.tickProperty ] = false;
                                                // we refresh input model as well
                                                inputModelIndex = $scope.filteredModel[ j ][ $scope.indexProperty ];
                                                $scope.inputModel[ inputModelIndex ][ $scope.tickProperty ] = false;
                                            }
                                        }
                                    }                                
                                }

                                else {
                                    for ( j = startIndex; j <= endIndex ; j++ ) {
                                        if ( typeof $scope.filteredModel[ j ][ attrs.groupProperty ] === 'undefined' ) {
                                            if ( typeof attrs.disableProperty === 'undefined' ) {
                                                $scope.filteredModel[ j ][ $scope.tickProperty ] = true;                                                
                                                // we refresh input model as well
                                                inputModelIndex = $scope.filteredModel[ j ][ $scope.indexProperty ];
                                                $scope.inputModel[ inputModelIndex ][ $scope.tickProperty ] = true;

                                            }                                            
                                            else if ( $scope.filteredModel[ j ][ attrs.disableProperty ] !== true ) {
                                                $scope.filteredModel[ j ][ $scope.tickProperty ] = true;
                                                // we refresh input model as well
                                                inputModelIndex = $scope.filteredModel[ j ][ $scope.indexProperty ];
                                                $scope.inputModel[ inputModelIndex ][ $scope.tickProperty ] = true;
                                            }
                                        }
                                    }                                
                                }                                                                                    
                            }
                        }
            
                        // if data
                        else {                            
                            tempArr.push( $scope.filteredModel[ i ] );                                                                                    
                        }
                    }                                 
                }

                // if an item (not group marker) is clicked
                else {

                    // If it's single selection mode
                    if ( typeof attrs.selectionMode !== 'undefined' && attrs.selectionMode.toUpperCase() === 'SINGLE' ) {
                        
                        // first, set everything to false
                        for( i=0 ; i < $scope.filteredModel.length ; i++) {                            
                            $scope.filteredModel[ i ][ $scope.tickProperty ] = false;                            
                        }        
                        for( i=0 ; i < $scope.inputModel.length ; i++) {                            
                            $scope.inputModel[ i ][ $scope.tickProperty ] = false;                            
                        }        
                        
                        // then set the clicked item to true
                        $scope.filteredModel[ index ][ $scope.tickProperty ] = true;                                                                 
                    }   

                    // Multiple
                    else {
                        $scope.filteredModel[ index ][ $scope.tickProperty ]   = !$scope.filteredModel[ index ][ $scope.tickProperty ];
                    }

                    // we refresh input model as well
                    var inputModelIndex = $scope.filteredModel[ index ][ $scope.indexProperty ];                                        
                    $scope.inputModel[ inputModelIndex ][ $scope.tickProperty ] = $scope.filteredModel[ index ][ $scope.tickProperty ];                    
                }                                  

                // we execute the callback function here
                clickedItem = angular.copy( item );                                                    
                if ( clickedItem !== null ) {                        
                    $timeout( function() {
                        delete clickedItem[ $scope.indexProperty ];
                        delete clickedItem[ $scope.spacingProperty ];      
                        $scope.onItemClick( { data: clickedItem } );
                        clickedItem = null;                    
                    }, 0 );                                                 
                }                                    
                
                $scope.refreshOutputModel();
                $scope.refreshButton();                              

                // We update the index here
                prevTabIndex = $scope.tabIndex;
                $scope.tabIndex = ng_repeat_index + helperItemsLength;
                                
                // Set focus on the hidden checkbox 
                e.target.focus();

                // set & remove CSS style
                $scope.removeFocusStyle( prevTabIndex );
                $scope.setFocusStyle( $scope.tabIndex );

                if ( typeof attrs.selectionMode !== 'undefined' && attrs.selectionMode.toUpperCase() === 'SINGLE' ) {
                    // on single selection mode, we then hide the checkbox layer
                    $scope.toggleCheckboxes( e );       
                }
            }     

            // update $scope.outputModel
            $scope.refreshOutputModel = function() {            
                
                $scope.outputModel  = [];
                var 
                    outputProps     = [],
                    tempObj         = {};

                // v4.0.0
                if ( typeof attrs.outputProperties !== 'undefined' ) {                    
                    outputProps = attrs.outputProperties.split(' ');                
                    angular.forEach( $scope.inputModel, function( value, key ) {                    
                        if ( 
                            typeof value !== 'undefined' 
                            && typeof value[ attrs.groupProperty ] === 'undefined' 
                            && value[ $scope.tickProperty ] === true 
                        ) {
                            tempObj         = {};
                            angular.forEach( value, function( value1, key1 ) {                                
                                if ( outputProps.indexOf( key1 ) > -1 ) {                                                                         
                                    tempObj[ key1 ] = value1;                                    
                                }
                            });
                            var index = $scope.outputModel.push( tempObj );                                                               
                            delete $scope.outputModel[ index - 1 ][ $scope.indexProperty ];
                            delete $scope.outputModel[ index - 1 ][ $scope.spacingProperty ];                                      
                        }
                    });         
                }
                else {
                    angular.forEach( $scope.inputModel, function( value, key ) {                    
                        if ( 
                            typeof value !== 'undefined' 
                            && typeof value[ attrs.groupProperty ] === 'undefined' 
                            && value[ $scope.tickProperty ] === true 
                        ) {
                            var temp = angular.copy( value );
                            var index = $scope.outputModel.push( temp );                                                               
                            delete $scope.outputModel[ index - 1 ][ $scope.indexProperty ];
                            delete $scope.outputModel[ index - 1 ][ $scope.spacingProperty ];                                      
                        }
                    });         
                }
            }

            // refresh button label
            $scope.refreshButton = function() {

                $scope.varButtonLabel   = '';                
                var ctr                 = 0;                  

                // refresh button label...
                if ( $scope.outputModel.length === 0 ) {
                    // https://github.com/isteven/angular-multi-select/pull/19                    
                    $scope.varButtonLabel = $scope.lang.nothingSelected;
                }
                else {                
                    var tempMaxLabels = $scope.outputModel.length;
                    if ( typeof attrs.maxLabels !== 'undefined' && attrs.maxLabels !== '' ) {
                        tempMaxLabels = attrs.maxLabels;
                    }

                    // if max amount of labels displayed..
                    if ( $scope.outputModel.length > tempMaxLabels ) {
                        $scope.more = true;
                    }
                    else {
                        $scope.more = false;
                    }                
                    
                    angular.forEach( $scope.inputModel, function( value, key ) {
                        if ( typeof value !== 'undefined' && value[ attrs.tickProperty ] === true ) {                        
                            if ( ctr < tempMaxLabels ) {                            
                                $scope.varButtonLabel += ( $scope.varButtonLabel.length > 0 ? '</div>, <div class="buttonLabel">' : '<div class="buttonLabel">') + $scope.writeLabel( value, 'buttonLabel' );
                            }
                            ctr++;
                        }
                    });                

                    if ( $scope.more === true ) {
                        // https://github.com/isteven/angular-multi-select/pull/16
                        if (tempMaxLabels > 0) {
                            $scope.varButtonLabel += ', ... ';
                        }
                        $scope.varButtonLabel += '(' + $scope.outputModel.length + ')';                        
                    }
                }
                $scope.varButtonLabel = $sce.trustAsHtml( $scope.varButtonLabel + '<span class="caret"></span>' );                
            }

            // Check if a checkbox is disabled or enabled. It will check the granular control (disableProperty) and global control (isDisabled)
            // Take note that the granular control has higher priority.
            $scope.itemIsDisabled = function( item ) {
                
                if ( typeof attrs.disableProperty !== 'undefined' && item[ attrs.disableProperty ] === true ) {                                        
                    return true;
                }
                else {             
                    if ( $scope.isDisabled === true ) {                        
                        return true;
                    }
                    else {
                        return false;
                    }
                }
                
            }

            // A simple function to parse the item label settings. Used on the buttons and checkbox labels.
            $scope.writeLabel = function( item, type ) {
                
                // type is either 'itemLabel' or 'buttonLabel'
                var temp    = attrs[ type ].split( ' ' );                    
                var label   = '';                

                angular.forEach( temp, function( value, key ) {                    
                    item[ value ] && ( label += '&nbsp;' + value.split( '.' ).reduce( function( prev, current ) {
                        return prev[ current ]; 
                    }, item ));        
                });
                
                if ( type.toUpperCase() === 'BUTTONLABEL' ) {                    
                    return label;
                }
                return $sce.trustAsHtml( label );
            }                                

            // UI operations to show/hide checkboxes based on click event..
            $scope.toggleCheckboxes = function( e ) {                                    
                
                // We grab the button
                var clickedEl = element.children()[0];

                // Just to make sure.. had a bug where key events were recorded twice
                angular.element( document ).off( 'click', $scope.externalClickListener );
                angular.element( document ).off( 'keydown', $scope.keyboardListener );        

                // The idea below was taken from another multi-select directive - https://github.com/amitava82/angular-multiselect 
                // His version is awesome if you need a more simple multi-select approach.                                

                // close
                if ( angular.element( checkBoxLayer ).hasClass( 'show' )) {                         

                    angular.element( checkBoxLayer ).removeClass( 'show' );                    
                    angular.element( clickedEl ).removeClass( 'buttonClicked' );                    
                    angular.element( document ).off( 'click', $scope.externalClickListener );
                    angular.element( document ).off( 'keydown', $scope.keyboardListener );                                    

                    // clear the focused element;
                    $scope.removeFocusStyle( $scope.tabIndex );
                    if ( typeof formElements[ $scope.tabIndex ] !== 'undefined' ) {
                        formElements[ $scope.tabIndex ].blur();
                    }

                    // close callback
                    $timeout( function() {
                        $scope.onClose();
                    }, 0 );

                    // set focus on button again
                    element.children().children()[ 0 ].focus();
                } 
                // open
                else                 
                {    
                    // clear filter
                    $scope.inputLabel.labelFilter = '';                
                    $scope.updateFilter();                                

                    helperItems = [];
                    helperItemsLength = 0;

                    angular.element( checkBoxLayer ).addClass( 'show' );
                    angular.element( clickedEl ).addClass( 'buttonClicked' );       

                    // Attach change event listener on the input filter. 
                    // We need this because ng-change is apparently not an event listener.                    
                    angular.element( document ).on( 'click', $scope.externalClickListener );
                    angular.element( document ).on( 'keydown', $scope.keyboardListener );  

                    // to get the initial tab index, depending on how many helper elements we have. 
                    // priority is to always focus it on the input filter                                                                
                    $scope.getFormElements();
                    $scope.tabIndex = 0;

                    var helperContainer = angular.element( element[ 0 ].querySelector( '.helperContainer' ) )[0];                
                    
                    if ( typeof helperContainer !== 'undefined' ) {
                        for ( var i = 0; i < helperContainer.getElementsByTagName( 'BUTTON' ).length ; i++ ) {
                            helperItems[ i ] = helperContainer.getElementsByTagName( 'BUTTON' )[ i ];
                        }
                        helperItemsLength = helperItems.length + helperContainer.getElementsByTagName( 'INPUT' ).length;
                    }
                    
                    // focus on the filter element on open. 
                    if ( element[ 0 ].querySelector( '.inputFilter' ) ) {                        
                        element[ 0 ].querySelector( '.inputFilter' ).focus();    
                        $scope.tabIndex = $scope.tabIndex + helperItemsLength - 2;
                        // blur button in vain
                        angular.element( element ).children()[ 0 ].blur();
                    }
                    // if there's no filter then just focus on the first checkbox item
                    else {                  
                        if ( !$scope.isDisabled ) {                        
                            $scope.tabIndex = $scope.tabIndex + helperItemsLength;
                            if ( $scope.inputModel.length > 0 ) {
                                formElements[ $scope.tabIndex ].focus();
                                $scope.setFocusStyle( $scope.tabIndex );
                                // blur button in vain
                                angular.element( element ).children()[ 0 ].blur();
                            }                            
                        }
                    }                          

                    // open callback
                    $scope.onOpen();
                }                            
            }
            
            // handle clicks outside the button / multi select layer
            $scope.externalClickListener = function( e ) {                   

                var targetsArr = element.find( e.target.tagName );
                for (var i = 0; i < targetsArr.length; i++) {                                        
                    if ( e.target == targetsArr[i] ) {
                        return;
                    }
                }

                angular.element( checkBoxLayer.previousSibling ).removeClass( 'buttonClicked' );                    
                angular.element( checkBoxLayer ).removeClass( 'show' );
                angular.element( document ).off( 'click', $scope.externalClickListener ); 
                angular.element( document ).off( 'keydown', $scope.keyboardListener );                
                
                // close callback                
                $timeout( function() {
                    $scope.onClose();
                }, 0 );

                // set focus on button again
                element.children().children()[ 0 ].focus();
            }
   
            // select All / select None / reset buttons
            $scope.select = function( type, e ) {

                var helperIndex = helperItems.indexOf( e.target );
                $scope.tabIndex = helperIndex;

                switch( type.toUpperCase() ) {
                    case 'ALL':
                        angular.forEach( $scope.filteredModel, function( value, key ) {                            
                            if ( typeof value !== 'undefined' && value[ attrs.disableProperty ] !== true ) {                                
                                if ( typeof value[ attrs.groupProperty ] === 'undefined' ) {                                
                                    value[ $scope.tickProperty ] = true;
                                }
                            }
                        });                            
                        $scope.refreshOutputModel();                                    
                        $scope.refreshButton();                                                  
                        $scope.onSelectAll();                                                
                        break;
                    case 'NONE':
                        angular.forEach( $scope.filteredModel, function( value, key ) {
                            if ( typeof value !== 'undefined' && value[ attrs.disableProperty ] !== true ) {                        
                                if ( typeof value[ attrs.groupProperty ] === 'undefined' ) {                                
                                    value[ $scope.tickProperty ] = false;
                                }
                            }
                        });               
                        $scope.refreshOutputModel();                                    
                        $scope.refreshButton();                                                                          
                        $scope.onSelectNone();                        
                        break;
                    case 'RESET':            
                        angular.forEach( $scope.filteredModel, function( value, key ) {                            
                            if ( typeof value[ attrs.groupProperty ] === 'undefined' && typeof value !== 'undefined' && value[ attrs.disableProperty ] !== true ) {                        
                                var temp = value[ $scope.indexProperty ];                                
                                value[ $scope.tickProperty ] = $scope.backUp[ temp ][ $scope.tickProperty ];
                            }
                        });               
                        $scope.refreshOutputModel();                                    
                        $scope.refreshButton();                                                                          
                        $scope.onReset();                        
                        break;
                    case 'CLEAR':
                        $scope.tabIndex = $scope.tabIndex + 1;
                        $scope.onClear();    
                        break;
                    case 'FILTER':                        
                        $scope.tabIndex = helperItems.length - 1;
                        break;
                    default:                        
                }                                                                                 
            }            

            // just to create a random variable name                
            function genRandomString( length ) {                
                var possible    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                var temp        = '';
                for( var i=0; i < length; i++ ) {
                     temp += possible.charAt( Math.floor( Math.random() * possible.length ));
                }
                return temp;
            }

            // count leading spaces
            $scope.prepareGrouping = function() {
                var spacing     = 0;                                                
                angular.forEach( $scope.filteredModel, function( value, key ) {
                    value[ $scope.spacingProperty ] = spacing;                    
                    if ( value[ attrs.groupProperty ] === true ) {
                        spacing+=2;
                    }                    
                    else if ( value[ attrs.groupProperty ] === false ) {
                        spacing-=2;
                    }                 
                });
            }

            // prepare original index
            $scope.prepareIndex = function() {
                var ctr = 0;
                angular.forEach( $scope.filteredModel, function( value, key ) {
                    value[ $scope.indexProperty ] = ctr;
                    ctr++;
                });
            }

            // navigate using up and down arrow
            $scope.keyboardListener = function( e ) { 
                
                var key = e.keyCode ? e.keyCode : e.which;      
                var isNavigationKey = false;                                                

                // ESC key (close)
                if ( key === 27 ) {
                    e.preventDefault();                   
                    e.stopPropagation();
                    $scope.toggleCheckboxes( e );
                }                    
                
                
                // next element ( tab, down & right key )                    
                else if ( key === 40 || key === 39 || ( !e.shiftKey && key == 9 ) ) {                    
                    
                    isNavigationKey = true;
                    prevTabIndex = $scope.tabIndex; 
                    $scope.tabIndex++;                         
                    if ( $scope.tabIndex > formElements.length - 1 ) {
                        $scope.tabIndex = 0;
                        prevTabIndex = formElements.length - 1; 
                    }                                                            
                    while ( formElements[ $scope.tabIndex ].disabled === true ) {
                        $scope.tabIndex++;
                        if ( $scope.tabIndex > formElements.length - 1 ) {
                            $scope.tabIndex = 0;                            
                        }                                                                                    
                        if ( $scope.tabIndex === prevTabIndex ) {
                            break;
                        }
                    }              
                }
                  
                // prev element ( shift+tab, up & left key )
                else if ( key === 38 || key === 37 || ( e.shiftKey && key == 9 ) ) { 
                    isNavigationKey = true;
                    prevTabIndex = $scope.tabIndex; 
                    $scope.tabIndex--;                              
                    if ( $scope.tabIndex < 0 ) {
                        $scope.tabIndex = formElements.length - 1;
                        prevTabIndex = 0;
                    }                                         
                    while ( formElements[ $scope.tabIndex ].disabled === true ) {                        
                        $scope.tabIndex--;
                        if ( $scope.tabIndex === prevTabIndex ) {
                            break;
                        }                                            
                        if ( $scope.tabIndex < 0 ) {
                            $scope.tabIndex = formElements.length - 1;
                        }                             
                    }                                                     
                }                    

                if ( isNavigationKey === true ) {                                         
                    
                    e.preventDefault();

                    // set focus on the checkbox                    
                    formElements[ $scope.tabIndex ].focus();    
                    var actEl = document.activeElement;                     
                    
                    if ( actEl.type.toUpperCase() === 'CHECKBOX' ) {                                                   
                        $scope.setFocusStyle( $scope.tabIndex );
                        $scope.removeFocusStyle( prevTabIndex );
                    }                    
                    else {
                        $scope.removeFocusStyle( prevTabIndex );
                        $scope.removeFocusStyle( helperItemsLength );
                        $scope.removeFocusStyle( formElements.length - 1 );
                    } 
                }                

                isNavigationKey = false;
            }

            // set (add) CSS style on selected row
            $scope.setFocusStyle = function( tabIndex ) {                                
                angular.element( formElements[ tabIndex ] ).parent().parent().parent().addClass( 'multiSelectFocus' );                        
            }

            // remove CSS style on selected row
            $scope.removeFocusStyle = function( tabIndex ) {                
                angular.element( formElements[ tabIndex ] ).parent().parent().parent().removeClass( 'multiSelectFocus' );
            }

            /*********************
             *********************             
             *
             * 1) Initializations
             *
             *********************
             *********************/

            // attrs to $scope - attrs-$scope - attrs - $scope
            // Copy some properties that will be used on the template. They need to be in the $scope.
            $scope.groupProperty    = attrs.groupProperty;   
            $scope.tickProperty     = attrs.tickProperty;
            $scope.directiveId      = attrs.directiveId;
            
            // Unfortunately I need to add these grouping properties into the input model
            var tempStr = genRandomString( 5 );
            $scope.indexProperty = 'idx_' + tempStr;
            $scope.spacingProperty = 'spc_' + tempStr;         

            // set orientation css            
            if ( typeof attrs.orientation !== 'undefined' ) {

                if ( attrs.orientation.toUpperCase() === 'HORIZONTAL' ) {                    
                    $scope.orientationH = true;
                    $scope.orientationV = false;
                }
                else 
                {
                    $scope.orientationH = false;
                    $scope.orientationV = true;
                }
            }            

            // get elements required for DOM operation
            checkBoxLayer = element.children().children().next()[0];

            // set max-height property if provided
            if ( typeof attrs.maxHeight !== 'undefined' ) {                
                var layer = element.children().children().children()[0];
                angular.element( layer ).attr( "style", "height:" + attrs.maxHeight + "; overflow-y:scroll;" );                                
            }

            // some flags for easier checking            
            for ( var property in $scope.helperStatus ) {
                if ( $scope.helperStatus.hasOwnProperty( property )) {                    
                    if ( 
                        typeof attrs.helperElements !== 'undefined' 
                        && attrs.helperElements.toUpperCase().indexOf( property.toUpperCase() ) === -1 
                    ) {
                        $scope.helperStatus[ property ] = false;
                    }
                }
            }
            if ( typeof attrs.selectionMode !== 'undefined' && attrs.selectionMode.toUpperCase() === 'SINGLE' )  {
                $scope.helperStatus[ 'all' ] = false;
                $scope.helperStatus[ 'none' ] = false;
            }

            // helper button icons.. I guess you can use html tag here if you want to. 
            $scope.icon        = {};            
            $scope.icon.selectAll  = '&#10003;';    // a tick icon
            $scope.icon.selectNone = '&times;';     // x icon
            $scope.icon.reset      = '&#8630;';     // undo icon            
            // this one is for the selected items
            $scope.icon.tickMark   = '&#10003;';    // a tick icon 

            // configurable button labels                       
            if ( typeof attrs.translation !== 'undefined' ) {
                $scope.lang.selectAll       = $sce.trustAsHtml( $scope.icon.selectAll  + '&nbsp;&nbsp;' + $scope.translation.selectAll );
                $scope.lang.selectNone      = $sce.trustAsHtml( $scope.icon.selectNone + '&nbsp;&nbsp;' + $scope.translation.selectNone );
                $scope.lang.reset           = $sce.trustAsHtml( $scope.icon.reset      + '&nbsp;&nbsp;' + $scope.translation.reset );
                $scope.lang.search          = $scope.translation.search;                
                $scope.lang.nothingSelected = $sce.trustAsHtml( $scope.translation.nothingSelected );                
            }
            else {
                $scope.lang.selectAll       = $sce.trustAsHtml( $scope.icon.selectAll  + '&nbsp;&nbsp;Select All' );                
                $scope.lang.selectNone      = $sce.trustAsHtml( $scope.icon.selectNone + '&nbsp;&nbsp;Select None' );
                $scope.lang.reset           = $sce.trustAsHtml( $scope.icon.reset      + '&nbsp;&nbsp;Reset' );
                $scope.lang.search          = 'Search...';
                $scope.lang.nothingSelected = 'None Selected';                
            }
            $scope.icon.tickMark = $sce.trustAsHtml( $scope.icon.tickMark );
                
            // min length of keyword to trigger the filter function
            if ( typeof attrs.MinSearchLength !== 'undefined' && parseInt( attrs.MinSearchLength ) > 0 ) {
                vMinSearchLength = Math.floor( parseInt( attrs.MinSearchLength ) );
            }

            /*******************************************************
             *******************************************************
             *
             * 2) Logic starts here, initiated by watch 1 & watch 2
             *
             *******************************************************
             *******************************************************/
            
            // watch1, for changes in input model property
            // updates multi-select when user select/deselect a single checkbox programatically
            // https://github.com/isteven/angular-multi-select/issues/8            
            $scope.$watch( 'inputModel' , function( newVal ) {                                 
                if ( newVal ) {                            
                    $scope.refreshOutputModel();                                    
                    $scope.refreshButton();                                                  
                }
            }, true );
            
            // watch2 for changes in input model as a whole
            // this on updates the multi-select when a user load a whole new input-model. We also update the $scope.backUp variable
            $scope.$watch( 'inputModel' , function( newVal ) {  
                if ( newVal ) {
                    $scope.backUp = angular.copy( $scope.inputModel );    
                    $scope.updateFilter();
                    $scope.prepareGrouping();
                    $scope.prepareIndex();                                                              
                    $scope.refreshOutputModel();                
                    $scope.refreshButton();                                                                                                                 
                }
            });                        

            // watch for changes in directive state (disabled or enabled)
            $scope.$watch( 'isDisabled' , function( newVal ) {         
                $scope.isDisabled = newVal;                               
            });            
            
            // this is for touch enabled devices. We don't want to hide checkboxes on scroll. 
            var onTouchStart = function( e ) { 
            	$scope.$apply( function() {
            		$scope.scrolled = false;
            	}); 
            };
            angular.element( document ).bind( 'touchstart', onTouchStart);
            var onTouchMove = function( e ) { 
            	$scope.$apply( function() {
            		$scope.scrolled = true;                
            	});
            };
            angular.element( document ).bind( 'touchmove', onTouchMove);            

            // unbind document events to prevent memory leaks
            $scope.$on( '$destroy', function () {
			    angular.element( document ).unbind( 'touchstart', onTouchStart);
            	angular.element( document ).unbind( 'touchmove', onTouchMove);
            });
        }
    }
}]).run( [ '$templateCache' , function( $templateCache ) {
    var template = 
        '<span class="multiSelect inlineBlock">' +
            // main button
            '<button id="{{directiveId}}" type="button"' +                
                'ng-click="toggleCheckboxes( $event ); refreshSelectedItems(); refreshButton(); prepareGrouping; prepareIndex();"' +
                'ng-bind-html="varButtonLabel"' +
                'ng-disabled="disable-button"' +
            '>' +
            '</button>' +
            // overlay layer
            '<div class="checkboxLayer">' +
                // container of the helper elements
                '<div class="helperContainer" ng-if="helperStatus.filter || helperStatus.all || helperStatus.none || helperStatus.reset ">' +
                    // container of the first 3 buttons, select all, none and reset
                    '<div class="line" ng-if="helperStatus.all || helperStatus.none || helperStatus.reset ">' +
                        // select all
                        '<button type="button" class="helperButton"' +
                            'ng-disabled="isDisabled"' + 
                            'ng-if="helperStatus.all"' +
                            'ng-click="select( \'all\', $event );"' +
                            'ng-bind-html="lang.selectAll">' +
                        '</button>'+
                        // select none
                        '<button type="button" class="helperButton"' +
                            'ng-disabled="isDisabled"' + 
                            'ng-if="helperStatus.none"' +
                            'ng-click="select( \'none\', $event );"' +
                            'ng-bind-html="lang.selectNone">' +
                        '</button>'+
                        // reset
                        '<button type="button" class="helperButton reset"' +
                            'ng-disabled="isDisabled"' + 
                            'ng-if="helperStatus.reset"' +
                            'ng-click="select( \'reset\', $event );"' +
                            'ng-bind-html="lang.reset">'+
                        '</button>' +
                    '</div>' +
                    // the search box
                    '<div class="line" style="position:relative" ng-if="helperStatus.filter">'+
                        // textfield                
                        '<input placeholder="{{lang.search}}" type="text"' +
                            'ng-click="select( \'filter\', $event )" '+
                            'ng-model="inputLabel.labelFilter" '+
                            'ng-change="searchChanged()" class="inputFilter"'+
                            '/>'+
                        // clear button
                        '<button type="button" class="clearButton" ng-click="clearClicked( $event )" >×</button> '+
                    '</div> '+
                '</div> '+
                // selection items
                '<div class="checkBoxContainer">'+
                    '<div '+
                        'ng-repeat="item in filteredModel | filter:removeGroupEndMarker" class="multiSelectItem"'+
                        'ng-class="{selected: item[ tickProperty ], horizontal: orientationH, vertical: orientationV, multiSelectGroup:item[ groupProperty ], disabled:itemIsDisabled( item )}"'+
                        'ng-click="syncItems( item, $event, $index );" '+
                        'ng-mouseleave="removeFocusStyle( tabIndex );"> '+
                        // this is the spacing for grouped items
                        '<div class="acol" ng-if="item[ spacingProperty ] > 0" ng-repeat="i in numberToArray( item[ spacingProperty ] ) track by $index">'+                        
                    '</div>  '+        
                    '<div class="acol">'+
                        '<label>'+                                
                            // input, so that it can accept focus on keyboard click
                            '<input class="checkbox focusable" type="checkbox" '+
                                'ng-disabled="itemIsDisabled( item )" '+
                                'ng-checked="item[ tickProperty ]" '+
                                'ng-click="syncItems( item, $event, $index )" />'+
                            // item label using ng-bind-hteml
                            '<span '+
                                'ng-class="{disabled:itemIsDisabled( item )}" '+
                                'ng-bind-html="writeLabel( item, \'itemLabel\' )">'+
                            '</span>'+
                        '</label>'+
                    '</div>'+
                    // the tick/check mark
                    '<span class="tickMark" ng-if="item[ groupProperty ] !== true && item[ tickProperty ] === true" ng-bind-html="icon.tickMark"></span>'+
                '</div>'+
            '</div>'+
        '</div>'+
    '</span>';
	$templateCache.put( 'isteven-multi-select.htm' , template );
}]); 