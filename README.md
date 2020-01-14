# Laravel Payfort

Using this package you can integrate laravel with payfort.
 
this package is under development currently it only support merchant page 2 integration.


## How to install

```
composer require moeen-basra/laravel-payfort
```

publish the configuration using the following command

```
php artisan vendor:publish --tag=payfort-config
```

update the `payfort` configuration file under config directory.

To check the implementation take the stubs from `public/package` folder and put these in project's appropriate folders.
