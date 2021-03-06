# The future of LaraPages
[nickdekruijk/admin](https://github.com/nickdekruijk/admin) is the next evolution of the LaraPages package in early development. New features include localisation, improved GUI design, beter authentication with user roles and it will be even easier to implement in your Laravel project. As soon as all current larapages fuctionality is ported into the new package this nickdekruijk/larapages repo won't be updated anymore but will remain online for historical reference.

# LaraPages
A simple CMS build on Laravel 5 (requires 5.1 or higher).
Basically it's a webbased editor for your Laravel models. Each model must have a `$pagesAdmin` array. See the samples folder for an example. A basic media/filemanager is included too.

## Installation
To install package use  
`composer require nickdekruijk/larapages`  
or  
`composer require nickdekruijk/larapages:dev-master`  
  
For Laravel 5.4 and lower add the Service Provider to the `'providers'` array in `config/app.php`
```php
NickDeKruijk\LaraPages\LaraPagesServiceProvider::class,
```

Add the larapages middleware to the `$routeMiddleware` array in `app/Http/Kernel.php`
```php
'larapages' => \NickDeKruijk\LaraPages\LaraPagesAuth::class,
```

After installing for the first time publish the css/js/config with

`php artisan vendor:publish --provider="NickDeKruijk\LaraPages\LaraPagesServiceProvider"`

After a `composer update` publish the public assets again with

`php artisan vendor:publish --tag=public --force --provider="NickDeKruijk\LaraPages\LaraPagesServiceProvider"`

## Configuration
After installation (if you did `php artisan vendor:publish`) a default config file called `larapages.php` will be available in your Laravel `app/config` folder.

## Frontend
To get you started an example model and migration is provided in the samples folder.
To use the Frontend template and to parse the pages add this to your `routes.php` (Laravel 5.2 and earlier) or `web.php` (Laravel 5.3 or later) if you use the Page model from our sample
```php
Route::get('{any}', 'PageController@route')->where('any', '(.*)');
```

### Enable Preview button while editing a model
If you use pagesAdmin['preview'] like this:
```php
'preview' => '/preview/page/{id}',          # Enable preview button, links to this url
```
you will need a route that enables it. For example add this to your `web.php` or `routes.php`:
```php
Route::get('/preview/page/{id}', function ($id) {
    $page = App\Page::findOrFail($id);
    if (!View::exists($page->view)) $page['view']='detail';
    return view($page->view, compact('page'));
})->middleware('larapages');
```
