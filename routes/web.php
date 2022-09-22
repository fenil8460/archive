<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckDomainController;

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
    return view('auth.login');
})->name('customLogin');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware(['auth'])->group(function () {
    Route::get('/task', function () {
        return view('task.create');
    })->name('task');
    Route::post('/create-task', [CheckDomainController::class, 'createTask'])->name('createTask');
    Route::get('/list-task', [CheckDomainController::class, 'listTask'])->name('listTask');
    Route::get('/url-spanshot', [CheckDomainController::class, 'getSnapShot'])->name('snapshot');
});
