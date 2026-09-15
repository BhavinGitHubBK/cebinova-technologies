<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');

Route::get('/services', [PageController::class, 'services'])->name('services.index');
Route::get('/services/{slug}', [PageController::class, 'service'])
    ->whereIn('slug', [
        'web-development',
        'ecommerce',
        'custom-software',
        'ai-automation',
        'digital-business',
        'digital-growth',
    ])
    ->name('services.show');

Route::get('/solutions', [PageController::class, 'solutions'])->name('solutions');
Route::permanentRedirect('/solutions/kirana', '/pricing');
Route::get('/solutions/{slug}', [PageController::class, 'solution'])
    ->whereIn('slug', [
        'retail',
        'professional-services',
        'ecommerce',
        'business-management',
        'ai-automation',
    ])
    ->name('solutions.show');
Route::get('/industries', [PageController::class, 'industries'])->name('industries');
Route::get('/marketing-packages', [PageController::class, 'marketingPackages'])->name('marketing-packages');
Route::permanentRedirect('/marketing', '/marketing-packages');
Route::permanentRedirect('/packages', '/marketing-packages');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/portfolio', [PageController::class, 'portfolio'])->name('portfolio');
Route::get('/demos', [PageController::class, 'demos'])->name('demos');
Route::get('/demos/{demo}/{path?}', [\App\Http\Controllers\DemoSiteController::class, 'show'])
    ->whereIn('demo', ['kirana', 'professional-services', 'jewellery-retail'])
    ->where('path', '.*')
    ->name('demos.show');

Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');

Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
