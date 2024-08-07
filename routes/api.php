<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IssueReportController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('register', 'register');
    Route::post('forget-password', 'forgot');
    Route::post('reset-password', 'reset')->name('password.reset');
    Route::prefix('auth')->group(function () {
        Route::get('google', 'redirectToGoogle');
        Route::post('google/callback', 'handleGoogleCallback');
        Route::get('facebook', 'redirectToFacebook');
        Route::get('facebook/callback', 'handleFacebookCallback');
    });
});

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('uploads', [UploadController::class, 'storeFiles'])->name('upload.store');
    Route::delete('uploads/{id}', [UploadController::class, 'destroy'])->name('upload.destroy');
    Route::post('issue-reports', [IssueReportController::class, 'create']);
    Route::apiResource('metas', MetaController::class);

}); //End of Auth Routes
