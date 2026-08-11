<?php

use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/projects/meta', [ProjectController::class, 'meta']);
    Route::apiResource('projects', ProjectController::class);
});
