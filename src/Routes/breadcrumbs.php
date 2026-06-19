<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;


Breadcrumbs::for('google.calendar.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('google::app.calendar.index.title'), route('admin.google.index', ['route' => request('route')]));
});

Breadcrumbs::for('google.meet.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push(trans('google::app.meet.index.title'), route('admin.google.index', ['route' => request('route')]));
});