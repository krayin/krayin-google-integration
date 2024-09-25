### 1. Introduction:

Krayin Google Integration.

It packs in lots of demanding features that allows your business to scale in no time:

* Admin user can connect to their google account.
* User can fetch all events from selected calendars
* Support two-way synchronization for events
* Support real time event synchronization
* User can create google meet link directly from activity form


### 2. Requirements:

* **Krayin**: v2.0.0 or higher.


### 3. Installation:

* Go to the root folder of **Krayin** and run the following command

~~~php
composer require krayin/krayin-google-integration
~~~

* Run these commands below to complete the setup

~~~
php artisan migrate
~~~

~~~
php artisan route:cache
~~~

~~~
php artisan vendor:publish --force

-> Search GoogleServiceProvider navigate to it and then press enter to publish all assets and configurations.
~~~


### 4. Configuration:

* Goto **routes/breadcrumbs.php** file and add following lines

```php
Breadcrumbs::for('google.calendar.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('google::app.calendar.index.title'), route('admin.google.index', ['route' => request('route')]));
});

Breadcrumbs::for('google.meet.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('google::app.meet.index.title'), route('admin.google.index', ['route' => request('route')]));
});
```

* Goto **config/krayin-vite.php** file and add following lines

```php
<?php

return [
    'viters' => [
        // ...

        'google' => [
            'hot_file'                 => 'google-vite.hot',
            'build_directory'          => 'google/build',
            'package_assets_directory' => 'src/Resources/assets',
        ],
    ],
];

```

* Goto **.env** file and add following lines

```.env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/google/oauth"
GOOGLE_WEBHOOK_URI="${APP_URL}/google/webhook"
```

* Goto **config/services.php** file and add following lines

```php
return [
    // ...
    
    'google' => [
        // Our Google API credentials.
        'client_id'       => env('GOOGLE_CLIENT_ID'),
        'client_secret'   => env('GOOGLE_CLIENT_SECRET'),
        
        // The URL to redirect to after the OAuth process.
        'redirect_uri'    => env('GOOGLE_REDIRECT_URI'),
        
        // The URL that listens to Google webhook notifications (Part 3).
        'webhook_uri'     => env('GOOGLE_WEBHOOK_URI'),
        
        // Let the user know what we will be using from his Google account.
        'scopes'          => [
            // Getting access to the user's email.
            \Google_Service_Oauth2::USERINFO_EMAIL,
            
            // Managing the user's calendars and events.
            \Google_Service_Calendar::CALENDAR,
        ],
        
        // Enables automatic token refresh.
        'approval_prompt' => 'force',
        'access_type'     => 'offline',
        
        // Enables incremental scopes (useful if in the future we need access to another type of data).
        'include_granted_scopes' => true,
    ],
];
```

* Goto **app/Http/Middleware/VerifyCsrfToken.php** file and add following line under $except array

```php
protected $except = [
    // ...
    'google/webhook',
];
```

* Goto **app/Console/Kernel.php** file and update the schedule function with the following lines

```php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new \Webkul\Google\Jobs\PeriodicSynchronizations())->everyFifteenMinutes();
    $schedule->job(new \Webkul\Google\Jobs\RefreshWebhookSynchronizations())->daily();
}
```

### 5. Clear Cache:
~~~
php artisan cache:clear

php artisan config:cache
~~~


> That's it, now just execute the project on your specified domain.
