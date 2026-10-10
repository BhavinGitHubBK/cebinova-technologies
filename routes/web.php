<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DemoSiteController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::post('/analytics/visit', [AnalyticsController::class, 'visit'])
    ->middleware('throttle:analytics')
    ->name('analytics.visit');
Route::post('/analytics/identify', [AnalyticsController::class, 'identify'])
    ->middleware('throttle:analytics')
    ->name('analytics.identify');
Route::post('/analytics/location', [AnalyticsController::class, 'locate'])
    ->middleware('throttle:analytics')
    ->name('analytics.location');
Route::get('/account', [AccountController::class, 'show'])->name('account.show');
Route::post('/account/login', [AccountController::class, 'store'])->name('account.login');
Route::match(['get', 'post'], '/account/logout', [AccountController::class, 'destroy'])->name('account.logout');
Route::post('/chat', [ChatController::class, 'store'])->middleware('throttle:contact')->name('chat.store');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

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
Route::get('/industries/{slug}', [PageController::class, 'industry'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('industries.show');
Route::get('/marketing-packages', [PageController::class, 'marketingPackages'])->name('marketing-packages');
Route::permanentRedirect('/marketing', '/marketing-packages');
Route::permanentRedirect('/packages', '/marketing-packages');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/offers', [PromotionController::class, 'index'])->name('offers.index');
Route::get('/offers/{slug}', [PromotionController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('offers.show');
Route::get('/promotions/surface', [PromotionController::class, 'surface'])->name('promotions.surface');
Route::post('/promotions/quote', [PromotionController::class, 'quote'])->middleware('throttle:promotions')->name('promotions.quote');
Route::post('/promotions/events', [PromotionController::class, 'event'])->middleware('throttle:promotions')->name('promotions.events');
Route::get('/demos', [PageController::class, 'demos'])->name('demos');
Route::get('/demos/{demo}/{path?}', [DemoSiteController::class, 'show'])
    ->whereIn('demo', ['kirana', 'professional-services', 'jewellery-retail'])
    ->where('path', '.*')
    ->name('demos.show');

Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');

Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
