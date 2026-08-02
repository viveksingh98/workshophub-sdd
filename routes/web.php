<?php

use App\Http\Controllers\WorkshopHubController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkshopHubController::class, 'index'])->name('home');
Route::post('/owner/login', [WorkshopHubController::class, 'login'])->name('owner.login');
Route::post('/owner/logout', [WorkshopHubController::class, 'logout'])->name('owner.logout');
Route::post('/bookings', [WorkshopHubController::class, 'storeBooking'])->name('bookings.store');
Route::post('/reservations', [WorkshopHubController::class, 'storeReservation'])->name('reservations.store');
Route::post('/reservations/{reservation}/cancel', [WorkshopHubController::class, 'cancelReservation'])->name('reservations.cancel');
Route::post('/admin/bookings/{booking}/status', [WorkshopHubController::class, 'updateBookingStatus'])->name('admin.bookings.status');
Route::post('/admin/classes', [WorkshopHubController::class, 'storeClass'])->name('admin.classes.store');
Route::post('/admin/students/{student}/notes', [WorkshopHubController::class, 'storeStudentNote'])->name('admin.students.notes.store');
Route::post('/admin/posts', [WorkshopHubController::class, 'storePost'])->name('admin.posts.store');
Route::post('/admin/settings', [WorkshopHubController::class, 'updateSettings'])->name('admin.settings.update');
Route::post('/admin/theme', [WorkshopHubController::class, 'updateTheme'])->name('admin.theme.update');
Route::get('/documents/waiver', [WorkshopHubController::class, 'waiver'])->name('documents.waiver');
