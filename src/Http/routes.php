<?php
Route::group([
    'prefix'     => 'admin/google',
    'namespace'  => 'Webkul\Google\Http\Controllers',
    'middleware' => ['web']
], function () {
    
    Route::group(['middleware' => ['user']], function () {
        Route::get('', 'AccountController@index')->name('admin.google.index');

        Route::get('oauth', 'AccountController@store')->name('admin.google.store');

        Route::post('sync/{id}', 'AccountController@sync')->name('admin.google.sync');

        Route::delete('{id}', 'AccountController@destroy')->name('admin.google.destroy');
    });

    Route::post('webhook', 'WebhookController')->name('admin.google.webhook');

});