<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileDiagnosticController;

Route::get('/', function () {
    return view('linkedin');
})->name('profile.page');

Route::post('/analyze', [
    ProfileDiagnosticController::class,
    'analyze'
])->name('profile.analyze');