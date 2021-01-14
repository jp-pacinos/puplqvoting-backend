<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Student\Reports\VoteResultController;
use App\Http\Controllers\Features\Admin\Reports\ElectionResultsController;
use App\Http\Controllers\Features\Student\Voting\StudentEmailVerifiedController;
use App\Http\Controllers\Features\Admin\App\HomeController as AdminHomeController;
use App\Http\Controllers\Features\Student\App\HomeController as StudentHomeController;

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

Route::prefix('vote')->name('vote.')->group(function () {
    /**
     * vote.final
     */
    Route::prefix('final')->name('final.')->group(function () {
        Route::get('/email/{history}/verify', [StudentEmailVerifiedController::class, 'index'])->name('verified.email');
        Route::get('/results/{history}', [VoteResultController::class, 'index'])->name('report');
    });
});

Route::prefix('pupadmin')->group(function () {
    Route::get('/reports/election/{session}', [ElectionResultsController::class, 'index'])->name('reports.election');

    Route::get('/{view?}', [AdminHomeController::class, 'index'])->where('view', '^((?!api).)*');
});

Route::get('/{view?}', [StudentHomeController::class, 'index'])->where('view', '^((?!api).)*');
