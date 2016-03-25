# Simple Angular RBAC
*Role Based Access Control* for angular in its simplest form.

## Dependencies
- [Angular 1.4+](https://github.com/angular/angular) as MV* framework
- [UI-Router](https://github.com/angular-ui/ui-router) as your router module

## What it does
- Allows for most basic role and permission setting
- Shows/hides DOM elements based on roles/permissions
- Redirect state changes if role or permission is not met for a route

# Installation
## Bower
```
bower install angular-simple-rbac --save
```
## Include in your dependencies
```javascript
angular.module('yourModule', [..., 'ui.router', 'angular-simple-rbac',  ...]);
```
**Important!** Angular permission is using ui-router state decoration to be able to inherit permissions/roles from parent states. So make sure that permission dependency is included just after ui-router as in example above.

# Defining Roles and Permissions
## Roles
```javascript
// You want to give different hax0rs different permissions.
// Follow along and let's do it!
//
// Let's assume we make an Ajax call to get the roles/permissions

angular
  .module('L33tH4x0rModule', ['angular-simple-rbac'])
  .run(function (rbac, User) {

  	// This fake function returns roles from the fake api
  	User.getRoles()
  		.then(function(roles){

  			// This function sets the roles
  			rbac.setRoles(roles)
  		})

  });
```
That's it. The roles and permissions are set. You can just set permissions, but using `rbac.setPermissions()` instead of `rbac.setRoles`

But what format should roles be in? Glad you asked...

### Formats accepted by setRoles and setPermissions
```javascript
// Basic arrays
var roles = ['admin','member']
var permissions = ['view_dashboard','ride_unicorn']
```
or
```javascript
// Arrays of objects with 'name' attribute
var roles = [{name:'admin'},{name:'member'}]
var permissions = [{name:'have_friends'},{name:'cry_rivers'}]
```
or 
```javascript
// rbac.setRoles also accepts an array of role objects, with the permissions attribute.
// The permissions attribute is in the same formats as above
var rolesAndPermissions = [{
  name:'admin', 
  permissions: ['view_dashboard','ride_unicorn']
 },{
  name:'member',
  permissions: [{name:'have_friends'},{name:'cry_rivers'}]
}]
```
Pretty sweet, huh? Well, I think so.

Now, how do I use the roles and permissions now that they are set? Glad you asked...

# Using to Control Access
After roles are set, we can now use them to control what users can see.

## Showing and hiding dom elements
You can now show or hide any element based on a user's roles and permissions:
```html
<div rbac rbac-only=['admin','l33t-h4x0r']>Only show this div to admins and the best hackers in the world</div>
```
Only those with roles or permissions of 'admin' or 'l33t-h4x0r' can see the div. It is hidden otherwise

Here are all of the possible ways to limit access to DOM elements
```html
<div rbac
 	<!-- Only users with role of 'admin' or 'l33t-h4x0r' can see this div -->
	rbac-role-only=['admin','l33t-h4x0r']

	<!-- Only users without roles of 'admin' and 'l33t-h4x0r' can see this div -->
	rbac-role-except=['admin','l33t-h4x0r']

	<!-- Only users with permission of 'view_dashboard' or 'ride_unicorn' can see this div -->
	rbac-permission-only=['view_dashboard','ride_unicorn']

	<!-- Only users without permissions of 'view_dashboard' and 'ride_unicorn' can see this div -->
	rbac-permission-except=['view_dashboard','ride_unicorn']

	<!-- Only users with role or permission of view_dashboard or 'l33t-h4x0r' can see this div -->
	rbac-only=['view_dashboard','l33t-h4x0r']

	<!-- Only users without roles and permissions of 'view_dashboard' and 'l33t-h4x0r' can see this div -->
	rbac-except=['view_dashboard','l33t-h4x0r']

>RBAC'ed Stuff</div>
```

## Limiting access to states
Say we only wanted to let 'l33t-h4xor's see the page /super-hacker-dashboard
This is how we do it.
```javascript
$stateProvider
  .state('staffpanel', {
    url: '...',
    data: {
      rbac: {
        roleOnly: ['l33t-h4xor']
      }
    }
  });
```
This rbac object accepts basically the same inputs as the div accepted above.
```javascript
var rbac = {
	roleOnly: [], // must contain any of these roles
  roleExcept: [], // must contain none of these roles
  permissionOnly: [], // must contain any of these permissions
  permissionExcept: [], // must contain none of these permission
  only: [], // must contain any of these roles or permissions
  except: [], // must contain none of these roles or permissions
}
```
### Redirecting if roles or permissions are denied
If a user's roles and permissions don't meet the state's required permissions, then we do not allow the state change. This is not the best user experience in cases, so you can also set the attribue `redirectTo`. This takes a state name as a parameter and redirects the user to that state on a failed authorization.

### How do I know if state change was canceled because of this RBAC?
Listen for the 'rbac.$stateChangeDenied' broadcast.
```javascript
// Get's broadcasted when a state change is denied
$scope.$on('rbac.$stateChangeDenied', function(){
	// Do what you need to do
})
```

That's the basics. Nearly every function in this module has a comment of what it does. I encourange you to explore, and pull requests are always welcome!

# Roadmap
- Add to npm
- Integrate grunt into dev workflow
- Add roles and permissions to local storage. Currently page refreshes will require you to set roles/permissions again.
- Add ability to spoof roles. I use this for testing, I might as well add it in to the package.

## Author
- Patrick Toerner
- [patricktoerner.com](http://patricktoerner.com)