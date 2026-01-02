<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransController extends Controller
{
    /**
     * Create payment transaction.
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'gold_package' => 'required|integer|min:0|max:5',
        ]);

        $user = Auth::user();
        $packages = config('gold_packages');
        
        if (!$packages || !isset($packages[$request->gold_package])) {
            return response()->json([
                'error' => 'Invalid gold package selected.',
            ], 400);
        }
        
        $package = $packages[$request->gold_package];

        // Generate unique order ID
        $orderId = 'GUILD-' . time() . '-' . Str::random(4);

        // Create order record
        $order = Order::create([
            'order_id' => $orderId,
            'user_id' => $user->id,
            'gold_amount' => $package['gold_amount'],
            'price' => $package['price'],
            'status' => Order::STATUS_PENDING,
        ]);

        // Midtrans configuration
        $serverKey = config('services.midtrans.server_key');
        if (!$serverKey) {
            $order->markAsFailed();
            return response()->json([
                'error' => 'Payment gateway not configured. Please contact support.',
            ], 500);
        }
        
        \Midtrans\Config::$serverKey = $serverKey;
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Transaction details
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $package['price'],
        ];

        // Customer details
        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
        ];

        // Item details
        $itemDetails = [
            [
                'id' => 'GOLD_' . $package['gold_amount'],
                'price' => $package['price'],
                'quantity' => 1,
                'name' => $package['name'] . ' - ' . $package['gold_amount'] . ' Gold',
            ],
        ];

        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('payment.finish'),
                'error' => route('payment.error'),
                'pending' => route('payment.pending'),
            ],
        ];

        try {
            // Check if Midtrans classes are available
            if (!class_exists('Midtrans\Config')) {
                throw new \Exception('Midtrans package not installed. Please run: composer require midtrans/midtrans-php');
            }
            
            if (!class_exists('Midtrans\Snap')) {
                throw new \Exception('Midtrans Snap class not found. Please check if the package is properly installed.');
            }
            
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $orderId,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans payment creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId ?? 'unknown',
                'user_id' => $user->id ?? 'unknown',
            ]);
            
            if (isset($order)) {
                try {
                    $order->markAsFailed();
                } catch (\Exception $orderError) {
                    Log::error('Failed to mark order as failed', [
                        'order_error' => $orderError->getMessage(),
                    ]);
                }
            }
            
            // Return more detailed error in development
            $errorMessage = config('app.debug') 
                ? 'Failed to create payment: ' . $e->getMessage() 
                : 'Failed to create payment. Please try again.';
            
            return response()->json([
                'error' => $errorMessage,
            ], 500);
        }
    }

    /**
     * Handle payment success callback.
     */
    public function paymentFinish(Request $request)
    {
        $orderId = $request->order_id;
        $order = Order::where('order_id', $orderId)->first();

        if (!$order) {
            return redirect('/top-up')->with('error', 'Order not found.');
        }

        // If already paid, just redirect
        if ($order->isPaid()) {
            // Refresh user session to get updated gold
            $user = $order->user;
            if (Auth::check() && Auth::id() === $user->id) {
                Auth::user()->refresh();
            }
            return redirect('/payment-history')->with('success', 'Payment completed successfully!');
        }

        // Verify payment status with Midtrans
        try {
            // Configure Midtrans
            $serverKey = config('services.midtrans.server_key');
            if (!$serverKey) {
                return redirect('/top-up')->with('error', 'Payment gateway not configured.');
            }
            
            if (!class_exists('Midtrans\Transaction')) {
                Log::error('Midtrans Transaction class not found');
                return redirect('/payment-history')->with('info', 'Payment verification unavailable. Please check back later.');
            }
            
            \Midtrans\Config::$serverKey = $serverKey;
            \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
            
            // Check transaction status
            $status = \Midtrans\Transaction::status($orderId);
            
            // Handle both object and array responses
            if (is_object($status)) {
                $transactionStatus = $status->transaction_status ?? null;
                $paymentType = $status->payment_type ?? null;
                $statusArray = (array) $status;
            } else {
                $transactionStatus = $status['transaction_status'] ?? null;
                $paymentType = $status['payment_type'] ?? null;
                $statusArray = $status;
            }
            
            // Process successful payment
            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                if ($order->isPending()) {
                    $order->markAsPaid($paymentType, $statusArray);
                    
                    // Add gold to user
                    $user = $order->user;
                    $user->addGold($order->gold_amount);
                    
                    // Refresh authenticated user's session
                    if (Auth::check() && Auth::id() === $user->id) {
                        Auth::user()->refresh();
                    }
                    
                    return redirect('/payment-history')->with('success', 'Payment completed successfully! Your gold has been added.');
                }
                
                return redirect('/payment-history')->with('success', 'Payment completed successfully!');
            }
            
            // Handle pending payment
            if ($transactionStatus === 'pending') {
                return redirect('/payment-history')->with('info', 'Payment is pending. Please complete your payment.');
            }
            
            // Handle failed payment
            if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                if ($order->isPending()) {
                    $order->markAsFailed();
                }
                return redirect('/top-up')->with('error', 'Payment failed. Please try again.');
            }
            
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect('/payment-history')->with('info', 'Payment is being processed. Please wait a moment.');
    }

    /**
     * Handle payment error callback.
     */
    public function paymentError(Request $request)
    {
        $orderId = $request->order_id;
        $order = Order::where('order_id', $orderId)->first();

        if ($order && $order->isPending()) {
            $order->markAsFailed();
        }

        return redirect('/top-up')->with('error', 'Payment failed. Please try again.');
    }

    /**
     * Handle payment pending callback.
     */
    public function paymentPending(Request $request)
    {
        return redirect('/payment-history')->with('info', 'Payment is pending. Please complete your payment.');
    }

    /**
     * Handle Midtrans webhook.
     */
    public function webhook(Request $request)
    {
        $signatureKey = config('services.midtrans.server_key');
        $orderId = $request->order_id;
        $statusCode = $request->status_code;
        $grossAmount = $request->gross_amount;
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $signatureKey);

        if ($signature !== $request->signature_key) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $order = Order::where('order_id', $orderId)->first();
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $transactionStatus = $request->transaction_status;
        $paymentType = $request->payment_type;

        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                if ($order->isPending()) {
                    $order->markAsPaid($paymentType, $request->all());
                    
                    // Add gold to user
                    $user = $order->user;
                    $user->addGold($order->gold_amount);
                    
                    // Refresh authenticated user's session if they're logged in
                    if (Auth::check() && Auth::id() === $user->id) {
                        Auth::user()->refresh();
                    }
                }
                break;

            case 'deny':
            case 'expire':
            case 'cancel':
                if ($order->isPending()) {
                    $order->markAsFailed();
                }
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Show payment history.
     */
    public function paymentHistory()
    {
        $user = Auth::user();
        
        if ($user->role === User::ROLE_ADVENTURER) {
            // Adventurers see withdrawal history and mission gold transactions
            $withdrawals = \App\Models\Withdrawal::where('user_id', $user->id)
                ->latest()
                ->paginate(10, ['*'], 'withdrawals_page');
                
            // Get completed quests where they earned gold
            $missionTransactions = \App\Models\Quest::where('adventurer_id', $user->id)
                ->where('status', \App\Models\Quest::STATUS_COMPLETED)
                ->with(['patron'])
                ->latest('approved_at')
                ->paginate(10, ['*'], 'missions_page');
                
            return view('payment.history', compact('withdrawals', 'missionTransactions'));
        } else {
            // Patrons (Quest Givers) see top-up history and mission gold transactions
            $orders = Order::where('user_id', $user->id)
                ->latest()
                ->with('user')
                ->paginate(10, ['*'], 'topup_page');
                
            // Get completed quests where they spent gold
            $missionTransactions = \App\Models\Quest::where('patron_id', $user->id)
                ->where('status', \App\Models\Quest::STATUS_COMPLETED)
                ->with(['adventurer'])
                ->latest('approved_at')
                ->paginate(10, ['*'], 'missions_page');
                
            return view('payment.history', compact('orders', 'missionTransactions'));
        }
    }
}
