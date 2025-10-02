<?php

use App\Http\Controllers\API\v1\Auth\AuthController;
use App\Http\Controllers\API\v1\Statuses\StatusController;
use App\Http\Controllers\API\v1\Tasks\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login',  [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);

    /**
     * Статусы для задач
     */
    Route::apiResource('statuses', StatusController::class);

    /**
     * Задачи
     */
    Route::apiResource('tasks', TaskController::class);
});
