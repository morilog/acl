# Laravel ACL
User-Role-Permission ACL system for Laravel >= 5.1

### Installation
#### Getting Package
Add following line to your `composer.json'` file at `require` section:
```json
"require": {
    "morilog/acl": "dev-master"
}
```
And run `composer update`

#### Configs
Publish configs with this command and set `admin_user_id`:

```
php artisan vendor:publish --provider="Morilog\Acl\AclServiceProvider" --tag="config"
```

in `app.php`:

services: 
```
Morilog\Acl\AclServiceProvider::class
```
alias: 
```
'Acl' => 'Morilog\Acl\Facades\Acl'
```

#### Middleware
Open `kernel.php` file in `app/Http' directory and add bellow line to `$routeMiddleware` array:
~~~php
   'acl' => Morilog\Acl\Middlewares\AclCheck::class
~~~
#### Migrations
```
php artisan vendor:publish --provider="Morilog\Acl\AclServiceProvider" --tag="migration"
```

#### Commands
```
php artisan morilog:acl:add-roles
php artisan morilog:acl:admin-roles
php artisan morilog:acl:add-permissions
php artisan morilog:acl:clear-permissions
```