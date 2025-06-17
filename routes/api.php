<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->middleware('role:admin,receptionist,doctor,patient');
        Route::post('/', [AppointmentController::class, 'store'])->middleware('role:admin,receptionist');
        Route::get('/{id}', [AppointmentController::class, 'show'])->middleware('role:admin,receptionist,doctor,patient');
        Route::put('/{id}', [AppointmentController::class, 'update'])->middleware('role:admin,receptionist');
        Route::delete('/{id}', [AppointmentController::class, 'destroy'])->middleware('role:admin,receptionist');
    });
});