<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoiceItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Invoice
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices/import', [InvoiceController::class, 'import']);
    Route::get('/invoice-items/{id}', [InvoiceItemController::class, 'index']);
});

// Auth
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/invoices', [AuthController::class, 'logout']);
