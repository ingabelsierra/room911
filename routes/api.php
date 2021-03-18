<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassportAuthController;
use App\Http\Controllers\EmployeeController;


Route::post('login', [PassportAuthController::class, 'login']);
Route::post('entry', [EmployeeController::class, 'entry']);
//Route::post('users', [PassportAuthController::class, 'store']);
//Route::get('generate-pdf', [EmployeeController::class, 'downloadPDF']);

Route::middleware('auth:api')->group(function () {
    
    Route::resource('employees', EmployeeController::class);
    Route::resource('users', PassportAuthController::class);
    Route::get('logout', [PassportAuthController::class, 'logout']);
    Route::get('generate-pdf', [EmployeeController::class, 'downloadPDF']);
});