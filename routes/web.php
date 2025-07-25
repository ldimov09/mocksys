<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\UserKeyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FiscalRecordController;
use App\Http\Controllers\TransactionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
//Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroy'])->name('users.destroy');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    
    Route::get('/fiscal-records', [FiscalRecordController::class, 'index'])->name('fiscal_records.index');

    Route::get('/logs', [LogController::class, 'index'])->name('logs');

    Route::post('/keys/{user}/lock', [UserKeyController::class, 'toggleLock'])->name('keys.lock.toggle');

    Route::resource('companies', CompanyController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/transfer', [DashboardController::class, 'transfer'])->name('transfer');

    Route::post('/keys/{user}/transaction/reset', [UserKeyController::class, 'resetTransactionKey'])->name('keys.transaction.reset');
    Route::post('/keys/{user}/fiscal/reset', [UserKeyController::class, 'resetFiscalKey'])->name('keys.fiscal.reset');
    Route::post('/keys/{user}/transaction/toggle', [UserKeyController::class, 'toggleTransactionKey'])->name('keys.transaction.toggle');
    Route::post('/keys/{user}/fiscal/toggle', [UserKeyController::class, 'toggleFiscalKey'])->name('keys.fiscal.toggle');
});
