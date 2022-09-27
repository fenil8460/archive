<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckDomainController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

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
    Route::get('/list-url/{id}', [CheckDomainController::class, 'listUrl'])->name('listUrl');
    Route::get('/url-spanshot', [CheckDomainController::class, 'getSnapShot'])->name('snapshot');
    Route::post('/import', [
        CheckDomainController::class,
        'import'
    ])->name('import');
    Route::get('/export/{id}/{status}', [
        CheckDomainController::class,
        'exportData'
    ]);
    Route::get('/sample-export', [
        CheckDomainController::class,
        'sampleExportData'
    ]);
    Route::get('/update-password', function () {
        return view('user.changePassword');
    })->name('viewUpdatePassword');
    Route::post('/change-password', [UserController::class, 'updatePassword'])->name('updatePassword');

});
