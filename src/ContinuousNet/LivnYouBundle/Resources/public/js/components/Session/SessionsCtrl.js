'use strict';

/**
 * Controller for Sessions List
 */

app.controller('SessionsCtrl', ['$scope', '$rootScope', '$stateParams', '$location', '$sce', '$timeout', '$filter', 'ngTableParams', '$state', '$q', '$interpolate', '$localStorage', 'toaster', 'SweetAlert', '$usersDataFactory', '$sessionsDataFactory',
function($scope, $rootScope, $stateParams, $location, $sce, $timeout, $filter, ngTableParams, $state, $q, $interpolate, $localStorage, toaster, SweetAlert, $usersDataFactory, $sessionsDataFactory) {


    $scope.booleanOptions = [{
        id: '',
        title: $filter('translate')('content.common.ALL'),
        css: ''
     }, {
        id: '1',
        title: $filter('translate')('content.common.YES'),
        css: 'success'
     }, {
        id: '0',
        title: $filter('translate')('content.common.NO'),
        css: 'danger'
    }];

    $scope.isLoading = false;
    $scope.locale = (angular.isDefined($localStorage.language))?$localStorage.language:'en';
    $scope.showFieldsMenu = false;

    $scope.users = [];
    $scope.usersLoaded = false;

    $scope.getUsers = function() {
        $scope.usersLoaded = true;
        if ($scope.users.length == 0) {
            $scope.users.push({id: '', title: $filter('translate')('content.form.messages.SELECTUSER')});
            var def = $q.defer();
            $usersDataFactory.query({locale: $localStorage.language, offset: 0, limit: 10000, 'filters[user.type]': 'Administrator', 'order_by[user.id]': 'desc'}).$promise.then(function(data) {
                $timeout(function(){
                    if (data.results.length > 0) {
                        for (var i in data.results) {
                            $scope.users.push({
                                id: data.results[i].id,
                                title: data.results[i].username
                            });
                        }
                        def.resolve($scope.users);
                    }
                });
            });
            return def;
        } else {
            return $scope.users;
        }
    };

    $scope.getUsers();


    $scope.textValue = function($scope, row) {
        return $scope.$eval('row.' + this.field);
    };

    $scope.trusted = {};

    $scope.linkValue = function($scope, row) {
        var value = row[this.field];
        if (value == null || typeof value == 'undefined') {
            return '';
        }
        var displayFields = this.displayField.split(' ');
        var displayText = ''
        for (var i in displayFields) {
            if (angular.isDefined(value[displayFields[i]])) {
                displayText += value[displayFields[i]] + ' ';
            }
        }
        var html = '';
        if ($rootScope.checkStatePermission(this.state)) {
            html += '<a ui-sref="'+this.state+'({id: ' + value.id + '})">';
            html += displayText.trim();
            html += '</a>';
        } else {
            html += displayText.trim();
        }
        return $scope.trusted[html] || ($scope.trusted[html] = $sce.trustAsHtml(html));
    };

    $scope.evaluatedValue = function($scope, row) {
        var value = $scope.$eval('row.' + this.field, {row: row});
        if (value == null || typeof value == 'undefined') {
            return '';
        }
        var evaluatedValue = $scope.$eval('\'' + value + '\' | ' + this.valueFormatter);
        if (this.field == 'birth_date') {
            evaluatedValue += ' ('+$scope.$eval('\'' + value + '\' | age')+')';
        }
        return evaluatedValue;
    };

    $scope.interpolatedValue = function($scope, row) {
        return this.interpolateExpr({
            row: row,
            field: this.field,
            title: this.title,
            sortable: this.sortable,
            filter: this.filter,
            show: this.show
        });
    };

    $scope.setParamValue = function(param, newValue) {
        $localStorage.sessionsParams[param] = newValue;
        $stateParams[param] = newValue;
        $location.search(param, JSON.stringify(newValue));
    };

    $scope.getParamValue = function(param, defaultValue) {
        if (!angular.isDefined($localStorage.sessionsParams)) {
           $localStorage.sessionsParams = {};
        }
        if (angular.isDefined($stateParams[param]) && typeof $stateParams[param] == 'string' && JSON.parse($stateParams[param]) != null) {
            return JSON.parse($stateParams[param]);
        } else if (angular.isDefined($stateParams[param]) && $stateParams[param] != null) {
            return $stateParams[param];
        } else if (angular.isDefined($location.search()[param]) && JSON.parse($location.search()[param]) != null) {
            return JSON.parse($location.search()[param]);
        } else if (angular.isDefined($localStorage.sessionsParams[param]) && $localStorage.sessionsParams[param] != null) {
            return $localStorage.sessionsParams[param];
        } else {
            $localStorage.sessionsParams[param] = defaultValue;
            return defaultValue;
        }
    };

    $scope.setCols = function() {
        $scope.cols = [
            { field: 'id', title: $filter('translate')('content.list.fields.ID'), sortable: 'session.id', filter: { 'session.id': 'number' }, show: ($scope.getParamValue('id_show_filed', true) && true), displayInList: true, getValue: $scope.textValue },
            { field: 'ip', title: $filter('translate')('content.list.fields.IP'), sortable: 'session.ip', filter: { 'session.ip': 'text' }, show: ($scope.getParamValue('ip_show_filed', true) && true), displayInList: true, getValue: $scope.textValue },
            { field: 'is_valid', title: $filter('translate')('content.list.fields.ISVALID'), sortable: 'session.isValid', filter: { 'session.isValid': 'select' }, show: ($scope.getParamValue('is_valid_show_filed', true) && true), displayInList: true, getValue: $scope.interpolatedValue, filterData : $scope.booleanOptions, interpolateExpr: $interpolate('<span my-boolean="[[ row.is_valid ]]"></span>') },
            { field: 'user_agent', title: $filter('translate')('content.list.fields.USERAGENT'), sortable: 'session.userAgent', filter: { 'session.userAgent': 'text' }, show: ($scope.getParamValue('user_agent_show_filed', false) && true), displayInList: true, getValue: $scope.textValue },
            { field: 'creator_user', 'class': 'has_one', title: $filter('translate')('content.list.fields.CREATORUSER'), sortable: 'creator_user.username', filter: { 'session.creatorUser': 'select' }, getValue: $scope.linkValue, filterData: $scope.getUsers(), show: ($scope.getParamValue('creator_user_id_show_filed', true) && true), displayInList: true, displayField: 'username', state: 'app.accesscontrol.usersdetails' },
            { field: 'created_at', title: $filter('translate')('content.list.fields.CREATEDAT'), sortable: 'session.createdAt', filter: { 'session.createdAt': 'text' }, show: ($scope.getParamValue('created_at_show_filed', true) && true), displayInList: true, getValue: $scope.evaluatedValue, valueFormatter: 'date:\''+$filter('translate')('formats.DATETIME')+'\''},
            { field: 'modifier_user', 'class': 'has_one', title: $filter('translate')('content.list.fields.MODIFIERUSER'), sortable: 'modifier_user.username', filter: { 'session.modifierUser': 'select' }, getValue: $scope.linkValue, filterData: $scope.getUsers(), show: ($scope.getParamValue('modifier_user_id_show_filed', true) && true), displayInList: true, displayField: 'username', state: 'app.accesscontrol.usersdetails' },
            { field: 'modified_at', title: $filter('translate')('content.list.fields.MODIFIEDAT'), sortable: 'session.modifiedAt', filter: { 'session.modifiedAt': 'text' }, show: ($scope.getParamValue('modified_at_show_filed', false) && true), displayInList: true, getValue: $scope.evaluatedValue, valueFormatter: 'date:\''+$filter('translate')('formats.DATETIME')+'\''},
            { title: $filter('translate')('content.common.ACTIONS'), show: true, displayInList: true, getValue: $scope.interpolatedValue, interpolateExpr: $interpolate(''
            +'<div class="btn-group pull-right">'
            +'<button type="button" class="btn btn-success" tooltip-placement="top" uib-tooltip="'+$filter('translate')('content.common.EDIT')+'" ng-click="edit(row)"><i class="ti-pencil-alt"></i></button>'
            +'<button type="button" class="btn btn-warning" tooltip-placement="top" uib-tooltip="'+$filter('translate')('content.common.SHOWDETAILS')+'" ng-click="details(row)"><i class="ti-clipboard"></i></button>'
            +'<button type="button" class="btn btn-danger" tooltip-placement="top" uib-tooltip="'+$filter('translate')('content.common.REMOVE')+'" ng-click="delete(row)"><i class="ti-trash"></i></button>'
            +'</div>') }
        ];
    };

    $scope.setCols();

    $scope.$on('languageChange', function(event, locale) {
        $timeout(function(){;
            $scope.setCols();
        }, 500);
    });

    $scope.isFiltersVisible = $scope.getParamValue('sessionsIsFiltersVisible', false);
    $scope.$watch('isFiltersVisible', function() {
        $scope.setParamValue('sessionsIsFiltersVisible', $scope.isFiltersVisible);
    });

    $scope.page = 1; // show first page
    $scope.page = $scope.getParamValue('sessionsPage', $scope.page);
    $scope.count = 50; // count per page
    $scope.count = $scope.getParamValue('sessionsCount', $scope.count);
    $scope.sorting = {'session.createdAt': 'desc'};
    $scope.sorting = $scope.getParamValue('sessionsSorting', $scope.sorting);
    $scope.filter = {
    };
    $scope.filter = $scope.getParamValue('sessionsFilter', $scope.filter);
    $scope.setParamValue('sessionsPage', $scope.page);
    $scope.setParamValue('sessionsCount', $scope.count);
    $scope.setParamValue('sessionsSorting', $scope.sorting);
    $scope.setParamValue('sessionsFilter', $scope.filter);
    $scope.tableParams = {
        page: $scope.page,
        count: $scope.count,
        sorting: $scope.sorting,
        filter: $scope.filter
    };
    $scope.tableParams = new ngTableParams($scope.tableParams, {
        getData: function ($defer, params) {
            var current = params.page();
            var offset = (current - 1) * params.count();
            var limit = params.count();
            var order_by = params.sorting();
            var filters = params.filter();
            $scope.setParamValue('sessionsIsFiltersVisible', $scope.isFiltersVisible);
            $scope.setParamValue('sessionsPage', current);
            $scope.setParamValue('sessionsCount', limit);
            $scope.setParamValue('sessionsSorting', order_by);
            $scope.setParamValue('sessionsFilter', filters);
            var http_params = {
                locale: $localStorage.language,
                offset: offset,
                limit: limit
            };
            for (var field in order_by) {
                http_params['order_by['+field+']'] = order_by[field];
            }
            if (filters.length > 0) {
                http_params.offset = 0;
            }
            for (var field in filters) {
                if (filters[field] != null || filters[field] != '') {
                    http_params['filters['+field+']'] = filters[field];
                }
            }
            $scope.isLoading = true;
            return $sessionsDataFactory.query(http_params).$promise.then(function(data) {
                $scope.isLoading = false;
                params.total(data.inlineCount);
                return data.results;
            });
        }
    });

    $scope.delete = function(row) {
        SweetAlert.swal({
            title: $filter('translate')('content.common.AREYOUSURE'),
            text: $filter('translate')('content.list.YOUWILLNOTBEABLETORECOVERSESSION'),
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: $filter('translate')('content.common.YESDELETE'),
            cancelButtonText: $filter('translate')('content.common.NOCANCEL'),
            closeOnConfirm: false,
            closeOnCancel: false,
            showLoaderOnConfirm: true
        }, function (isConfirm) {
            if (isConfirm) {
                $sessionsDataFactory.remove({id: row.id}).$promise.then(function(data) {
                    SweetAlert.swal({
                        title: $filter('translate')('content.common.DELETED'), 
                        text: $filter('translate')('content.list.SESSIONDELETED'), 
                        type: 'success',
                        confirmButtonColor: '#007AFF'
                    });
                    $scope.tableParams.reload();
                }, function(error) {
                    SweetAlert.swal({
                        title: $filter('translate')('content.common.ERROR'), 
                        text: $filter('translate')('content.list.SESSIONNOTDELETED'), 
                        type: 'warning',
                        confirmButtonColor: '#007AFF'
                    });
                });
            } else {
                SweetAlert.swal({
                    title: $filter('translate')('content.common.CANCELLED'), 
                    text: $filter('translate')('content.list.SESSIONNOTDELETED'), 
                    type: 'error',
                    confirmButtonColor: '#007AFF'
                });
            }
        });
    };

    $scope.add = function() {
        $state.go('app.accesscontrol.sessionsnew');
    };

    $scope.edit = function(row) {
        $state.go('app.accesscontrol.sessionsedit', {id: row.id});
    };

    $scope.details = function(row) {
        $state.go('app.accesscontrol.sessionsdetails', {id: row.id});
    };
}]);

