/**
 * angular-simple-rbac
 * This module provides simple role based access control (RBAC) for angular apps.
 * @version v0.0.1 - 2016-03-22
 * @link http://www.patricktoerner.com
 * @author Patrick Toerner <patrick.toerner@gmail.com>
 * @license MIT License, http://www.opensource.org/licenses/MIT
 */

(function () {
  'use strict';

  var rbac = angular.module('angular-simple-rbac', ['ui.router','LocalStorageModule']);

  rbac.run(['$rootScope', '$state', '$q', 'rbac','RbacAuthorization', function ($rootScope, $state, $q, rbac, RbacAuthorization) {

    $rootScope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams, options) {

      if(!isAuthorizationFinished() && areSetStatePermissions(toState)){
        event.preventDefault(); // Prevent state change, will manually do it later
        setStateAuthorizationStatus(true);
        authorizeForState()
      }

      /**
       * Checks if state is qualified to be rbac'ed
       * @private
       *
       * @returns {boolean}
       */
      function areSetStatePermissions(state) {
        return angular.isDefined(state.data) && angular.isDefined(state.data.rbac);
      }

      /**
       * Sets internal state `$$finishedAuthorization` variable to prevent looping
       * @private
       *
       * @param status {boolean} When true authorization has been already preceded
       */
      function setStateAuthorizationStatus(status) {
        angular.extend(toState, {'$$isAuthorizationFinished': status});
      }

      /**
       * Checks if state has been already checked for authorization
       * @private
       *
       * @returns {boolean}
       */
      function isAuthorizationFinished() {
        return toState.$$isAuthorizationFinished || false;
      }

      /**
       * Determines if user has proper authorizations for the state
       * and redirects the user if necessary
       * @private
       *
       */
      function authorizeForState() {
        if( RbacAuthorization.meetsRolesAndPermissionsRequirements(toState.data.rbac) ){
          $state
            .go(toState.name, toParams, {notify: false})
            .then(function () {
              $rootScope.$broadcast('$stateChangeSuccess', toState, toParams, fromState, fromParams);
            });
        }else{
          $rootScope.$broadcast('rbac.$stateChangeDenied', toState, toParams, options);
          handleRedirect()
        }
        setStateAuthorizationStatus(false)
      }

      /**
       * Called when a user does not have permissions to view a state
       * Redirects to redirectTo if available in the state info
       * @private
       *
       * @returns {boolean}
       */
      function handleRedirect(){
        if(angular.isDefined(toState.data.rbac.redirectTo)){
          $state.go(toState.data.rbac.redirectTo);
        }
      }

  
    });
  }]);
  
}());


(function () {
  'use strict';

  angular
    .module('angular-simple-rbac')
    .service('rbac', ['$q','$log','$rootScope', 
    function ($q,$log,$rootScope) {
      var roles = []
      var permissions = []
      var defer = $q.defer();
      var promises = []

      /**
      *
      * Accepts roles as ['admin','member']
      * or as [{name:'admin'},{name:'member'}]
      *
      * Also accepts permissions as an array in the role object:
      * [{
      *   name:'admin', 
      *   permissions: ['view_dashboard','ride_unicorn']
      *  },{
      *   name:'member',
      *   permissions: [{name:'have_friends'},{name:'cry_rivers'}]
      * }]
      *
      **/
      this.setRoles = function(roles){
        this.clearRolesAndPermissions()
        return this.addRoles(roles)
      }

      /**
      *
      * Accepts permissions as ['view_dashboard','ride_unicorn']
      * or as [{name:'have_friends'},{name:'cry_rivers'}]
      *
      **/
      this.setPermissions = function(permissions){
        this.clearPermissions()
        return this.addPermissions(permissions)
      }

      /**
      *
      * This function can be used to add roles without removes current roles and permissions
      *
      **/
      this.addRoles = function(roles){
        promises = []
        parseRoleArray(roles)
        $q.all(promises).then(rolesOrPermissionsUpdated)
        return $q.all(promises);
      }

      /**
      *
      * This function can be used to add permissions without removes current permission
      *
      **/
      this.addPermissions = function(permissions){
        promises = []
        parsePermissionArray(permissions)
        $q.all(promises).then(rolesOrPermissionsUpdated)
        return $q.all(promises);
      }

      /**
      *
      * Used to signal the directives to update
      *
      * */
      function rolesOrPermissionsUpdated(){
        $rootScope.$broadcast('rbac.RolesPermissionsUpdate')
      }

      // Determines format of roles and delegates work
      function parseRoleArray(roles){
        defer = $q.defer();
        for (var i = roles.length - 1; i >= 0; i--) {
          if(typeof roles[i] === "string")
            setRole(roles[i])
          else if(typeof roles[i] === "object")
            parseRoleObject(roles[i])
          else
            throwError('roleObjectWrongType','error')
        }
        return defer.promise;
      }

      // adds roleName to roles array
      function setRole(roleName){
        pushUnique(roles,roleName)
      }

      // adds permissions name to permissions array
      function setPermission(permissionName){
        pushUnique(permissions,permissionName)
      }

      // checks if array contains item before pushing it
      // This also resolves promises, so addRoles and addPermissions is aware when one is added
      function pushUnique(array, item){
        if(array.indexOf(item) == -1){
          array.push(item)
          promises.push( defer.resolve() )
        }
      }

      // break down the roleObjects and sends it off to setRole
      // Also determines if permissions is a child and send it off to parsePermissionArray if so
      function parseRoleObject(roleObject){
        if(angular.isDefined(roleObject.name)){
          setRole(roleObject.name)
          if(angular.isDefined( roleObject.permissions ))
            parsePermissionArray( roleObject.permissions )
        }else{
          throwError('roleObjectWrongType','error')
        }
      }

      // Determines format of permissions and delegates work
      function parsePermissionArray(permissionArray){
        defer = $q.defer();
        for (var i = permissionArray.length - 1; i >= 0; i--) {
          if(typeof permissionArray[i] === "string")
            setPermission(permissionArray[i])
          else if(typeof permissionArray[i] === "object")
            parsePermissionObject(permissionArray[i])
          else
            throwError('permissionObjectWrongType','error')
        }
        return defer.promise;
      }

      // break down the permissionObjects and sends it off to setRole
      function parsePermissionObject(permissionObject){
        if(angular.isDefined(permissionObject.name)){
          setPermission(permissionObject.name)
        }else{
          throwError('permissionObjectWrongType','error')
        }
      }

      // removes all roles
      // @public
      this.clearRoles = function(){
        roles = []
      }

      // removes all permissions
      // @public
      this.clearPermissions = function(){
        permissions = []
      }

      // removes all permissions and roles
      // @public
      this.clearRolesAndPermissions = function(){
        this.clearRoles()
        this.clearPermissions()
      }

      // returns roles
      // @public
      this.getRoles = function(){
        return roles
      }

      // returns permissions
      // @public
      this.getPermissions = function(){
        return permissions
      }

      // returns merged array of roles and permissions
      // @public
      this.getRolesAndPermissions = function(){
        return roles.concat(permissions)
      }

      var errorMessages = {
        'roleObjectWrongType': 'Role object error: The array provided to rbac.setRoles is in the incorrect format. Please refer to documentation.',
        'permissionObjectWrongType': 'Permission object error: The array provided to rbac.setRoles is in the incorrect format. Please refer to documentation.',
      }

      // Displays error in console
      function throwError(errorKey, errorType){
        $log[errorType]( errorMessages[errorKey] )
      }

    }]);

}());



(function () {
  'use strict';

  angular
    .module('angular-simple-rbac')
    .service('RbacAuthorization', ['rbac',
    function (rbac) {

      this.meetsRolesAndPermissionsRequirements = function(rbacArray){
        if(
          this.meetsRoleOnlyRequirement(rbacArray) &&
          this.meetsRoleExceptRequirement (rbacArray) &&
          this.meetsPermissionOnlyRequirement(rbacArray) &&
          this.meetsPermissionExceptRequirement(rbacArray) &&
          this.meetsOnlyRequirement(rbacArray) &&
          this.meetsExceptRequirement(rbacArray)
        )
          return true
        else
          return false
      }

      this.hasRole = function(role){
        return this.meetsRoleOnlyRequirement([role])
      }

      this.meetsRoleOnlyRequirement = function(rbacArray){
        if( angular.isDefined(rbacArray.roleOnly) )
          return this.arraysOverlap( [].concat(rbacArray.roleOnly), rbac.getRoles() )
        else return true
      }

      this.meetsRoleExceptRequirement = function(rbacArray){
        if( angular.isDefined(rbacArray.roleExcept) )
          return !this.arraysOverlap( [].concat(rbacArray.roleExcept), rbac.getRoles() )
        else return true
      }

      this.meetsPermissionOnlyRequirement = function(rbacArray){
        if( angular.isDefined(rbacArray.permissionOnly) )
          return this.arraysOverlap( [].concat(rbacArray.permissionOnly), rbac.getPermissions() )
        else return true
      }

      this.meetsPermissionExceptRequirement = function(rbacArray){
        if( angular.isDefined(rbacArray.permissionExcept) )
          return !this.arraysOverlap( [].concat(rbacArray.permissionExcept), rbac.getPermissions() )
        else return true
      }

      this.meetsOnlyRequirement = function(rbacArray){
        if( angular.isDefined(rbacArray.only) )
          return this.arraysOverlap( [].concat(rbacArray.only), rbac.getRolesAndPermissions() )
        else return true
      }

      this.meetsExceptRequirement = function(rbacArray){
        if( angular.isDefined(rbacArray.except) )
          return !this.arraysOverlap( [].concat(rbacArray.except), rbac.getRolesAndPermissions() )
        else return true
      }


      this.arraysOverlap = function(array1, array2){
        for (var i = 0; i < array1.length; i++) {
          if( array2.indexOf(array1[i]) != -1 )
            return true               
        }
        return false;
      }

    }]);

}());





(function () {
  'use strict';
  angular
    .module('angular-simple-rbac')
    .directive('rbac', ['$log', 'rbac', 'rbacUtilities', 'RbacAuthorization',
    function ($log, rbac, rbacUtilities, RbacAuthorization) {
      return {
        restrict: 'A',
        scope: true,
        bindToController: {
          roleOnly: '=?rbacRoleOnly', // must contain any of these roles
          roleExcept: '=?rbacRoleExcept', // must contain none of these roles
          permissionOnly: '=?rbacPermissionOnly', // must contain any of these permissions
          permissionExcept: '=?rbacPermissionExcept', // must contain none of these permission
          only: '=?rbacOnly', // must contain any of these roles or permissions
          except: '=?rbacExcept', // must contain none of these roles or permissions
        },
        controllerAs: 'rbac',
        controller: ['$scope', '$element', function ($scope, $element) {
          // bindToController variables get passed into $scope.rbac
          // $scope.rbac gets sent to Authorization to determine if the element should remain visible or not
          $scope.update = function(){
            RbacAuthorization.meetsRolesAndPermissionsRequirements($scope.rbac) ? rbacUtilities.showElement($element) : rbacUtilities.hideElement($element)
          }

          // Emitted when roles or permissions are changed
          $scope.$on('rbac.RolesPermissionsUpdate', function(event, args){
            $scope.update()
          })

          $scope.update();

        }]
      };
    }]);
}());


(function () {
  'use strict';

  /**
   * Basic Utilities used for modifying the DOM element
   *
   * enableElement and disableElement are not currently implemented
   */
  angular
    .module('angular-simple-rbac')
    .constant('rbacUtilities', {
      enableElement: function ($element) {
        $element.removeAttr('disabled');
      },
      disableElement: function ($element) {
        $element.attr('disabled', 'disabled');
      },
      showElement: function ($element) {
        $element.removeClass('ng-hide');
      },
      hideElement: function ($element) {
        $element.addClass('ng-hide');
      }
    });
}());
