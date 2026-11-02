<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/play', [SiteController::class, 'play'])->name('play');
Route::get('/download/{browser}', [SiteController::class, 'install'])->name('install');
Route::get('/privacy', [SiteController::class, 'privacy'])->name('privacy');
Route::get('/about', [SiteController::class, 'about'])->name('about');
Route::get('/install-yourself', [SiteController::class, 'installYourself'])->name('install-yourself');
Route::get('/changelog', [SiteController::class, 'changelog'])->name('changelog');
Route::get('/bugs', [SiteController::class, 'bugs'])->name('bugs');
Route::post('/bugs', [SiteController::class, 'storeBug'])->middleware('throttle:8,1')->name('bugs.store');
Route::redirect('/bugs/inbox', '/admin');
Route::get('/donate', [SiteController::class, 'donate'])->name('donate');

Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->middleware('throttle:8,1')->name('admin.login.store');
Route::post('/admin/logout', [AdminController::class, 'logout'])->middleware('admin')->name('admin.logout');
Route::get('/admin', [AdminController::class, 'home'])->middleware('admin')->name('admin.home');
Route::patch('/admin/bugs/{report}', [AdminController::class, 'updateBug'])->middleware('admin')->name('admin.bugs.update');
Route::post('/admin/releases', [AdminController::class, 'storeRelease'])->middleware('admin')->name('admin.releases.store');
