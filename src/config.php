<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Specify the models you want to be able to edit with laraPages
    | 'modelId'=>'Nice name',
    | modelId will be used to define the model name with studly_case()
    | so page will be Model App\Page and user App\User
    | Nice name will be what to logged user will see in the navigation menu
    |
    */

    'models' => [
        'page'=>'Pages',
        'media'=>'Media',
        'user'=>'Users',    
    ],
    
    /*
    |--------------------------------------------------------------------------
    | adminpath
    |--------------------------------------------------------------------------
    |
    | The url used to login. e.g. 'lp-admin' for www.domain.com/lp-admin
    |
    */

    'adminpath' => 'lp-admin',
    
    /*
    |--------------------------------------------------------------------------
    | media options
    |--------------------------------------------------------------------------
    |
    | Options for media management
    |
    */

    'media' => [
        'expanded'=>3,           # When treeview is shown auto expand up to 3 levels
        'maxUploadSize'=>'12',   # Maximum size of an uploaded file in megabytes, still limited by php.ini upload_max_filesize and post_max_size
        'folder'=>'media',       # Base folder to store uploaded files. Will be public_path(this)
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    |
    | Specify the users and passwords you want to be able to login to laraPages
    | Hash passwords with bcrypt() or password_hash('xxx', PASSWORD_BCRYPT)
    | Most secure is to put the hash string here, for example:
    | 'admin' => '"$2y$10$ugiFuuMhKZNYHfhdoewkZYUlt1UhkBux3FYDRXcmURhhr/eHC"' 
    | By default get the admin password from LARAPAGES_ADMIN_PASSWORD in .env
    |
    */
	    
    'users' => [
	    'admin' => password_hash(env('LARAPAGES_ADMIN_PASSWORD'), PASSWORD_BCRYPT),
	    'user' => false,
    ],


    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | Specify the views to be used by the parse() method
    |
    */
	    
    'views' => [
	    '404' => 'laraPages::main.404',
	    'page' => 'laraPages::main.page',
    ],

];
