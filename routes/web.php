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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/students', [App\Http\Controllers\StudentController::class, 'index'])->name('students.index');
Route::post('/students/import', [App\Http\Controllers\StudentController::class, 'import'])->name('students.import');
Route::get('/students/download_file', [App\Http\Controllers\StudentController::class, 'downloadFile'])->name('students.download_file');
