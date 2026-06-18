<?php

declare(strict_types=1);

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', static fn () => Inertia::render('Dashboard', [
    'eventsLast24h' => 0,
    'uniqueVisitors' => 0,
    'ingestP95Ms' => 0,
    'eventsOverTime' => [],
]))->name('home');

// Dashboard reads. Auth middleware is added once Breeze/Fortify is installed;
// authorization is enforced server-side per Action (never client-side gating).
Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
