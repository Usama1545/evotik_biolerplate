<?php

use App\Http\Controllers\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('uploads', [UploadController::class, 'storeFiles'])->name('upload.store');
    Route::delete('uploads/{id}', [UploadController::class, 'destroy'])->name('upload.destroy');
}); //End of Auth Routes
