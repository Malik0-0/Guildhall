@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Withdrawal History</h2>
        <a href="{{ route('withdrawal.index') }}" class="text-green-400 hover:text-green-300 text-sm">
            ← Back to Withdraw
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-600 text-white px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($withdrawals->isEmpty())
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <p class="text-gray-400 text-lg mb-4">No withdrawal history</p>
            <a href="{{ route('withdrawal.index') }}" class="text-green-400 hover:text-green-300">
                Make your first withdrawal →
            </a>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Withdrawal ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Gold Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Cash Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Bank Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($withdrawals as $withdrawal)
                            <tr class="hover:bg-gray-750">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-white">{{ $withdrawal->withdrawal_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-yellow-400 font-semibold">{{ number_format($withdrawal->gold_amount) }} Gold</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-green-400 font-semibold">Rp {{ number_format($withdrawal->cash_amount, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-300">
                                        <div>{{ $withdrawal->bank_name }}</div>
                                        <div class="text-xs text-gray-400">{{ substr($withdrawal->account_number, 0, 4) }}****{{ substr($withdrawal->account_number, -4) }}</div>
                                        <div class="text-xs text-gray-400">{{ $withdrawal->account_holder_name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium
                                        {{ $withdrawal->isCompleted() ? 'bg-green-900 text-green-300' : 
                                           ($withdrawal->isRejected() ? 'bg-red-900 text-red-300' :
                                           ($withdrawal->isProcessing() ? 'bg-blue-900 text-blue-300' : 
                                           'bg-yellow-900 text-yellow-300')) }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                    @if($withdrawal->isRejected() && $withdrawal->rejection_reason)
                                        <div class="mt-2 text-xs text-red-400 max-w-xs">
                                            {{ Str::limit($withdrawal->rejection_reason, 50) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-400">
                                        <div>{{ $withdrawal->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs">{{ $withdrawal->created_at->format('H:i') }}</div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $withdrawals->links() }}
        </div>
    @endif
</div>
@endsection

