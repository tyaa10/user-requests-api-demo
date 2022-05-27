<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['cors'])->controller(App\Http\Controllers\RequestController::class)->group(function () {
    Route::get('/requests/{dateDelimiter?}/{date?}/{statusDelimiter?}/{status?}/', 'index')
        ->where('dateDelimiter', 'date')
        ->where('statusDelimiter', 'status');
    Route::get('/requests/{statusDelimiter?}/{status?}/', 'index')
        ->where('statusDelimiter', 'status');
    Route::put('/requests/{id}', 'update');
    Route::post('/requests', 'store');
});
