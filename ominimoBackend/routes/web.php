<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return response()->json(['message' => 'API Only - Use React frontend']);
})->where('any', '.*');
