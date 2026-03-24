<?php

use App\Livewire\AnswerList;
use Illuminate\Support\Facades\Route;
use App\Livewire\BrandManager;
use App\Livewire\DeviceManager;
use App\Livewire\ModelManager;
use App\Livewire\ProblemManager;
use App\Livewire\StaffDiagnosis;
use Livewire\Livewire;

use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AdminDiagnosisController;
use App\Livewire\Actions\Logout;

Route::get('/clear-view', function () {
    Artisan::call('view:clear');
    return "View cache cleared successfully!";
});


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/solution/public/livewire/update', $handle);
});

Route::post('/logout', function (Logout $logout) {
    $logout();
    return redirect()->route('login');
})->middleware('auth')->name('logout');



Route::middleware(["auth","admin"])->group(function(){
    Route::prefix("admin")->group(function(){
        Route::get('/', [AdminDiagnosisController::class, 'index'])->name("index");
        Route::get('/diagnosis/tree/{problem}', [AdminDiagnosisController::class, 'tree'])->name('admin.diagnosis.tree');
        Route::get('/diagnosis/questions/{problem}', [AdminDiagnosisController::class, 'questions'])->name('admin.diagnosis.questions');
        Route::post('/diagnosis/clone', [AdminDiagnosisController::class, 'clone'])->name('admin.diagnosis.clone');
        Route::post('/diagnosis/root', [AdminDiagnosisController::class, 'createRootQuestion'])->name('admin.diagnosis.root');
        Route::post('/diagnosis/branch', [AdminDiagnosisController::class, 'createBranchQuestion'])->name('admin.diagnosis.branch');
        Route::put('/diagnosis/question/{question}', [AdminDiagnosisController::class, 'updateQuestion'])->name('admin.diagnosis.question.update');

        // Legacy Livewire diagnosis builder (kept during migration)
        Route::get('/legacy', \App\Livewire\SupportDiagnosis::class)->name('admin.legacy.diagnosis');
        Route::get('/devices', DeviceManager::class)->name("manage.devices");
        Route::get('/brands', BrandManager::class)->name("manage.brands");
        Route::get('/models', ModelManager::class)->name('manage.models');
        Route::get('/problems', ProblemManager::class)->name("manage.problems");
        Route::get('/answers', AnswerList::class)->name('manage.answers');
    });
});
Route::get('/', StaffDiagnosis::class)->name('homepage')->middleware("auth");




require __DIR__.'/auth.php';
