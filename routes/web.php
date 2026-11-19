<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('files.index');
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/dashboard', function () {
    return redirect()->route('files.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('files', FileController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::get('files/{file}/download', [FileController::class, 'download'])
        ->name('files.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
