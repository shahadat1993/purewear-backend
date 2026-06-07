<?php

use App\Http\Controllers\admin\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/admin/login',[AuthController::class, 'authenticate'])->name('admin.login');
Route::post('/admin/register',[AuthController::class, 'register'])->name('admin.register');


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
