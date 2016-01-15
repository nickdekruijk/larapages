# LaraPages
A simple CMS build on Laravel 5 (requires 5.1 or higher)

## Installation
To install package use  
`composer require nickdekruijk/larapages`  
or  
`composer require nickdekruijk/larapages:dev-master`  
  
Add the Service Provider to the `'providers'` array in `config/app.php`  
```php
NickDeKruijk\LaraPages\LaraPagesServiceProvider::class,
```
  
If you haven't used the Illuminate\Html before you might need to add this too to the `'providers'` array in `config/app.php`  
```php
Illuminate\Html\HtmlServiceProvider::class,
```  
And also this to the `'aliases`' array
```php
    'Form'		=> Illuminate\Html\FormFacade::class,
	'Html'		=> Illuminate\Html\HtmlFacade::class,
```  
  
If you haven't done so already activate authentication with this in Laravel 5.2  
`php artisan make:auth`  
  
Add the following to `app/Http/Controllers/Auth/AuthController.php`
```php
/**
 * Custom larapages login form
 */
public function showLoginForm()
{
    return view('laraPages::login');
}
```