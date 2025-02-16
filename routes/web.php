<?php
use App\Livewire\SupportDiagnosis;
use Illuminate\Support\Facades\Route;
use App\Livewire\BrandManager;
use App\Livewire\DeviceManager;
use App\Livewire\ModelManager;
use App\Livewire\ProblemManager;



Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');




Route::get('/', SupportDiagnosis::class)->name("index");

Route::get('/devices', DeviceManager::class)->name("manage.devices");
Route::get('/brands', BrandManager::class)->name("manage.brands");
Route::get('/models', ModelManager::class)->name('manage.models');
Route::get('/problems', ProblemManager::class)->name("manage.problems");

require __DIR__.'/auth.php';
