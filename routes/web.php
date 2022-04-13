<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
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

Route::prefix('teacher')->name('teacher.')->middleware(['auth:sanctum', 'verified', 'role:teacher'])->group(function () {

    Route::get('/', [TeacherController::class, 'index'])->name('index');

});

Route::prefix('student')->middleware(['auth:sanctum', 'verified', 'role:student'])->group(function () {

    Route::get('/', [StudentController::class, 'index'])->name('dashboard');

});

require __DIR__ . '/auth.php';
