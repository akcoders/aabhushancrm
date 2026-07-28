<?php

use Illuminate\Support\Facades\Route;

Route::get('/{path?}', function () {
    $index = public_path('crm/index.html');

    abort_unless(is_file($index), 503, 'CRM frontend has not been built.');

    return response()->file($index);
})->where('path', '^(?!api(?:/|$)|crm(?:/|$)).*');
