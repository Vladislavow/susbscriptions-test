<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\OAuthController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckPublicationOwner;

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
Route::group(['middleware' => ['web']], function () {
    Route::get('/redirect/google', [OAuthController::class, 'getGoogleRedirect'])->name('google.redirect');
    Route::get('/login/google', [\App\Http\Controllers\Api\Auth\OAuthController::class, 'handleGoogleAuth'])->name('google.login');
});

Route::get('/subscriptions/success', [SubscriptionController::class, 'handleSuccessPayment'])->name('subscriptions.success');

Route::post('/register', [\App\Http\Controllers\Api\Auth\RegisterController::class, 'register'])->name('register');
Route::post('/login', [\App\Http\Controllers\Api\Auth\LoginController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [\App\Http\Controllers\Api\UserController::class, 'show'])->name('profile');
    Route::post('/logout', [\App\Http\Controllers\Api\Auth\LoginController::class, 'logout'])->name('logout');

    Route::group([
        'prefix' => 'subscriptions',
        'as' => 'subscriptions.'
    ], function () {
        Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('canceled');

        Route::middleware(AdminMiddleware::class)->group(function () {
            Route::get('/', [SubscriptionController::class, 'index'])->name('index');
            Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('show');
        });
    });

    Route::group([
        'prefix' => 'plans',
        'as' => 'plans.'
    ], function () {
        Route::get('/available', [PlanController::class, 'available'])->name('available');

        Route::middleware(AdminMiddleware::class)->group(function () {
            Route::post('/', [PlanController::class, 'store'])->name('store');
            Route::get('/', [PlanController::class, 'index'])->name('index');
            Route::get('/{plan}', [PlanController::class, 'show'])->name('show');
            Route::put('/{plan}', [PlanController::class, 'update'])->name('update');
            Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
        });
    });

    Route::group([
        'prefix' => 'publications',
        'as' => 'publications.'
    ], function () {
        Route::get('/', [PublicationController::class, 'index'])->name('index');
        Route::post('/', [PublicationController::class, 'store'])->name('create');
        Route::get('/own', [PublicationController::class, 'gerOwnPublications'])->name('own');
        Route::get('/{publication}', [PublicationController::class, 'show'])->name('show');

        Route::middleware(CheckPublicationOwner::class)->group(function () {
            Route::post('/{publication}/publish', [PublicationController::class, 'publish'])->name('publish');
            Route::post('/{publication}/archive', [PublicationController::class, 'archive'])->name('archive');
            Route::put('/{publication}', [PublicationController::class, 'update'])->name('update');
            Route::delete('/{publication}', [PublicationController::class, 'destroy'])->name('destroy');
        });
    });
});
