# LaraPages
A simple CMS build on Laravel 5 (requires 5.1 or higher)

## Installation
To install package use<br>
<code>composer require nickdekruijk/larapages</code><br>
or<br>
<code>composer require nickdekruijk/larapages:dev-master</code><br>
<br>
Add the Service Provider to the 'providers' array in config/app.php<br>
<code>NickDeKruijk\LaraPages\LaraPagesServiceProvider::class,</code><br>
<br>
If you haven't done so already activate authentication with either<br>
`
php artisan make:auth</code>
or adding this to app/routes.php<br>
<code>Route::group(['middleware' => ['web']], function () {
    Route::auth();
});
`
Add the following to app/Http/Controllers/Auth/AuthController
`
    /**
     * Custom larapages login form
     */
    public function showLoginForm()
    {
        return view('laraPages::login');
    }
`