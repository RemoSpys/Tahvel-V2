<?php

use App\Http\Controllers\TahvelCookieController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Carbon\Carbon;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', function () {
        $user = auth()->user();
        $userData = $user->tahvelUser();
        $userJournal = $user->tahvelJournals();

        // Get current week's timetable
        $timetable = null;
        if ($userData && isset($userData['student'])) {
            $timetable = $user->tahvelTimetable();
        }

        if($userData){
            $user->update(['tahvel_cookie' => null]);
        }

        return Inertia::render('Dashboard', [
            'userData' => $userData,
            'journals' => $userJournal,
            'timetable' => $timetable
        ]);
    })->name('dashboard');

    // Dedicated timetable route with date range support
    Route::get('timetable/{week?}', function ($week = null) {
        $user = auth()->user();
        $userData = $user->tahvelUser();

        if (!$userData) {
            return redirect()->route('dashboard')->with('error', 'Please set your Tahvel cookie first');
        }

        // Calculate date range based on week parameter
        if ($week) {
            // If week is provided, parse it (format: YYYY-WW)
            try {
                $date = Carbon::createFromFormat('Y-W', $week)->startOfWeek();
            } catch (Exception $e) {
                $date = Carbon::now()->startOfWeek();
            }
        } else {
            // Default to current week
            $date = Carbon::now()->startOfWeek();
        }

        $from = $date->copy()->startOfWeek()->toISOString();
        $thru = $date->copy()->endOfWeek()->toISOString();

        // Get timetable for the specified week
        $timetable = $user->tahvelTimetable($from, $thru);

        return Inertia::render('Timetable', [
            'userData' => $userData,
            'timetable' => $timetable,
            'currentWeek' => $date->format('Y-W'),
            'weekStart' => $from,
            'weekEnd' => $thru,
            'weekDisplay' => $date->format('M j') . ' - ' . $date->copy()->endOfWeek()->format('M j, Y')
        ]);
    })->name('timetable');

    // API route for fetching timetable data (for AJAX requests)
    Route::get('api/timetable/{week?}', function ($week = null) {
        $user = auth()->user();
        $userData = $user->tahvelUser();

        if (!$userData) {
            return response()->json(['error' => 'No Tahvel data available'], 401);
        }

        // Calculate date range
        if ($week) {
            try {
                $date = Carbon::createFromFormat('Y-W', $week)->startOfWeek();
            } catch (Exception $e) {
                $date = Carbon::now()->startOfWeek();
            }
        } else {
            $date = Carbon::now()->startOfWeek();
        }

        $from = $date->copy()->startOfWeek()->toISOString();
        $thru = $date->copy()->endOfWeek()->toISOString();

        $timetable = $user->tahvelTimetable($from, $thru);

        return response()->json([
            'timetable' => $timetable,
            'week' => $date->format('Y-W'),
            'weekDisplay' => $date->format('M j') . ' - ' . $date->copy()->endOfWeek()->format('M j, Y'),
            'from' => $from,
            'thru' => $thru
        ]);
    })->name('api.timetable');

    Route::put('save-tahvel-cookie', TahvelCookieController::class)->name('save.tahvel-cookie');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';