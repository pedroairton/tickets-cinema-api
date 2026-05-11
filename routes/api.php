<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MovieController as AdminMovieController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\ScreeningController as AdminScreeningController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\MovieController as PublicMovieController;
use App\Http\Controllers\Public\GenreController as PublicGenreController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Public\ScreeningController as PublicScreeningController;
use App\Http\Controllers\Customer\SeatController;
use App\Http\Controllers\Customer\TicketController;
use Illuminate\Support\Facades\Route;


Route::get('/health', function () {
    return response()->json([
        'status' => 'ok'
    ]);
});
Route::prefix('v1')->group(function () {
    // auth
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // rotas publicas
    Route::prefix('movies')->group(function () {
        Route::get('/', [PublicMovieController::class, 'index']);
        Route::get('/search', [PublicMovieController::class, 'search']);
        Route::get('/{movie:slug}', [PublicMovieController::class, 'show']);
        Route::get('/{movie:slug}/screenings', [PublicMovieController::class, 'screenings']);
    });

    Route::prefix('genres')->group(function () {
        Route::get('/', [PublicGenreController::class, 'index']);
        Route::get('/{genre:slug}/movies', [PublicGenreController::class, 'movies']);
    });

    Route::prefix('screenings')->group(function () {
        Route::get('/', [PublicScreeningController::class, 'index']);
        Route::get('/by-date', [PublicScreeningController::class, 'byDate']);
        Route::get('/{screening}', [PublicScreeningController::class, 'show']);
        Route::get('/{screening}/seats', [PublicScreeningController::class, 'seats']);
    });

    // rotas autenticadas - customer
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/screenings/{screening}/available-seats', [SeatController::class, 'available']);

        // pedidos
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::post('/', [OrderController::class, 'store']);
            Route::get('/{order}', [OrderController::class, 'show']);
            Route::patch('/{order}/cancel', [OrderController::class, 'cancel']);
        });

        // tickets (historico)
        Route::prefix('tickets')->group(function () {
            Route::get('/', [TicketController::class, 'index']);
            Route::get('/{ticket}', [TicketController::class, 'show']);
        });
    });

    // rotas autenticadas - admin

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard/summary', [DashboardController::class, 'index']);
            Route::get('/dashboard/top-movies', [DashboardController::class, 'topMovies']);
            Route::get('/dashboard/revenue', [DashboardController::class, 'revenue']);
            Route::get('/dashboard/popular-times', [DashboardController::class, 'popularTimes']);
            Route::get('/dashboard/top-genres', [DashboardController::class, 'topGenres']);

            // crud filmes
            Route::apiResource('movies', AdminMovieController::class);
            Route::patch('/movies/{movie}/status', [AdminMovieController::class, 'updateStatus']);
            Route::get('/movies{movie}/insights', [AdminMovieController::class, 'insights']);

            // crud sessoes
            Route::apiResource('screenings', AdminScreeningController::class);

            // crud salas
            Route::apiResource('rooms', AdminRoomController::class);

            // crud generos
            Route::apiResource('genres', AdminGenreController::class);
        });
    });
});
