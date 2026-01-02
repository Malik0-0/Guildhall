<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    /**
     * Show the withdrawal page.
     */
    public function index()
    {
        // Only adventurers can withdraw
        if (Auth::user()->role !== User::ROLE_ADVENTURER) {
            return redirect('/quests')->with('error', 'Only adventurers can withdraw gold.');
        }

        $user = Auth::user();
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        // Calculate conversion rate (from gold packages: 1000 gold = 100,000 IDR, so 1 gold = 100 IDR)
        $conversionRate = 100; // 1 gold = 100 IDR

        return view('withdrawal.index', compact('withdrawals', 'conversionRate'));
    }

    /**
     * Process a withdrawal request.
     */
    public function store(Request $request)
    {
        // Only adventurers can withdraw
        if (Auth::user()->role !== User::ROLE_ADVENTURER) {
            return redirect('/quests')->with('error', 'Only adventurers can withdraw gold.');
        }

        $validated = $request->validate([
            'gold_amount' => ['required', 'integer', 'min:100', 'max:100000'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        // Check if user has enough gold
        if ($user->gold < $validated['gold_amount']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You do not have enough gold. Your balance: ' . number_format($user->gold) . ' gold coins.');
        }

        // Calculate cash amount (1 gold = 100 IDR)
        $conversionRate = 100;
        $cashAmount = $validated['gold_amount'] * $conversionRate;

        // Minimum withdrawal amount check (e.g., minimum 10,000 IDR = 100 gold)
        $minCashAmount = 10000;
        if ($cashAmount < $minCashAmount) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Minimum withdrawal amount is ' . number_format($minCashAmount) . ' IDR (' . ($minCashAmount / $conversionRate) . ' gold).');
        }

        try {
            DB::transaction(function () use ($validated, $user, $cashAmount) {
                // Generate unique withdrawal ID
                $withdrawalId = 'WD-' . time() . '-' . Str::random(4);

                // Create withdrawal record
                $withdrawal = Withdrawal::create([
                    'user_id' => $user->id,
                    'withdrawal_id' => $withdrawalId,
                    'gold_amount' => $validated['gold_amount'],
                    'cash_amount' => $cashAmount,
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'account_holder_name' => $validated['account_holder_name'],
                    'status' => Withdrawal::STATUS_PENDING,
                ]);

                // Deduct gold from user
                $user->deductGold($validated['gold_amount']);
            });

            return redirect()->route('withdrawal.index')
                ->with('success', 'Withdrawal request submitted successfully! Your gold has been deducted and will be processed within 1-3 business days.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to process withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Show withdrawal history.
     */
    public function history()
    {
        $user = Auth::user();
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('withdrawal.history', compact('withdrawals'));
    }
}
