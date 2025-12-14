<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CampaignMobileController;

Route::get('/', [FrontController::class, 'index'])->name('home');
Route::get('/login', [FrontController::class, 'index'])->name('login');
Route::post('/login', [BackController::class, 'login'])->name('login');
Route::get('/register', [FrontController::class, 'register'])->name('register');
Route::post('/register-simpan', [BackController::class, 'registerStore'])->name('register.store');

Route::get('/change-password', [FrontController::class, 'changePassword'])->name('change-password');
Route::post('/change-password', [FrontController::class, 'updatePassword'])->name('password.update');
Route::get('/logout', [FrontController::class, 'logout'])->name('logout');

Route::get('/users', [UserController::class, 'index'])->name('users.page');
Route::get('/users-data', [UserController::class, 'getUsers'])->name('users.data');
Route::post('/users-store', [UserController::class, 'storeUser'])->name('users.store');
Route::get('/users-edit/{id}', [UserController::class, 'editUser'])->name('users.edit');
Route::post('/users-update/{id}', [UserController::class, 'updateUser'])->name('users.update');
Route::post('/users-delete/{id}', [UserController::class, 'deleteUser'])->name('users.delete');

Route::middleware(['auth', 'checkrole:Admin,User'])->group(function () {
    Route::get('/admin/home', function () {
        return redirect()->route('campaign-mobile.index');
    })->name('admin.home');

    Route::prefix('campaign-mobile')->name('campaign-mobile.')->group(function () {
        Route::get('/', [CampaignMobileController::class, 'index'])->name('index');
        Route::get('/create', [CampaignMobileController::class, 'create'])->name('create');
        Route::get('/data', [CampaignMobileController::class, 'data'])->name('data');
        Route::post('/store', [CampaignMobileController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CampaignMobileController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CampaignMobileController::class, 'update'])->name('update');
        Route::get('/{id}', [CampaignMobileController::class, 'show'])->name('show');
        Route::delete('/{id}', [CampaignMobileController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('campaign-indihome')->name('campaign-indihome.')->middleware('auth')->group(function () {
        Route::get('/', [CampaignIndihomeController::class, 'index'])->name('index');
        Route::get('/create', [CampaignIndihomeController::class, 'create'])->name('create');
        Route::post('/store', [CampaignIndihomeController::class, 'store'])->name('store');
        Route::get('/{campaignIndihome}/edit', [CampaignIndihomeController::class, 'edit'])->name('edit');
        Route::put('/{campaignIndihome}', [CampaignIndihomeController::class, 'update'])->name('update');
        Route::delete('/{campaignIndihome}', [CampaignIndihomeController::class, 'destroy'])->name('destroy');
    });
});

