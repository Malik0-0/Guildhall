<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Services\QuestService;
use App\Events\QuestCreated;
use App\Events\QuestStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Helpers\SanitizeHelper;

class QuestController extends Controller
{
    /**
     * Display a listing of quests based on user role and status.
     * - Adventurers: See all open quests globally + their own quests
     * - Patrons: See their own quests (all statuses)
     * - Guests: See all open quests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status', 'all'); // all, open, in_progress, completed
        
        // Get all categories and tags for filters (cached)
        $categories = Cache::remember('categories_list', 3600, function () {
            return Category::active()->withCount('quests')->get();
        });
        
        $tags = Cache::remember('tags_list', 3600, function () {
            return Tag::withCount('quests')->get();
        });

        // Build query based on user role
        if ($user) {
            if ($user->role === User::ROLE_ADVENTURER) {
                // Adventurers see all open quests globally + their own accepted quests
                $query = Quest::with(['patron', 'categories', 'tags', 'adventurer'])
                    ->where(function($q) use ($user) {
                        $q->where('status', Quest::STATUS_OPEN) // All open quests
                          ->orWhere('adventurer_id', $user->id); // Their own quests
                    });
            } else {
                // Patrons see their own quests (all statuses)
                $query = Quest::where('patron_id', $user->id)
                    ->with(['adventurer', 'categories', 'tags']);
            }
        } else {
            // Guests see all open quests
            $query = Quest::open()->with(['patron', 'categories', 'tags']);
        }

        // Apply status filter if not 'all'
        if ($status !== 'all') {
            if ($status === 'open') {
                $query->where('status', Quest::STATUS_OPEN);
            } elseif ($status === 'in_progress') {
                $query->whereIn('status', [Quest::STATUS_ACCEPTED, Quest::STATUS_PENDING_APPROVAL]);
            } elseif ($status === 'completed') {
                $query->where('status', Quest::STATUS_COMPLETED);
            }
        }

        // Apply filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('tag')) {
            $query->byTag($request->tag);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('min_price')) {
            $query->priceRange($request->min_price, $request->max_price ?? null);
        } elseif ($request->filled('max_price')) {
            $query->priceRange(null, $request->max_price);
        }

        if ($request->filled('start_date')) {
            $query->dateRange($request->start_date, $request->end_date ?? null);
        } elseif ($request->filled('end_date')) {
            $query->dateRange(null, $request->end_date);
        }

        // Paginate results
        $quests = $query->latest()->paginate(12)->appends($request->except('page'));

        // Calculate stats for the current user
        if ($user) {
            if ($user->role === User::ROLE_ADVENTURER) {
                $stats = [
                    'all' => Quest::where('status', Quest::STATUS_OPEN)->count() + 
                           Quest::where('adventurer_id', $user->id)->count(),
                    'open' => Quest::open()->count(),
                    'in_progress' => Quest::where('adventurer_id', $user->id)
                        ->whereIn('status', [Quest::STATUS_ACCEPTED, Quest::STATUS_PENDING_APPROVAL])
                        ->count(),
                    'completed' => Quest::where('adventurer_id', $user->id)
                        ->where('status', Quest::STATUS_COMPLETED)
                        ->count(),
                ];
            } else {
                $stats = [
                    'all' => Quest::where('patron_id', $user->id)->count(),
                    'open' => Quest::where('patron_id', $user->id)
                        ->where('status', Quest::STATUS_OPEN)
                        ->count(),
                    'in_progress' => Quest::where('patron_id', $user->id)
                        ->whereIn('status', [Quest::STATUS_ACCEPTED, Quest::STATUS_PENDING_APPROVAL])
                        ->count(),
                    'completed' => Quest::where('patron_id', $user->id)
                        ->where('status', Quest::STATUS_COMPLETED)
                        ->count(),
                ];
            }
        } else {
            // Guest stats - only open quests
            $stats = Cache::remember('quest_stats_guest', 300, function () {
                return [
                    'all' => Quest::where('status', Quest::STATUS_OPEN)->count(),
                    'open' => Quest::where('status', Quest::STATUS_OPEN)->count(),
                    'in_progress' => 0,
                    'completed' => 0,
                ];
            });
        }

        return view('quests.index', compact('quests', 'categories', 'tags', 'stats', 'status'));
    }

    /**
     * Show the form for creating a new quest.
     */
    public function create()
    {
        // Only quest givers (patrons) can create quests
        if (Auth::user()->role !== User::ROLE_QUEST_GIVER) {
            return redirect()->route('quests.index')->with('error', 'Only patrons can create quests.');
        }

        $categories = Category::active()->orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('quests.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created quest in storage.
     */
    public function store(Request $request)
    {
        // Only quest givers (patrons) can create quests
        if (Auth::user()->role !== User::ROLE_QUEST_GIVER) {
            return redirect('/quests')->with('error', 'Only patrons can create quests.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:1',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $user = Auth::user();

        // Check if user has enough gold
        if (!$user->hasEnoughGold($validated['price'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You do not have enough gold. Your balance: ' . number_format($user->gold) . ' gold coins.');
        }

        try {
            $quest = null;
            
            DB::transaction(function () use ($validated, $user, &$quest) {
                $quest = Quest::create([
                    'title' => SanitizeHelper::sanitizeText($validated['title']),
                    'description' => SanitizeHelper::sanitizeHtml($validated['description']),
                    'price' => (int)$validated['price'],
                    'status' => Quest::STATUS_OPEN,
                    'patron_id' => $user->id,
                ]);

                // Attach categories and tags
                if (!empty($validated['categories'])) {
                    $quest->categories()->attach($validated['categories']);
                }
                if (!empty($validated['tags'])) {
                    $quest->tags()->attach($validated['tags']);
                }

                // Deduct gold from user
                $user->deductGold($validated['price']);
            });

            // Broadcast the new quest
            if ($quest) {
                $quest->refresh(); // Refresh to load relationships
                broadcast(new QuestCreated($quest));
            }

            // Clear quest stats cache
            Cache::forget('quest_stats');

            return redirect()->route('quests.index')->with('success', 'Quest created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display a listing of quests for the authenticated user.
     * For patrons: Shows quests they created.
     * For adventurers: Shows quests they accepted.
     */
    public function myQuests()
    {
        $user = Auth::user();

        if ($user->role === User::ROLE_QUEST_GIVER) {
            // Patrons see their created quests
            $quests = Quest::where('patron_id', $user->id)
                ->with(['adventurer', 'categories', 'tags'])
                ->latest()
                ->paginate(10);
        } else {
            // Adventurers see their accepted quests
            $quests = Quest::where('adventurer_id', $user->id)
                ->whereIn('status', [Quest::STATUS_ACCEPTED, Quest::STATUS_COMPLETED])
                ->with(['patron', 'categories', 'tags'])
                ->latest()
                ->paginate(10);
        }

        return view('quests.my-quests', compact('quests'));
    }

    /**
     * Show the form to submit evidence for completing a quest.
     */
    public function showCompleteForm($id)
    {
        $quest = Quest::with(['patron', 'adventurer', 'categories', 'tags', 'reviews.reviewer'])->findOrFail($id);

        // Only the adventurer who accepted the quest can submit evidence
        if ($quest->adventurer_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only complete quests you have accepted.');
        }

        if (!$quest->canBeCompleted()) {
            return redirect()->back()->with('error', 'This quest cannot be completed.');
        }

        return view('quests.complete', compact('quest'));
    }

    /**
     * Display the specified quest.
     */
    public function show($id)
    {
        $user = Auth::user();
        $quest = Quest::with(['patron', 'adventurer', 'categories', 'tags', 'reviews.reviewer', 'proposals.adventurer.profile'])
            ->findOrFail($id);

        // Check if current user has already proposed to this quest
        $userProposal = null;
        if ($user && $user->role === User::ROLE_ADVENTURER) {
            $userProposal = $quest->proposals()
                ->where('adventurer_id', $user->id)
                ->first();
        }

        // Get related quests based on categories
        $relatedQuests = Quest::where('id', '!=', $id)
            ->where('status', Quest::STATUS_OPEN)
            ->whereHas('categories', function($query) use ($quest) {
                $query->whereIn('categories.id', $quest->categories->pluck('id'));
            })
            ->with(['patron', 'categories'])
            ->limit(3)
            ->get();

        return view('quests.show', compact('quest', 'relatedQuests', 'userProposal'));
    }

    /**
     * Complete a quest by submitting evidence and processing payment.
     */
    public function complete(Request $request, $id)
    {
        $quest = Quest::findOrFail($id);

        // Only the adventurer who accepted the quest can complete it
        if ($quest->adventurer_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only complete quests you have accepted.');
        }

        if (!$quest->canBeCompleted()) {
            return redirect()->back()->with('error', 'This quest cannot be completed.');
        }

        $validated = $request->validate([
            'evidence' => ['required', 'string', 'min:20', 'max:10000'],
            'evidence_files.*' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,txt'],
        ]);

        try {
            $evidenceFiles = [];
            
            // Handle file uploads
            if ($request->hasFile('evidence_files')) {
                foreach ($request->file('evidence_files') as $file) {
                    $path = $file->store('evidence/' . $quest->id, 'public');
                    $evidenceFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];
                }
            }

            $previousStatus = $quest->status;
            $questService = new QuestService();
            $questService->submitEvidence($quest, $validated['evidence'], $evidenceFiles);

            // Broadcast status change
            $quest->refresh();
            broadcast(new QuestStatusChanged($quest, 'completed', $previousStatus));

            // Clear quest stats cache and quest index cache
            Cache::forget('quest_stats');
            Cache::flush(); // Clear all quest caches

            $message = 'Evidence submitted successfully! The patron will review your submission. You will be paid automatically after 72 hours if no action is taken.';

            return redirect()->route('quests.my-quests')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Download evidence file.
     */
    public function downloadFile($id, $fileIndex)
    {
        $quest = Quest::findOrFail($id);

        // Only patron or adventurer involved in the quest can download files
        if (!Auth::user() || ($quest->patron_id !== Auth::id() && $quest->adventurer_id !== Auth::id())) {
            return redirect()->back()->with('error', 'You do not have permission to download files from this quest.');
        }

        if (!$quest->evidence_files || !isset($quest->evidence_files[$fileIndex])) {
            return redirect()->back()->with('error', 'File not found.');
        }

        $file = $quest->evidence_files[$fileIndex];
        $filePath = storage_path('app/public/' . $file['path']);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found on server.');
        }

        return response()->download($filePath, $file['name']);
    }

    /**
     * Cancel a quest.
     */
    public function cancel($id)
    {
        $quest = Quest::findOrFail($id);

        // Only the patron who created the quest can cancel it
        if ($quest->patron_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only cancel your own quests.');
        }

        // Check if quest can be cancelled
        if (!$quest->canBeCancelled()) {
            return redirect()->back()->with('error', 'This quest cannot be cancelled.');
        }

        try {
            $previousStatus = $quest->status;
            $questService = new QuestService();
            $questService->cancelQuest($quest);

            // Broadcast status change
            $quest->refresh();
            broadcast(new QuestStatusChanged($quest, 'cancelled', $previousStatus));

            // Clear quest stats cache
            Cache::forget('quest_stats');

            $message = 'Quest cancelled successfully. Your gold (' . number_format($quest->price) . ' coins) has been refunded.';

            return redirect()->route('quests.my-quests')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Approve quest evidence and process payment.
     */
    public function approve($id)
    {
        $quest = Quest::findOrFail($id);

        // Only the patron who created the quest can approve it
        if ($quest->patron_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only approve your own quests.');
        }

        // Check if quest is pending approval
        if (!$quest->isPendingApproval()) {
            return redirect()->back()->with('error', 'This quest is not pending approval.');
        }

        try {
            $previousStatus = $quest->status;
            $questService = new QuestService();
            $result = $questService->approveQuest($quest);

            // Broadcast status change
            $quest->refresh();
            broadcast(new QuestStatusChanged($quest, 'approved', $previousStatus));

            // Clear quest stats cache
            Cache::forget('quest_stats');

            $message = 'Quest approved! Payment of ' . number_format($quest->price) . ' gold coins has been processed.';
            if ($result['leveled_up']) {
                $message .= ' The adventurer leveled up to level ' . $result['new_level'] . '!';
            }

            return redirect()->route('quests.show', $quest->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject quest evidence and request revision.
     */
    public function reject(Request $request, $id)
    {
        $quest = Quest::findOrFail($id);

        // Only the patron who created the quest can reject it
        if ($quest->patron_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only reject evidence for your own quests.');
        }

        // Check if quest is pending approval
        if (!$quest->isPendingApproval()) {
            return redirect()->back()->with('error', 'This quest is not pending approval.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:20', 'max:1000'],
        ]);

        try {
            $previousStatus = $quest->status;
            $questService = new QuestService();
            $questService->rejectEvidence($quest, $validated['rejection_reason']);

            // Broadcast status change
            $quest->refresh();
            broadcast(new QuestStatusChanged($quest, 'rejected', $previousStatus));

            // Clear quest stats cache
            Cache::forget('quest_stats');

            return redirect()->route('quests.show', $quest->id)
                ->with('success', 'Evidence rejected. The adventurer has been notified and can resubmit with improvements.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
