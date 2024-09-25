<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'admin_locale']], function () {
    Route::group([
        'prefix'     => 'admin/google',
        'namespace'  => 'Webkul\Google\Http\Controllers',
    ], function () {
        Route::group(['middleware' => ['user']], function () {
            Route::get('', 'AccountController@index')->name('admin.google.index');

            Route::get('oauth', 'AccountController@store')->name('admin.google.store');

            Route::delete('{id}', 'AccountController@destroy')->name('admin.google.destroy');

            Route::post('sync/{id}', 'CalendarController@sync')->name('admin.google.calendar.sync');

            Route::post('create-link', 'MeetController@createLink')->name('admin.google.meet.create_link');
        });

        Route::post('webhook', 'WebhookController')->name('admin.google.webhook');
    });
});
