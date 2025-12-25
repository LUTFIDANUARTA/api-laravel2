<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage; // Tambahan: Import Storage
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TodoController;
use App\Models\User;

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login'])->name('login');

// Protected: semua user harus login
Route::middleware('auth:sanctum')->group(function () {

    // CRUD todo milik user login
    Route::apiResource('todos', TodoController::class);

    // Route Download Attachment (User biasa bisa akses milik sendiri, Admin bisa akses semua)
    Route::get('/todos/{todo}/attachment', function (\App\Models\Todo $todo) {
        $user = request()->user();

        // Cek permission: Admin BOLEH, Pemilik Todo BOLEH. Selain itu FORBIDDEN.
        if ($user->role !== 'admin' && $todo->user_id !== $user->id) {
            abort(403, 'Forbidden');
        }

        // Cek apakah file ada di storage
        if (! $todo->attachment_path || ! Storage::disk('public')->exists($todo->attachment_path)) {
            abort(404, 'Attachment not found');
        }

        // Download file
        return response()->download(
            Storage::disk('public')->path($todo->attachment_path)
        );
    });

    // logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // ------------------
    // ADMIN ONLY ROUTES
    // ------------------
    Route::middleware('role:admin')->group(function () {

        // 1. List semua user
        Route::get('/admin/users', function () {
            return User::select('id','name','email','role','created_at')->get();
        });

        // 2. List semua todos
        Route::get('/admin/todos', function () {
            return \App\Models\Todo::with('user')->get();
        });

    });
});