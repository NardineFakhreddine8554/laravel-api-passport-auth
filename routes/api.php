<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticationController;



// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::post('register', [AuthenticationController::class, 'register'])->name('register');
Route::post('login', [AuthenticationController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthenticationController::class, 'user']);
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});

Route::middleware(['auth:api', 'role:admin'])->get('/admin-only', function () {
    return response()->json(['message' => 'Admin route']);
});

Route::middleware(['auth:api', 'role:editor'])->get('/editor-only', function () {
    return response()->json(['message' => 'Editor route']);
});
Route::middleware(['auth:api', 'role:user'])->get('/user-only', function () {
    return response()->json(['message' => 'User route']);
});
