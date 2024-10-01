<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'auth'], function(){
    Route::post("/sign-up", [UserController::class, "signUp"]);
    Route::post("/sign-in", [UserController::class, "signIn"]);
});

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('/current-user', [UserController::class, 'getCurrentUser']);
    Route::get('/users', [UserController::class, 'getAllUsers']);

    Route::prefix('/users/{id}')->group(function (){
       Route::get("/", [UserController::class, 'getUserById']);
       Route::put("/", [UserController::class, 'updateUserById']);
       Route::delete("/", [UserController::class, 'deleteUserById']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
