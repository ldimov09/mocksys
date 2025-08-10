<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CardPaymentController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\FiscalRecordController;
use App\Http\Controllers\Api\ItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

Route::post('/fiscalize', [FiscalRecordController::class, 'process']);
Route::post('/card-payment', [CardPaymentController::class, 'process']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transfer', [TransferController::class, 'transfer']);
    Route::get('/users/{accountNumber}', [AuthController::class, 'show']);
    Route::apiResource('items', ItemController::class);

    Route::middleware('role:business')->group(function(){
        Route::post('/keys/transaction/reset', [AuthController::class, 'resetTransactionKey'])->name('keys.transaction.reset');
        Route::post('/keys/fiscal/reset', [AuthController::class, 'resetFiscalKey'])->name('keys.fiscal.reset');
        Route::post('/keys/transaction/toggle', [AuthController::class, 'toggleTransactionKey'])->name('keys.transaction.toggle');
        Route::post('/keys/fiscal/toggle', [AuthController::class, 'toggleFiscalKey'])->name('keys.fiscal.toggle');
    });

    Route::prefix('company')->group(function () {
        Route::get('/', [CompanyController::class, 'show']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::put('/', [CompanyController::class, 'update']);
        Route::delete('/', [CompanyController::class, 'destroy']);
    });
});
    
