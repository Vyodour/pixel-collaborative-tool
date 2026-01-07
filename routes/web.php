<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (which handles auth check via middleware)
Route::get('/', function () {
    return redirect('/dashboard');
});

// Guest Routes
Route::middleware(['guest'])->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
});

Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $projects = \App\Models\Project::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orWhereIn('id', \App\Models\ProjectUser::where('user_id', \Illuminate\Support\Facades\Auth::id())->where('status', 'active')->pluck('project_id'))
            ->get();
        return view('dashboard', compact('projects'));
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('projects', \App\Http\Controllers\ProjectController::class)->only(['store', 'show']);
    Route::get('/join/{code}', [\App\Http\Controllers\ProjectController::class, 'join'])->name('projects.join');

    // Canvas Routes
    Route::post('/projects/{project}/canvases', [\App\Http\Controllers\CanvasController::class, 'store'])->name('canvases.store');
    Route::get('/canvases/{canvas}', [\App\Http\Controllers\CanvasController::class, 'show'])->name('canvases.show');
});
