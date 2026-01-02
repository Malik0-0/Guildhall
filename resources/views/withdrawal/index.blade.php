@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-6">Withdraw Gold</h2>

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

    <div class="bg-gray-800 p-6 rounded-lg mb-6">
        <p class="text-gray-300 mb-2">Current Balance:</p>
        <p class="text-3xl font-bold text-yellow-400">{{ number_format(Auth::user()->gold) }} Gold</p>
        <p class="text-sm text-gray-400 mt-2">Conversion Rate: 1 Gold = Rp 100</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Withdrawal Form -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4">Request Withdrawal</h3>
            
            @if ($errors->any())
                <div class="bg-red-600 text-white px-4 py-2 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('withdrawal.store') }}" class="space-y-4">
                @csrf
                
                <div>
                    <label for="gold_amount" class="block text-gray-300 mb-2">
                        Gold Amount (Minimum: 100 gold = Rp 10,000)
                    </label>
                    <input 
                        type="number" 
                        id="gold_amount" 
                        name="gold_amount" 
                        min="100" 
                        max="100000"
                        value="{{ old('gold_amount') }}"
                        required
                        class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                        oninput="updateCashAmount(this.value)"
                    >
                    <p class="text-sm text-gray-400 mt-1">You will receive: <span id="cash_amount" class="text-green-400 font-semibold">Rp 0</span></p>
                </div>

                <div>
                    <label for="bank_name" class="block text-gray-300 mb-2">Bank Name</label>
                    <select 
                        id="bank_name" 
                        name="bank_name" 
                        required
                        class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option value="">Select Bank</option>
                        <option value="BCA" {{ old('bank_name') == 'BCA' ? 'selected' : '' }}>BCA (Bank Central Asia)</option>
                        <option value="Mandiri" {{ old('bank_name') == 'Mandiri' ? 'selected' : '' }}>Bank Mandiri</option>
                        <option value="BNI" {{ old('bank_name') == 'BNI' ? 'selected' : '' }}>BNI (Bank Negara Indonesia)</option>
                        <option value="BRI" {{ old('bank_name') == 'BRI' ? 'selected' : '' }}>BRI (Bank Rakyat Indonesia)</option>
                        <option value="CIMB Niaga" {{ old('bank_name') == 'CIMB Niaga' ? 'selected' : '' }}>CIMB Niaga</option>
                        <option value="Danamon" {{ old('bank_name') == 'Danamon' ? 'selected' : '' }}>Bank Danamon</option>
                        <option value="Permata" {{ old('bank_name') == 'Permata' ? 'selected' : '' }}>Bank Permata</option>
                        <option value="Other" {{ old('bank_name') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label for="account_number" class="block text-gray-300 mb-2">Account Number</label>
                    <input 
                        type="text" 
                        id="account_number" 
                        name="account_number" 
                        value="{{ old('account_number') }}"
                        required
                        class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Enter your bank account number"
                    >
                </div>

                <div>
                    <label for="account_holder_name" class="block text-gray-300 mb-2">Account Holder Name</label>
                    <input 
                        type="text" 
                        id="account_holder_name" 
                        name="account_holder_name" 
                        value="{{ old('account_holder_name', Auth::user()->name) }}"
                        required
                        class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Enter account holder name"
                    >
                </div>

                <div class="bg-yellow-900/20 border border-yellow-700 rounded p-4 text-sm">
                    <p class="text-yellow-300 mb-2">⚠️ Important Information:</p>
                    <ul class="text-gray-300 space-y-1 list-disc list-inside">
                        <li>Withdrawals are processed within 1-3 business days</li>
                        <li>Gold will be deducted immediately upon submission</li>
                        <li>Minimum withdrawal: 100 gold (Rp 10,000)</li>
                        <li>Maximum withdrawal: 100,000 gold (Rp 10,000,000)</li>
                        <li>Please ensure your bank account details are correct</li>
                    </ul>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-green-600 hover:bg-green-500 px-6 py-3 rounded font-semibold transition-colors"
                    onclick="return confirm('Are you sure you want to withdraw this amount? Your gold will be deducted immediately.')"
                >
                    Submit Withdrawal Request
                </button>
            </form>
        </div>

        <!-- Withdrawal History -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4">Recent Withdrawals</h3>
            
            @if($withdrawals->isEmpty())
                <p class="text-gray-400 text-center py-8">No withdrawal history yet.</p>
            @else
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($withdrawals as $withdrawal)
                        <div class="bg-gray-900 rounded p-4 border border-gray-700">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-semibold text-white">{{ number_format($withdrawal->gold_amount) }} Gold</p>
                                    <p class="text-sm text-green-400">Rp {{ number_format($withdrawal->cash_amount, 0, ',', '.') }}</p>
                                </div>
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    {{ $withdrawal->isCompleted() ? 'bg-green-900 text-green-300' : 
                                       ($withdrawal->isRejected() ? 'bg-red-900 text-red-300' :
                                       ($withdrawal->isProcessing() ? 'bg-blue-900 text-blue-300' : 
                                       'bg-yellow-900 text-yellow-300')) }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-400 space-y-1">
                                <p>Bank: {{ $withdrawal->bank_name }}</p>
                                <p>Account: {{ substr($withdrawal->account_number, 0, 4) }}****{{ substr($withdrawal->account_number, -4) }}</p>
                                <p>Requested: {{ $withdrawal->created_at->diffForHumans() }}</p>
                                @if($withdrawal->isRejected() && $withdrawal->rejection_reason)
                                    <p class="text-red-400 mt-2">Reason: {{ $withdrawal->rejection_reason }}</p>
                                @endif
                                @if($withdrawal->isCompleted() && $withdrawal->processed_at)
                                    <p class="text-green-400 mt-2">Processed: {{ $withdrawal->processed_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('withdrawal.history') }}" class="text-green-400 hover:text-green-300 text-sm">
                        View All Withdrawals →
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function updateCashAmount(goldAmount) {
    const conversionRate = {{ $conversionRate ?? 100 }};
    const cashAmount = goldAmount * conversionRate;
    const cashAmountElement = document.getElementById('cash_amount');
    if (cashAmountElement) {
        cashAmountElement.textContent = 'Rp ' + cashAmount.toLocaleString('id-ID');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const goldAmountInput = document.getElementById('gold_amount');
    if (goldAmountInput && goldAmountInput.value) {
        updateCashAmount(goldAmountInput.value);
    }
});
</script>
@endsection

