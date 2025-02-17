<?php

use App\Livewire\AnswerList;
use App\Livewire\SupportDiagnosis;
use Illuminate\Support\Facades\Route;
use App\Livewire\BrandManager;
use App\Livewire\DeviceManager;
use App\Livewire\ModelManager;
use App\Livewire\ProblemManager;
use App\Livewire\StaffDiagnosis;
use Livewire\Livewire;

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/solution/public/livewire/update', $handle);
});



Route::middleware(["auth","admin"])->group(function(){
    Route::prefix("admin")->group(function(){
        Route::get('/', SupportDiagnosis::class)->name("index");
        Route::get('/devices', DeviceManager::class)->name("manage.devices");
        Route::get('/brands', BrandManager::class)->name("manage.brands");
        Route::get('/models', ModelManager::class)->name('manage.models');
        Route::get('/problems', ProblemManager::class)->name("manage.problems");
        Route::get('/answers', AnswerList::class)->name('manage.answers');
    });
});
Route::get('/', StaffDiagnosis::class)->name('homepage')->middleware("auth");




require __DIR__.'/auth.php';
