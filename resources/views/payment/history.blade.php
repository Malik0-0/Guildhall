@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-6">Payment History</h2>

    @if(session('success'))
        <div class="bg-green-600 text-white px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-600 text-white px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-600 text-white px-4 py-2 rounded mb-4">
            {{ session('info') }}
        </div>
    @endif

    <div class="bg-gray-800 p-6 rounded-lg mb-6">
        <p class="text-gray-300 mb-2">Current Balance:</p>
        <p class="text-3xl font-bold text-yellow-400">{{ Auth::user()->gold }} Gold</p>
    </div>

    @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
        <!-- PATRON VIEW: Top-up History + Mission Transactions -->
        <div class="mb-4">
            <a href="/top-up" class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded font-semibold">
                Top Up Gold
            </a>
        </div>

        <!-- Top-up Transactions Section -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4 text-white">💰 Top-up Transactions</h3>
            @if(isset($orders) && $orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full bg-gray-800 rounded-lg overflow-hidden">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Order ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Gold Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Price
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Payment Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $order->order_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="text-yellow-400 font-semibold">{{ number_format($order->gold_amount) }}</span> Gold
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        Rp {{ number_format($order->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($order->status === 'paid')
                                            <span class="bg-green-600 text-white px-2 py-1 rounded text-xs">
                                                Paid
                                            </span>
                                        @elseif($order->status === 'pending')
                                            <span class="bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                                                Pending
                                            </span>
                                        @elseif($order->status === 'failed')
                                            <span class="bg-red-600 text-white px-2 py-1 rounded text-xs">
                                                Failed
                                            </span>
                                        @else
                                            <span class="bg-gray-600 text-white px-2 py-1 rounded text-xs">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $order->payment_type ? ucfirst(str_replace('_', ' ', $order->payment_type)) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $order->created_at->format('M d, Y H:i') }}
                                        @if($order->paid_at)
                                            <br><small class="text-gray-400">Paid: {{ $order->paid_at->format('M d, Y H:i') }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-8 bg-gray-800 rounded-lg">
                    <p class="text-gray-400 mb-4">No top-up transactions found.</p>
                    <a href="/top-up" class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded font-semibold">
                        Make Your First Top Up
                    </a>
                </div>
            @endif
        </div>

        <!-- Mission Transactions Section -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4 text-white">⚔️ Mission Gold Transactions</h3>
            @if(isset($missionTransactions) && $missionTransactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full bg-gray-800 rounded-lg overflow-hidden">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Quest Title
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Adventurer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Gold Spent
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Completed Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($missionTransactions as $quest)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('quests.show', $quest->id) }}" class="text-indigo-400 hover:text-indigo-300">
                                            {{ $quest->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $quest->adventurer->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="text-red-400 font-semibold">-{{ number_format($quest->price) }}</span> Gold
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $quest->approved_at ? $quest->approved_at->format('M d, Y H:i') : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $missionTransactions->links() }}
                </div>
            @else
                <div class="text-center py-8 bg-gray-800 rounded-lg">
                    <p class="text-gray-400">No completed missions found.</p>
                </div>
            @endif
        </div>

    @else
        <!-- ADVENTURER VIEW: Withdrawal History + Mission Transactions -->
        <div class="mb-4">
            <a href="/withdraw" class="bg-green-600 hover:bg-green-500 px-4 py-2 rounded font-semibold">
                Withdraw Gold
            </a>
        </div>

        <!-- Withdrawal Transactions Section -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4 text-white">💸 Withdrawal History</h3>
            @if(isset($withdrawals) && $withdrawals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full bg-gray-800 rounded-lg overflow-hidden">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Withdrawal ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Gold Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Cash Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($withdrawals as $withdrawal)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $withdrawal->withdrawal_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="text-yellow-400 font-semibold">{{ number_format($withdrawal->gold_amount) }}</span> Gold
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        Rp {{ number_format($withdrawal->cash_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($withdrawal->status === 'completed')
                                            <span class="bg-green-600 text-white px-2 py-1 rounded text-xs">
                                                Completed
                                            </span>
                                        @elseif($withdrawal->status === 'processing')
                                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs">
                                                Processing
                                            </span>
                                        @elseif($withdrawal->status === 'pending')
                                            <span class="bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                                                Pending
                                            </span>
                                        @elseif($withdrawal->status === 'rejected')
                                            <span class="bg-red-600 text-white px-2 py-1 rounded text-xs">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="bg-gray-600 text-white px-2 py-1 rounded text-xs">
                                                {{ ucfirst($withdrawal->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $withdrawal->created_at->format('M d, Y H:i') }}
                                        @if($withdrawal->processed_at)
                                            <br><small class="text-gray-400">Processed: {{ $withdrawal->processed_at->format('M d, Y H:i') }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $withdrawals->links() }}
                </div>
            @else
                <div class="text-center py-8 bg-gray-800 rounded-lg">
                    <p class="text-gray-400 mb-4">No withdrawal history found.</p>
                    <a href="/withdraw" class="bg-green-600 hover:bg-green-500 px-4 py-2 rounded font-semibold">
                        Make Your First Withdrawal
                    </a>
                </div>
            @endif
        </div>

        <!-- Mission Transactions Section -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-4 text-white">⚔️ Mission Gold Earnings</h3>
            @if(isset($missionTransactions) && $missionTransactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full bg-gray-800 rounded-lg overflow-hidden">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Quest Title
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Patron
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Gold Earned
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Completed Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($missionTransactions as $quest)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('quests.show', $quest->id) }}" class="text-indigo-400 hover:text-indigo-300">
                                            {{ $quest->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $quest->patron->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="text-green-400 font-semibold">+{{ number_format($quest->price) }}</span> Gold
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $quest->approved_at ? $quest->approved_at->format('M d, Y H:i') : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $missionTransactions->links() }}
                </div>
            @else
                <div class="text-center py-8 bg-gray-800 rounded-lg">
                    <p class="text-gray-400">No completed missions found.</p>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
