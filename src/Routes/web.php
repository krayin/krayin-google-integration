<?php

use Illuminate\Support\Facades\Route;
use Webkul\Google\Http\Controllers\AccountController;
use Webkul\Google\Http\Controllers\CalendarController;
use Webkul\Google\Http\Controllers\MeetController;
use Webkul\Google\Http\Controllers\WebhookController;

Route::group([
    'prefix'     => 'admin/google',
    'middleware' => ['web'],
], function () {
    Route::group(['middleware' => ['user']], function () {
        Route::controller(AccountController::class)->group(function () {
            Route::get('', 'index')->name('admin.google.index');

            Route::get('oauth', 'store')->name('admin.google.store');

            Route::delete('{id}', 'destroy')->name('admin.google.destroy');
        });

        Route::post('sync/{id}', [CalendarController::class, 'sync'])->name('admin.google.calendar.sync');

        Route::post('create-link', [MeetController::class, 'createLink'])->name('admin.google.meet.create_link');
    });

    Route::post('webhook', [WebhookController::class])->name('admin.google.webhook');
});
