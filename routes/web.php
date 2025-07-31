<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('contacts.index');
    }
    return redirect()->route('login');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('contacts', \App\Http\Controllers\ContactController::class);

    // Merge contacts routes
    Route::get('contacts/{contact}/merge', [\App\Http\Controllers\ContactController::class, 'showMerge'])->name('contacts.showMerge');
    Route::post('contacts/{contact}/merge', [\App\Http\Controllers\ContactController::class, 'merge'])->name('contacts.merge');

});

require __DIR__.'/auth.php';
