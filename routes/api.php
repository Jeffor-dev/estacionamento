<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/motorista', [App\Http\Controllers\MotoristaController::class,'api'])->name('motorista.api');

