<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NonceController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\CardPaymentController;
use App\Http\Controllers\Api\FiscalRecordController;
use App\Http\Controllers\Api\RegistrationController;

Route::middleware('verify.device')->withoutMiddleware('auth:sanctum')->group(function () {
    Route::post('/fiscalize', [FiscalRecordController::class, 'process']);
    Route::post('/card-payment', [CardPaymentController::class, 'process']);
    Route::get('/items/{id}', [ItemController::class, 'getForCompany']);
    Route::get('/nonce', [NonceController::class, 'getNonce']);
});

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register/full', [RegistrationController::class, 'fullRegister']);
Route::post('/register', [RegistrationController::class, 'register']);

Route::get('/device/{deviceKey}', [DeviceController::class, 'getDeviceData']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transfer', [TransferController::class, 'transfer']);
    Route::get('/users/{accountNumber}', [AuthController::class, 'show']);
    Route::apiResource('items', ItemController::class);
    Route::get('/transfers', [TransferController::class, 'index']);

    Route::get('/devices', [DeviceController::class, 'index']);
    Route::post('/devices', [DeviceController::class, 'store']);
    Route::get('/devices/{id}', [DeviceController::class, 'show']);
    Route::put('/devices/{id}', [DeviceController::class, 'update']);
    Route::delete('/devices/{id}', [DeviceController::class, 'destroy']);
    
    Route::post('/keys/transaction/toggle', [AuthController::class, 'toggleTransactionKey'])->name('keys.transaction.toggle');
    Route::post('/keys/transaction/reset', [AuthController::class, 'resetTransactionKey'])->name('keys.transaction.reset');
    Route::middleware('role:business')->group(function(){
        Route::post('/keys/fiscal/toggle', [AuthController::class, 'toggleFiscalKey'])->name('keys.fiscal.toggle');
        Route::post('/keys/fiscal/reset', [AuthController::class, 'resetFiscalKey'])->name('keys.fiscal.reset');
    });

    Route::prefix('company')->group(function () {
        Route::get('/', [CompanyController::class, 'show']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::put('/', [CompanyController::class, 'update']);
        Route::delete('/', [CompanyController::class, 'destroy']);
    });

});
    
