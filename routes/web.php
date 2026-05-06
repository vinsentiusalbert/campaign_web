<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CampaignMobileController;
use App\Http\Controllers\CampaignIndihomeController;
use App\Http\Controllers\CampaignKamController;
use App\Http\Controllers\CampaignKamDashboardController;
use App\Http\Controllers\CampaignOrbitController;
use App\Http\Controllers\CampaignSoundboxController;
use App\Http\Controllers\CampaignNomorCantikController;
use App\Http\Controllers\CampaignWabaInteraktifController;

Route::get('/', [FrontController::class, 'index'])->name('home');
Route::get('/login', [FrontController::class, 'index']);
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

Route::middleware(['auth', 'checkrole:Super,Admin,Tsel'])->group(function () {
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
        Route::get('/{id}/download', [CampaignMobileController::class, 'download'])->name('download');
        Route::post('/{id}/activate', [CampaignMobileController::class, 'activate'])->name('activate');
        Route::post('/{id}/toggle-testing', [CampaignMobileController::class, 'toggleTesting'])->name('toggle-testing');
    });
    Route::prefix('campaign-indihome')->name('campaign-indihome.')->middleware('auth')->group(function () {
        Route::get('/', [CampaignIndihomeController::class, 'index'])->name('index');
        Route::get('/create', [CampaignIndihomeController::class, 'create'])->name('create');
        Route::get('/data', [CampaignIndihomeController::class, 'data'])->name('data');
        Route::post('/store', [CampaignIndihomeController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CampaignIndihomeController::class, 'edit'])->name('edit');
        Route::put('/{campaignIndihome}', [CampaignIndihomeController::class, 'update'])->name('update');
        Route::get('/{id}', [CampaignIndihomeController::class, 'show'])->name('show');
        Route::delete('/{id}', [CampaignIndihomeController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [CampaignIndihomeController::class, 'download'])->name('download');
        Route::post('/{id}/activate', [CampaignIndihomeController::class, 'activate'])->name('activate');
        Route::post('/{id}/toggle-testing', [CampaignIndihomeController::class, 'toggleTesting'])->name('toggle-testing');
    });

    Route::prefix('campaign-orbit')->name('campaign-orbit.')->middleware('auth')->group(function () {
        Route::get('/', [CampaignOrbitController::class, 'index'])->name('index');
        Route::get('/create', [CampaignOrbitController::class, 'create'])->name('create');
        Route::get('/data', [CampaignOrbitController::class, 'data'])->name('data');
        Route::post('/store', [CampaignOrbitController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CampaignOrbitController::class, 'edit'])->name('edit');
        Route::put('/{campaignOrbit}', [CampaignOrbitController::class, 'update'])->name('update');
        Route::get('/{id}', [CampaignOrbitController::class, 'show'])->name('show');
        Route::delete('/{id}', [CampaignOrbitController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [CampaignOrbitController::class, 'download'])->name('download');
        Route::post('/{id}/activate', [CampaignOrbitController::class, 'activate'])->name('activate');
        Route::post('/{id}/toggle-testing', [CampaignOrbitController::class, 'toggleTesting'])->name('toggle-testing');
    });

    Route::prefix('campaign-soundbox')->name('campaign-soundbox.')->middleware('auth')->group(function () {
        Route::get('/', [CampaignSoundboxController::class, 'index'])->name('index');
        Route::get('/create', [CampaignSoundboxController::class, 'create'])->name('create');
        Route::get('/data', [CampaignSoundboxController::class, 'data'])->name('data');
        Route::post('/store', [CampaignSoundboxController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CampaignSoundboxController::class, 'edit'])->name('edit');
        Route::put('/{campaignSoundbox}', [CampaignSoundboxController::class, 'update'])->name('update');
        Route::get('/{id}', [CampaignSoundboxController::class, 'show'])->name('show');
        Route::delete('/{id}', [CampaignSoundboxController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [CampaignSoundboxController::class, 'download'])->name('download');
        Route::post('/{id}/activate', [CampaignSoundboxController::class, 'activate'])->name('activate');
        Route::post('/{id}/toggle-testing', [CampaignSoundboxController::class, 'toggleTesting'])->name('toggle-testing');
    });

    Route::prefix('campaign-nomor-cantik')->name('campaign-nomor-cantik.')->middleware('auth')->group(function () {
        Route::get('/', [CampaignNomorCantikController::class, 'index'])->name('index');
        Route::get('/create', [CampaignNomorCantikController::class, 'create'])->name('create');
        Route::get('/data', [CampaignNomorCantikController::class, 'data'])->name('data');
        Route::post('/store', [CampaignNomorCantikController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CampaignNomorCantikController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CampaignNomorCantikController::class, 'update'])->name('update');
        Route::get('/{id}', [CampaignNomorCantikController::class, 'show'])->name('show');
        Route::delete('/{id}', [CampaignNomorCantikController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [CampaignNomorCantikController::class, 'download'])->name('download');
        Route::post('/{id}/activate', [CampaignNomorCantikController::class, 'activate'])->name('activate');
        Route::post('/{id}/toggle-testing', [CampaignNomorCantikController::class, 'toggleTesting'])->name('toggle-testing');
    });
});

Route::middleware(['auth', 'checkrole:KAM,Admin,Super'])->group(function () {
    Route::get('/campaign-kam-dashboard', [CampaignKamDashboardController::class, 'index'])->name('campaign-kam-dashboard.index');
    Route::get('/campaign-kam-dashboard/data', [CampaignKamDashboardController::class, 'data'])->name('campaign-kam-dashboard.data');

    Route::prefix('campaign-kam')->name('campaign-kam.')->group(function () {
        Route::get('/', [CampaignKamController::class, 'index'])->name('index');
        Route::get('/create', [CampaignKamController::class, 'create'])->name('create');
        Route::get('/data', [CampaignKamController::class, 'data'])->name('data');
        Route::get('/template/download', [CampaignKamController::class, 'downloadTemplate'])->name('template.download');
        Route::post('/store', [CampaignKamController::class, 'store'])->name('store');
        Route::post('/{id}/upload-report', [CampaignKamController::class, 'uploadReport'])->name('upload-report');
        Route::get('/{id}/edit', [CampaignKamController::class, 'edit'])->name('edit');
        Route::put('/{campaignKam}', [CampaignKamController::class, 'update'])->name('update');
        Route::get('/{id}', [CampaignKamController::class, 'show'])->name('show');
        Route::delete('/{id}', [CampaignKamController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [CampaignKamController::class, 'download'])->name('download');
        Route::post('/{id}/activate', [CampaignKamController::class, 'activate'])->name('activate');
        Route::post('/{id}/toggle-testing', [CampaignKamController::class, 'toggleTesting'])->name('toggle-testing');
    });
});

Route::middleware(['auth', 'checkrole:User,Admin,Super'])->group(function () {
    Route::get('/admin/home', function () {
        return redirect()->route('campaign-waba-interaktif.index');
    })->name('admin.home.user');

    Route::prefix('campaign-waba-interaktif')->name('campaign-waba-interaktif.')->group(function () {
        Route::get('/', [CampaignWabaInteraktifController::class, 'index'])->name('index');
        Route::get('/create', [CampaignWabaInteraktifController::class, 'create'])->name('create');
        Route::get('/data', [CampaignWabaInteraktifController::class, 'data'])->name('data');
        Route::post('/store', [CampaignWabaInteraktifController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CampaignWabaInteraktifController::class, 'edit'])->name('edit');
        Route::put('/{campaignWabaInteraktif}', [CampaignWabaInteraktifController::class, 'update'])->name('update');
        Route::get('/{id}', [CampaignWabaInteraktifController::class, 'show'])->name('show');
        Route::delete('/{id}', [CampaignWabaInteraktifController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [CampaignWabaInteraktifController::class, 'download'])->name('download');
        Route::post('/{id}/activate', [CampaignWabaInteraktifController::class, 'activate'])->name('activate');
        Route::post('/{id}/toggle-testing', [CampaignWabaInteraktifController::class, 'toggleTesting'])->name('toggle-testing');
    });
});
