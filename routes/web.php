<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HrVacancyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CompanyController::class, 'index'])->name('home');
Route::get('/{locale}', [CompanyController::class, 'index'])
    ->where('locale', 'id|en|zh')->name('company');
Route::get('/{locale}/news/{article}', [CompanyController::class, 'article'])
    ->where('locale', 'id|en|zh')->where('article', '[0-2]')->name('company.article');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::get('/{locale}/careers', [CareerController::class, 'index'])
    ->where('locale', 'id|en|zh')->name('careers.index');
Route::get('/{locale}/careers/{vacancy}', [CareerController::class, 'show'])
    ->where('locale', 'id|en|zh')->whereNumber('vacancy')->name('careers.show');
Route::middleware(['auth', 'verified', 'can:manage-careers'])->prefix('hr')->name('hr.')->group(function () {
    Route::resource('articles', ArticleController::class)->except(['show', 'destroy']);
    Route::post('instagram/sync', [ArticleController::class, 'sync'])->middleware('throttle:3,1')->name('instagram.sync');
    Route::resource('vacancies', HrVacancyController::class)->except(['show', 'destroy']);
});

Route::get('/{locale}/articles/{post}', [ArticleController::class, 'show'])
    ->where('locale', 'id|en|zh')->whereNumber('post')->name('articles.show');
