<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\WithdrawalController;

Route::get('/', fn () => view('landing'));
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/register', fn () => view('auth.register'));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Quest routes - public first
Route::get('/quests', [QuestController::class, 'index'])->name('quests.index');

// Authenticated quest routes - specific routes MUST come before parameterized routes
Route::middleware('auth')->group(function () {
    Route::get('/quests/create', [QuestController::class, 'create'])->name('quests.create');
    Route::get('/quests/my-quests', [QuestController::class, 'myQuests'])->name('quests.my-quests');
    Route::post('/quests', [QuestController::class, 'store'])->name('quests.store');
    Route::post('/quests/{id}/cancel', [QuestController::class, 'cancel'])->name('quests.cancel');
    Route::get('/quests/{id}/complete', [QuestController::class, 'showCompleteForm'])->name('quests.complete');
    Route::post('/quests/{id}/complete', [QuestController::class, 'complete'])->name('quests.complete.submit');
    Route::post('/quests/{id}/approve', [QuestController::class, 'approve'])->name('quests.approve');
    Route::post('/quests/{id}/reject', [QuestController::class, 'reject'])->name('quests.reject');
    Route::get('/quests/{id}/files/{fileIndex}', [QuestController::class, 'downloadFile'])->name('quests.download.file');
    
    // Proposal routes
    Route::post('/quests/{questId}/proposals', [ProposalController::class, 'apply'])->name('proposals.apply');
    Route::get('/quests/{questId}/proposals', [ProposalController::class, 'index'])->name('proposals.index');
    Route::post('/quests/{questId}/proposals/{proposalId}/accept', [ProposalController::class, 'accept'])->name('proposals.accept');
    Route::post('/top-up', [AuthController::class, 'topUp'])->name('top-up');
    
    // Message routes
    Route::get('/quests/{questId}/messages', [\App\Http\Controllers\MessageController::class, 'thread'])->name('messages.thread');
    Route::post('/quests/{questId}/messages', [\App\Http\Controllers\MessageController::class, 'send'])->name('messages.send');
    Route::get('/api/messages/unread-count', [\App\Http\Controllers\MessageController::class, 'unreadCount'])->name('messages.unread-count');
    Route::get('/api/messages/unread-by-quest', [\App\Http\Controllers\MessageController::class, 'unreadByQuest'])->name('messages.unread-by-quest');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profile.show.user');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/test', function() {
        return 'Profile routes are working!';
    })->name('profile.test');
    Route::get('/profile/reviews', [ProfileController::class, 'reviews'])->name('profile.reviews');
    Route::get('/profile/{username}/reviews', [ProfileController::class, 'reviews'])->name('profile.reviews.user');
    Route::post('/profile/update-stats', [ProfileController::class, 'updateStats'])->name('profile.update-stats');
    
    // Review routes
    Route::get('/reviews/{questId}/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews/{questId}', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{id}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{id}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    
    // Skill routes
    Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
    Route::get('/skills/create', [SkillController::class, 'create'])->name('skills.create');
    Route::post('/skills', [SkillController::class, 'store'])->name('skills.store');
    Route::get('/skills/{id}/edit', [SkillController::class, 'edit'])->name('skills.edit');
    Route::put('/skills/{id}', [SkillController::class, 'update'])->name('skills.update');
    Route::delete('/skills/{id}', [SkillController::class, 'destroy'])->name('skills.destroy');
    Route::get('/api/skills', [SkillController::class, 'apiIndex'])->name('skills.api');
    Route::get('/skills/search', [SkillController::class, 'search'])->name('skills.search');
    Route::get('/skills/popular', [SkillController::class, 'popular'])->name('skills.popular');
    
    // Midtrans payment routes (Top Up - for Patrons)
    Route::get('/top-up', function () { return view('top-up'); })->name('top-up.page');
    Route::post('/payment/create', [MidtransController::class, 'createPayment'])->name('payment.create');
    Route::get('/payment/finish', [MidtransController::class, 'paymentFinish'])->name('payment.finish');
    Route::get('/payment/error', [MidtransController::class, 'paymentError'])->name('payment.error');
    Route::get('/payment/pending', [MidtransController::class, 'paymentPending'])->name('payment.pending');
    Route::post('/payment/webhook', [MidtransController::class, 'webhook'])->name('payment.webhook');
    Route::get('/payment-history', [MidtransController::class, 'paymentHistory'])->name('payment.history');
    
    // Withdrawal routes (for Adventurers)
    Route::get('/withdraw', [WithdrawalController::class, 'index'])->name('withdrawal.index');
    Route::post('/withdraw', [WithdrawalController::class, 'store'])->name('withdrawal.store');
    Route::get('/withdrawal-history', [WithdrawalController::class, 'history'])->name('withdrawal.history');
});

// Public quest detail route - must come AFTER specific routes to avoid conflicts
Route::get('/quests/{id}', [QuestController::class, 'show'])->name('quests.show');
