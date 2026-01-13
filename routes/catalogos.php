<?php

use App\Http\Controllers\UserController;

Route::prefix('catalogos')
    ->name('catalogos.')
    ->middleware('auth')
    ->group(function () {
        Route::resource('usuarios', UserController::class);
    });
