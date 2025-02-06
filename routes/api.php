<?php

use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthorMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('v1/auth/signin', [AuthController::class, 'SignIn']);
Route::post('v1/auth/signup', [AuthController::class, 'SignUp']);
Route::post('admin/signup', [AuthController::class, 'signupadmin']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('v1/auth/signout', [AuthController::class, 'SignOut']);
    Route::post('v1/games', [GameController::class, 'store']);
    Route::get('v1/games', [GameController::class, 'index']);
    Route::get('/game/{slug}', [GameController::class, 'show']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/v1/games/{slug}/scores', [ScoreController::class, 'index']);
    Route::post('/v1/games/{slug}/scores', [ScoreController::class, 'store']);

    Route::get('v1/users/{username}', [GameController::class, 'author']);

});

Route::middleware(AdminMiddleware::class)->group(function () {
    Route::get('v1/admins', [AdministratorController::class, 'index']);
    Route::post('v1/users', [AdministratorController::class, 'store']);
    Route::get('v1/users', [UserController::class, 'index']);
    Route::put('v1/users/{id}', [AdministratorController::class, 'update']);
    Route::delete('v1/users/{id}', [AdministratorController::class, 'delete']);


});

Route::middleware(AuthorMiddleware::class)->group(function () {
    Route::put('v1/games/{slug}', [GameController::class, 'update']);

});
