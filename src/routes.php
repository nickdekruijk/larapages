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
Route::group(['middleware' => 'web'], function () {
    Route::get('admin', 'NickDeKruijk\LaraPages\LaraPagesController@index');
    Route::get('admin/{model}', 'NickDeKruijk\LaraPages\LaraPagesController@model');
    Route::get('admin/{model}/{id}', 'NickDeKruijk\LaraPages\LaraPagesController@show');
    Route::post('admin/{model}/store/{parent}', 'NickDeKruijk\LaraPages\LaraPagesController@store');
    Route::post('admin/{model}/store/', 'NickDeKruijk\LaraPages\LaraPagesController@store');
    Route::post('admin/{model}/{id}/update', 'NickDeKruijk\LaraPages\LaraPagesController@update');
    Route::post('admin/{model}/{id}/destroy', 'NickDeKruijk\LaraPages\LaraPagesController@destroy');
    Route::post('admin/{model}/{id}/changeparent', 'NickDeKruijk\LaraPages\LaraPagesController@changeparent');
    Route::post('admin/{model}/{parent}/sort', 'NickDeKruijk\LaraPages\LaraPagesController@sort');
});
