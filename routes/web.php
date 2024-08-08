<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\TestController;

use App\Http\Controllers\IndexController;
use App\Http\Controllers\VideosController;

Route::get('/', [IndexController::class, 'index'])->name('index');

Route::get('/test', [TestController::class, 'index'])->name('test');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::post('/api/videos/{channel}', [App\Http\Controllers\VideosController::class, 'store']);
Route::get('/api/videos', [App\Http\Controllers\VideosController::class, 'index']);

Route::get('/channels/{channel}', [App\Http\Controllers\ChannelsController::class, 'show'])->name('channels.show');