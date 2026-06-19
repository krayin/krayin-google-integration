### 1. Introduction:

Krayin Google Integration.

It packs in lots of demanding features that allows your business to scale in no time:

* Admin user can connect to their google account.
* User can fetch all events from selected calendars
* Support two-way synchronization for events
* Support real time event synchronization
* User can create google meet link directly from activity form


### 2. Requirements:

* **Krayin**: v2.2.3 or higher.


### 3. Installation:

* Go to the root folder of **Krayin** and run the following command

~~~php
composer require krayin/krayin-google-integration
~~~

* Run these commands below to complete the setup

~~~
php artisan google:install
~~~

### 4. Configuration:

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

* Goto **bootstrap/app.php** file and add `'google/webhook'` to the CSRF token exception list inside the `withMiddleware` callback

```php
->withMiddleware(function (Middleware $middleware) {
    // ...

    $middleware->validateCsrfTokens(except: [
        // ...
        'google/webhook',
    ]);
})
```

### 5. Clear Cache:
~~~
php artisan cache:clear

php artisan config:cache
~~~


> That's it, now just execute the project on your specified domain.
