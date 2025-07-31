<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CardPaymentController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\FiscalRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

Route::post('/fiscalize', [FiscalRecordController::class, 'process']);
Route::post('/card-payment', [CardPaymentController::class, 'process']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transfer', [TransferController::class, 'transfer']);
    Route::get('/users/{accountNumber}', [AuthController::class, 'show']);

    Route::middleware('role:business')->group(function(){
        Route::post('/keys/transaction/reset', [AuthController::class, 'resetTransactionKey'])->name('keys.transaction.reset');
        Route::post('/keys/fiscal/reset', [AuthController::class, 'resetFiscalKey'])->name('keys.fiscal.reset');
        Route::post('/keys/transaction/toggle', [AuthController::class, 'toggleTransactionKey'])->name('keys.transaction.toggle');
        Route::post('/keys/fiscal/toggle', [AuthController::class, 'toggleFiscalKey'])->name('keys.fiscal.toggle');
    });
    
});
