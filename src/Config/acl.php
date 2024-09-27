
<?php

return [
    [
        'key'   => 'google',
        'name'  => 'google::app.title',
        'route' => 'admin.google.index',
        'sort'  => 2,
    ], [
        'key'   => 'google.view',
        'name'  => 'google::app.view',
        'route' => 'admin.google.index',
        'sort'  => 1,
    ], [
        'key'   => 'google.sync',
        'name'  => 'google::app.sync',
        'route' => 'admin.google.calendar.sync',
        'sort'  => 2,
    ],
];
