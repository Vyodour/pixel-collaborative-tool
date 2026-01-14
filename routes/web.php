<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CanvasController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root Redirect
Route::get('/', fn() => redirect('/dashboard'));

// Authentication Routes (Guest)
Route::middleware(['guest'])->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
    
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Protected Application Routes
Route::middleware(['auth'])->group(function () {
    
    // Authentication Actions
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard & Static Pages
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        // Stats Logic (Inline for now, consider moving to DashboardController)
        $totalProjects = $user->projects()->count();
        $activeMemberships = $user->joinedProjects()->count();
        
        $totalCanvases = \App\Models\Canvas::whereHas('project', function($query) use ($user) {
             $query->where('user_id', $user->id)
                  ->orWhereHas('members', function ($q) use ($user) {
                      $q->where('user_id', $user->id)
                        ->where('project_users.status', 'active');
                  });
        })->count();

        $recentProjects = \App\Models\Project::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('members', function ($q) use ($user) {
                      $q->where('user_id', $user->id)
                        ->where('project_users.status', 'active');
                  });
        })
        ->with('canvases')
        ->latest('updated_at')
        ->take(3)
        ->get();
        
        return view('dashboard', compact('totalProjects', 'activeMemberships', 'totalCanvases', 'recentProjects'));
    })->name('dashboard');

    Route::view('/explore', 'pages.explore.index')->name('explore');
    Route::view('/challenges', 'pages.challenges.index')->name('challenges');
    Route::view('/assets', 'pages.assets.index')->name('assets');
    Route::view('/settings', 'pages.settings.index')->name('settings');

    // --- Core Features ---

    // 1. Teams & Projects
    Route::get('/teams', [ProjectController::class, 'teams'])->name('teams');
    Route::get('/join/{code}', [ProjectController::class, 'join'])->name('projects.join');
    
    Route::resource('projects', ProjectController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('/projects/{project}/leave', [ProjectController::class, 'leave'])->name('projects.leave');

    // 2. Project Sub-Resources
    Route::prefix('projects/{project}')->group(function () {
        // Chat
        Route::get('/messages', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/messages', [ChatController::class, 'store'])->name('chat.store');
        Route::patch('/messages/{message}', [ChatController::class, 'update'])->name('chat.update');
        Route::delete('/messages/{message}', [ChatController::class, 'destroy'])->name('chat.destroy');
        
        // Members
        Route::patch('/members/{user}', [ProjectMemberController::class, 'update'])->name('members.update');
        Route::delete('/members/{user}', [ProjectMemberController::class, 'destroy'])->name('members.destroy');
        
        // Canvas Creation (Project Context)
        Route::post('/canvases', [CanvasController::class, 'store'])->name('canvases.store');
    });

    // 3. Canvas Management
    Route::prefix('canvases/{canvas}')->name('canvases.')->group(function () {
        Route::get('/', [CanvasController::class, 'show'])->name('show');
        Route::patch('/', [CanvasController::class, 'update'])->name('update');
        Route::delete('/', [CanvasController::class, 'destroy'])->name('destroy');
        
        // Canvas Actions
        Route::post('/paint', [CanvasController::class, 'paint'])->name('paint');
        Route::post('/comments', [CanvasController::class, 'storeComment'])->name('comments.store');
        Route::delete('/comments/{comment}', [CanvasController::class, 'destroyComment'])->name('comments.destroy');
    });

});
