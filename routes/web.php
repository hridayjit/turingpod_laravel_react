<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SmartStatistics\HypothesisOneController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/tensile_property_evaluator', function () {
    return Inertia::render('Products/TensilePropertyEvaluator');
})->name('tensile_property_evaluator');

Route::get('/smart_statistics', function () {
    return Inertia::render('Products/SmartStatistics');
})->name('smart_statistics');

Route::get('/solar_system_simulation', function () {
    return Inertia::render('Products/SolarSystemSimulation');
})->name('solar_system_simulation');

Route::get('/momentum_conservation', function () {
    return Inertia::render('Products/MomentumConservation');
})->name('momentum_conservation');

Route::get('/chat_app', function () {
    return Inertia::render('Products/ChatApp');
})->name('chat_app');

Route::get('/blogs', function () {
    return Inertia::render('Products/Blogs');
})->name('blogs');

Route::get('/query_recommend', function () {
    return Inertia::render('Products/QueryRecommend');
})->name('query_recommend');

Route::post('/getHypoOne', [HypothesisOneController::class, 'getResult'])->name('getHypoOne');


// Route::post('Api/statistics', [StatisticsController::class, 'get'])->name('statistics');




Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
