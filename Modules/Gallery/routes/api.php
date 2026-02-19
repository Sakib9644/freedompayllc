<?php

use App\Http\Controllers\Web\Backend\GalleryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('galleries', GalleryController::class)->names('gallery');
});
