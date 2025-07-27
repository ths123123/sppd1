
<?php


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TravelRequestController;
use App\Http\Controllers\ApprovalPimpinanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB; // Added for DB facade
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TemplateDokumenController;
use App\Http\Controllers\NotificationController;

// Authentication routes
Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Home route
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');
// Route untuk daftar SPPD all
// Route untuk daftar SPPD all
Route::get('/travel-requests/all', [TravelRequestController::class, 'indexAll'])->name('travel-requests.indexAll');

// Dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// Protected routes requiring authentication
Route::middleware(['auth'])->group(function () {

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/extended', [ProfileController::class, 'updateExtended'])->name('profile.update.extended');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/ajax', [ProfileController::class, 'updateAjax'])->name('profile.update.ajax');

    // Password update routes
    Route::patch('/password/ajax', [PasswordController::class, 'updateAjax'])->name('password.update.ajax');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Travel Request routes - complete resource
    Route::get('/travel-requests', [TravelRequestController::class, 'indexAll'])->name('travel-requests.index');
    Route::get('/travel-requests/create', [TravelRequestController::class, 'create'])->name('travel-requests.create');
    Route::post('/travel-requests', [TravelRequestController::class, 'store'])->name('travel-requests.store');
    Route::get('/travel-requests/{id}', [TravelRequestController::class, 'show'])->name('travel-requests.show');
    Route::get('/travel-requests/{id}/edit', [TravelRequestController::class, 'edit'])->name('travel-requests.edit');
    Route::patch('/travel-requests/{id}', [TravelRequestController::class, 'update'])->name('travel-requests.update');
    Route::delete('/travel-requests/{id}', [TravelRequestController::class, 'destroy'])->name('travel-requests.destroy');
    Route::post('/travel-requests/{id}/submit', [TravelRequestController::class, 'submit'])->name('travel-requests.submit');
    Route::get('/travel-requests/{id}/export/pdf', [TravelRequestController::class, 'exportPdf'])->name('travel-requests.export.pdf');
    Route::get('/travel-requests/{id}/export/zip', [App\Http\Controllers\ExportController::class, 'exportZip'])->name('travel-requests.export.zip');


    // My Travel Requests
    Route::get('/my-travel-requests', [TravelRequestController::class, 'index'])->name('my-travel-requests.index');
    Route::get('/my-travel-requests/ajax', [App\Http\Controllers\TravelRequestController::class, 'ajaxList'])->name('my-travel-requests.ajax');

    // Approval routes
    Route::get('/approvals', [ApprovalPimpinanController::class, 'index'])->name('approvals.index');
    Route::patch('/approvals/{id}/approve', [ApprovalPimpinanController::class, 'approve'])->name('approvals.approve');
    Route::patch('/approvals/{id}/reject', [ApprovalPimpinanController::class, 'reject'])->name('approvals.reject');
    // Route admin untuk perbaikan data approval
    Route::get('/approval/fix-inconsistent-data', [ApprovalPimpinanController::class, 'fixInconsistentData'])
        ->middleware('role:admin')
        ->name('approval.fix-inconsistent-data');

    // Report routes
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
    Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
    Route::get('/laporan/ajax', [\App\Http\Controllers\LaporanController::class, 'ajaxRekap'])->name('laporan.ajax');

    // Analytics routes
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('/analytics/data', [AnalyticsController::class, 'data'])->name('analytics.data');
    Route::get('/analytics/detail', [AnalyticsController::class, 'detail'])->name('analytics.detail');

    // Settings routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/save', [SettingsController::class, 'saveSettings'])->name('settings.save');
    Route::post('/settings/user/save', [SettingsController::class, 'saveUserSettings'])->name('settings.user.save');

    // User Management (admin only)
    Route::middleware('role_direct:kasubbag,sekretaris,admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('/users/export', [UserManagementController::class, 'export'])->name('users.export');
    });

    // Admin routes - approval and management
    Route::middleware('role_direct:kasubbag,sekretaris,ppk,admin')->group(function () {
        Route::get('/approval/pimpinan', [ApprovalPimpinanController::class, 'index'])->name('approval.pimpinan.index');
        Route::get('/approval/pimpinan/ajax', [\App\Http\Controllers\ApprovalPimpinanController::class, 'ajaxListApproval'])->name('approval.pimpinan.ajax');
        Route::post('/approval/pimpinan/{id}/approve', [ApprovalPimpinanController::class, 'approve'])
            ->middleware('throttle:10,1')
            ->name('approval.pimpinan.approve');
        Route::post('/approval/pimpinan/{id}/reject', [ApprovalPimpinanController::class, 'reject'])
            ->middleware('throttle:10,1')
            ->name('approval.pimpinan.reject');
        Route::post('/approval/pimpinan/{id}/revision', [ApprovalPimpinanController::class, 'revision'])
            ->middleware('throttle:10,1')
            ->name('approval.pimpinan.revision');
        Route::get('/approval/pimpinan/{id}/show', [ApprovalPimpinanController::class, 'show'])->name('approval.pimpinan.show');
    });

    // Admin-only routes
    Route::middleware('role_direct:admin')->group(function () {
        Route::get('/approval/fix-inconsistent-data', [ApprovalPimpinanController::class, 'fixInconsistentData'])->name('approval.fix-inconsistent-data');
    });

    // Document routes
    Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
    Route::get('/dokumen', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/dokumen/saya', [DocumentController::class, 'myDocuments'])->name('documents.my');
    Route::get('/dokumen/semua', [DocumentController::class, 'allDocuments'])
        ->middleware('role_direct:sekretaris,kasubbag,ppk,admin')
        ->name('documents.all');
    Route::get('/dokumen/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/dokumen/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});

// API routes for realtime data
Route::get('/api/dashboard/realtime', [DashboardController::class, 'getRealtimeData'])
    ->middleware(['auth'])
    ->name('dashboard.realtime');

// Endpoint untuk realtime user list (hanya admin)
Route::middleware(['auth', 'role_direct:kasubbag,sekretaris,ppk,admin'])->get('/users/list-json', [UserManagementController::class, 'listJson'])->name('users.list-json');

// Authentication routes
require __DIR__.'/auth.php';

Route::middleware(['auth', 'role_direct:kasubbag,sekretaris'])->group(function () {
    Route::resource('templates', TemplateDokumenController::class)->except(['show']);
    Route::get('templates/{template}/preview', [TemplateDokumenController::class, 'preview'])->name('templates.preview');
    Route::post('templates/{template}/activate', [TemplateDokumenController::class, 'activate'])->name('templates.activate');
});

// API Notifikasi user (untuk navbar polling)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/notifications', [App\Http\Controllers\NotificationController::class, 'apiIndex'])->name('api.notifications');
    Route::post('/api/notifications', [App\Http\Controllers\NotificationController::class, 'apiIndex']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
});

// Hapus route yang sudah dipindahkan
// Route::get('/approval/pimpinan/ajax', [\App\Http\Controllers\ApprovalPimpinanController::class, 'ajaxListApproval'])->name('approval.pimpinan.ajax');
Route::get('/review/ajax', [\App\Http\Controllers\ReviewController::class, 'ajaxListReview'])->name('review.ajax');
