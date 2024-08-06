<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('daftar', [AuthController::class, 'daftar']);
Route::post('login', [AuthController::class, 'login']);
Route::post('token', [AuthController::class, 'refreshToken']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('todo', [TodoController::class, 'all']);
    Route::get('todo/{id}', [TodoController::class, 'getById']);
    Route::post('todo', [TodoController::class, 'create']);
    Route::put('todo/{id}', [TodoController::class, 'update']);
    Route::delete('todo/{id}', [TodoController::class, 'delete']);

    Route::put('todo-check/{id}', [TodoController::class, 'check']);
    Route::put('todo-uncheck/{id}', [TodoController::class, 'uncheck']);
});


Route::fallback(function () {
    return response()->json(['message' => 'URL Not Found.'], 404);
});
