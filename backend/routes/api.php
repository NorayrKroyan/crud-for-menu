<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountingMenuController;

Route::get('/accounting-menus', [AccountingMenuController::class, 'index']);
Route::get('/accounting-menus/parents', [AccountingMenuController::class, 'parents']);

Route::post('/accounting-menus', [AccountingMenuController::class, 'store']);
Route::put('/accounting-menus/{id}', [AccountingMenuController::class, 'update']);
Route::delete('/accounting-menus/{id}', [AccountingMenuController::class, 'destroy']);
