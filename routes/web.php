<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\WorkshopHubController;
use Illuminate\Support\Facades\Route;

// Public site
Route::get('/', [WorkshopHubController::class, 'index'])->name('home');
Route::get('/booking/options', [WorkshopHubController::class, 'bookingOptions'])->name('booking.options');
Route::post('/bookings', [WorkshopHubController::class, 'storeBooking'])->middleware('throttle:10,1')->name('bookings.store');
Route::post('/reservations', [WorkshopHubController::class, 'storeReservation'])->name('reservations.store');
Route::post('/reservations/{reservation}/cancel', [WorkshopHubController::class, 'cancelReservation'])->name('reservations.cancel');
Route::get('/theme-preview/{theme}', [WorkshopHubController::class, 'themePreview'])->name('theme.preview');

// Setup wizard (only while no owner exists)
Route::get('/setup', [SetupController::class, 'show'])->name('setup');
Route::post('/setup', [SetupController::class, 'install'])->name('setup.install');

// Owner auth — Unit 35: /studio-access, three fields, one door
Route::get('/studio-access', [AuthController::class, 'show'])->name('login');
Route::post('/studio-access', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
Route::post('/studio-logout', [AuthController::class, 'logout'])->name('logout');

// Studio dashboard — Unit 45: every /studio-dashboard URL requires auth
Route::middleware('auth')->prefix('studio-dashboard')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/availability', [DashboardController::class, 'saveAvailability'])->name('dashboard.availability');
    Route::post('/holidays', [DashboardController::class, 'storeHoliday'])->name('dashboard.holidays.store');
    Route::post('/holidays/{holiday}/delete', [DashboardController::class, 'deleteHoliday'])->name('dashboard.holidays.delete');

    Route::post('/bookings', [DashboardController::class, 'storeBooking'])->name('dashboard.bookings.store');
    Route::post('/bookings/{booking}', [DashboardController::class, 'updateBooking'])->name('dashboard.bookings.update');
    Route::get('/students/search', [DashboardController::class, 'searchStudents'])->name('dashboard.students.search');

    Route::post('/events', [DashboardController::class, 'storeEvent'])->name('dashboard.events.store');
    Route::post('/events/{event}/delete', [DashboardController::class, 'deleteEvent'])->name('dashboard.events.delete');

    Route::post('/students/{student}', [DashboardController::class, 'updateStudent'])->name('dashboard.students.update');
    Route::post('/students/{student}/notes', [DashboardController::class, 'storeNote'])->name('dashboard.students.notes');
    Route::post('/students/{student}/records', [DashboardController::class, 'storeRecord'])->name('dashboard.students.records');
    Route::get('/students/{student}/waiver', [DashboardController::class, 'waiver'])->name('dashboard.students.waiver');
    Route::get('/waiver-template', [DashboardController::class, 'waiverBlank'])->name('dashboard.waiver.blank');

    Route::post('/posts', [DashboardController::class, 'storePost'])->name('dashboard.posts.store');
    Route::post('/posts/{post}', [DashboardController::class, 'updatePost'])->name('dashboard.posts.update');
    Route::post('/posts/{post}/delete', [DashboardController::class, 'deletePost'])->name('dashboard.posts.delete');

    Route::post('/faqs', [DashboardController::class, 'storeFaq'])->name('dashboard.faqs.store');
    Route::post('/faqs/{faq}/delete', [DashboardController::class, 'deleteFaq'])->name('dashboard.faqs.delete');

    Route::post('/settings', [DashboardController::class, 'updateSettings'])->name('dashboard.settings');
    Route::post('/theme', [DashboardController::class, 'updateTheme'])->name('dashboard.theme');
    Route::post('/images', [DashboardController::class, 'uploadImage'])->name('dashboard.images');
});
