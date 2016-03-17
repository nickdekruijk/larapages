<?php
# Routes for MediaController
/*
Route::get('admin/media', 'MediaController@index');
Route::get('admin/media/mini', 'MediaController@mini');
Route::get('admin/media/{folder}', 'MediaController@show');
Route::post('admin/media/store', 'MediaController@store');
Route::post('admin/media/newfolder', 'MediaController@newfolder');
Route::post('admin/media/destroy', 'MediaController@destroy');
Route::post('admin/media/destroyFolder', 'MediaController@destroyFolder');
Route::post('admin/media/rename', 'MediaController@rename');
*/

# Routes for the admin/cms part

Route::group(['middleware' => ['web']], function () {
	Route::get(config('larapages.adminpath').'/login', 'NickDeKruijk\LaraPages\LaraPagesController@login');
	Route::post(config('larapages.adminpath').'/login', 'NickDeKruijk\LaraPages\LaraPagesController@loginValidate');
});

Route::group(['middleware' => ['web', 'larapages']], function () {
	Route::get(config('larapages.adminpath').'/media', 'NickDeKruijk\LaraPages\LaraPagesMediaController@index');
	Route::get(config('larapages.adminpath').'/media/mini', 'NickDeKruijk\LaraPages\LaraPagesMediaController@mini');
	Route::get(config('larapages.adminpath').'/media/{folder}', 'NickDeKruijk\LaraPages\LaraPagesMediaController@show');
	Route::post(config('larapages.adminpath').'/media/store', 'NickDeKruijk\LaraPages\LaraPagesMediaController@store');
	Route::post(config('larapages.adminpath').'/media/newfolder', 'NickDeKruijk\LaraPages\LaraPagesMediaController@newfolder');
	Route::post(config('larapages.adminpath').'/media/destroy', 'NickDeKruijk\LaraPages\LaraPagesMediaController@destroy');
	Route::post(config('larapages.adminpath').'/media/destroyFolder', 'NickDeKruijk\LaraPages\LaraPagesMediaController@destroyFolder');
	Route::post(config('larapages.adminpath').'/media/rename', 'NickDeKruijk\LaraPages\LaraPagesMediaController@rename');

    Route::get(config('larapages.adminpath'), 'NickDeKruijk\LaraPages\LaraPagesController@index');
    Route::get(config('larapages.adminpath').'/{model}', 'NickDeKruijk\LaraPages\LaraPagesController@model');
    Route::get(config('larapages.adminpath').'/{model}/{id}', 'NickDeKruijk\LaraPages\LaraPagesController@show');
    Route::post(config('larapages.adminpath').'/{model}/store/{parent}', 'NickDeKruijk\LaraPages\LaraPagesController@store');
    Route::post(config('larapages.adminpath').'/{model}/store/', 'NickDeKruijk\LaraPages\LaraPagesController@store');
    Route::post(config('larapages.adminpath').'/{model}/{id}/update', 'NickDeKruijk\LaraPages\LaraPagesController@update');
    Route::post(config('larapages.adminpath').'/{model}/{id}/destroy', 'NickDeKruijk\LaraPages\LaraPagesController@destroy');
    Route::post(config('larapages.adminpath').'/{model}/{id}/changeparent', 'NickDeKruijk\LaraPages\LaraPagesController@changeparent');
    Route::post(config('larapages.adminpath').'/{model}/{parent}/sort', 'NickDeKruijk\LaraPages\LaraPagesController@sort');
});
