# LaraPages
A simple CMS build on Laravel 5 (requires 5.1 or higher)

## Installation
To install package use  
`composer require nickdekruijk/larapages`  
or  
`composer require nickdekruijk/larapages:dev-master`  
Publish the css/js/config with  
`php artisan vendor:publish`  
  
Add the Service Provider to the `'providers'` array in `config/app.php`  
```php
NickDeKruijk\LaraPages\LaraPagesServiceProvider::class,
```

Add the larapages middleware to the `$routeMiddleware` array in `app/Http/Kernel.php`
```php
'larapages' => \NickDeKruijk\LaraPages\LaraPagesAuth::class,
```