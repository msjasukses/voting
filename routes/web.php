<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\VoterLoginController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    // Pemilih (siswa): login dengan NIS dari Data Center.
    Route::get('/masuk', [VoterLoginController::class, 'show'])->name('voter.login');
    Route::post('/masuk', [VoterLoginController::class, 'login'])->middleware('throttle:10,1');

    // Admin / panitia: login dengan email & password.
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/voting', [PollController::class, 'index'])->name('polls.index');
    Route::get('/polls/{poll}', [PollController::class, 'show'])->name('polls.show');
    Route::post('/polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('polls', Admin\PollController::class);
        Route::post('polls/{poll}/reset', [Admin\PollController::class, 'resetVotes'])->name('polls.reset');
        Route::get('polls/{poll}/export/excel', [Admin\PollExportController::class, 'excel'])->name('polls.export.excel');
        Route::get('polls/{poll}/export/pdf', [Admin\PollExportController::class, 'pdf'])->name('polls.export.pdf');

        Route::resource('polls.candidates', Admin\CandidateController::class)
            ->only(['store', 'edit', 'update', 'destroy'])
            ->scoped(['candidate' => 'id']);

        Route::get('pengaturan', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('pengaturan', [Admin\SettingController::class, 'update'])->name('settings.update');

        Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}/toggle-admin', [Admin\UserController::class, 'toggleAdmin'])->name('users.toggle-admin');
        Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    });
});
