<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CardPaymentController;
use App\Http\Controllers\Api\FiscalRecordController;

Route::post('/card-payment', [CardPaymentController::class, 'process']);
Route::post('/fiscalize', [FiscalRecordController::class, 'process']);