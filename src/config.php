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
    | Users
    |--------------------------------------------------------------------------
    |
    | Specify the users and passwords you want to be able to login to laraPages
    | Hash passwords with bcrypt() or password_hash('xxx', PASSWORD_BCRYPT)
    |
    */
	    
    'users' => [
	    'admin' => false,
	    'user' => false,
    ],


];
