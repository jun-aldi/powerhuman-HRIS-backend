<?php

use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Company API
Route::group(
    [
        'prefix' => 'company', // company/
        'middleware' => 'auth:sanctum'
    ],
    function () {
        Route::get('', [CompanyController::class, 'all']);
        Route::post('', [CompanyController::class, 'create'])->name('company');
        Route::put('', [CompanyController::class, 'update'])->name('company');
    }
);



// Auth API
Route::post('login', [UserController::class, 'login'])->name('login');
Route::post('register', [UserController::class, 'register'])->name('register');
//logout karena ambil token login pakai middleware
Route::post('logout', [UserController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
//fetch harus login dl
Route::get('user', [UserController::class, 'fetch'])->name('user')->middleware('auth:sanctum');
