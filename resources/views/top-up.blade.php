@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-6">Top Up Gold</h2>

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

    <h3 class="text-xl font-semibold mb-4">Choose Gold Package</h3>
    
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @foreach(config('gold_packages') as $index => $package)
            <div class="border border-gray-700 p-4 rounded-lg hover:border-indigo-500 transition-colors cursor-pointer gold-package" 
                 data-package="{{ $index }}">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-semibold">{{ $package['name'] }}</h4>
                    @if($index >= 2)
                        <span class="bg-yellow-600 text-xs px-2 py-1 rounded">
                            POPULAR
                        </span>
                    @endif
                </div>
                <div class="text-3xl font-bold text-yellow-400 mb-1">{{ number_format($package['gold_amount']) }}</div>
                <div class="text-sm text-gray-300 mb-2">Gold Coins</div>
                <div class="text-xl text-gray-300 mb-2">Rp {{ number_format($package['price'], 0, ',', '.') }}</div>
                <p class="text-sm text-gray-400">{{ $package['description'] }}</p>
            </div>
        @endforeach
    </div>

    <button id="proceedPayment" class="bg-indigo-600 hover:bg-indigo-500 px-6 py-3 rounded font-semibold disabled:bg-gray-600 disabled:cursor-not-allowed" disabled>
        Select a Package to Continue
    </button>
</div>

<!-- Midtrans Snap Container -->
<div id="midtrans-snap-container"></div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
let selectedPackage = null;
let snapLoaded = false;

// Wait for Midtrans Snap to load
if (typeof snap !== 'undefined') {
    snapLoaded = true;
} else {
    // Poll for snap to be available
    const checkSnap = setInterval(() => {
        if (typeof snap !== 'undefined') {
            snapLoaded = true;
            clearInterval(checkSnap);
        }
    }, 100);
    
    // Timeout after 5 seconds
    setTimeout(() => {
        if (!snapLoaded) {
            clearInterval(checkSnap);
            console.error('Midtrans Snap failed to load');
        }
    }, 5000);
}

document.querySelectorAll('.gold-package').forEach(package => {
    package.addEventListener('click', function() {
        // Remove previous selection
        document.querySelectorAll('.gold-package').forEach(p => {
            p.classList.remove('border-indigo-500', 'bg-indigo-900');
        });
        
        // Add selection to clicked package
        this.classList.add('border-indigo-500', 'bg-indigo-900');
        selectedPackage = this.dataset.package;
        
        // Update button
        const button = document.getElementById('proceedPayment');
        button.disabled = false;
        const selectedPackageData = config(selectedPackage);
        button.textContent = 'Proceed to Payment - Rp ' + formatPrice(selectedPackageData.price);
    });
});

document.getElementById('proceedPayment').addEventListener('click', function() {
    if (selectedPackage === null) return;
    
    const button = this;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Processing...';
    
    // Check if snap is loaded
    if (!snapLoaded && typeof snap === 'undefined') {
        alert('Payment gateway is still loading. Please wait a moment and try again.');
        button.disabled = false;
        button.textContent = originalText;
        return;
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page.');
        button.disabled = false;
        button.textContent = originalText;
        return;
    }
    
    fetch('/payment/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            gold_package: selectedPackage
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || 'Failed to create payment. Please try again.');
            }).catch(() => {
                throw new Error('Server error. Please try again.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Check if snap is loaded
        if (typeof snap === 'undefined') {
            throw new Error('Payment gateway not loaded. Please refresh the page.');
        }
        
        // Open Midtrans Snap
        snap.pay(data.snap_token, {
            onSuccess: function(result) {
                window.location.href = '/payment/finish?order_id=' + data.order_id;
            },
            onPending: function(result) {
                window.location.href = '/payment/pending?order_id=' + data.order_id;
            },
            onError: function(result) {
                window.location.href = '/payment/error?order_id=' + data.order_id;
            },
            onClose: function() {
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Payment failed: ' + error.message);
        button.disabled = false;
        button.textContent = originalText;
    });
});

function formatPrice(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function config(key) {
    const packages = @json(config('gold_packages'));
    return packages[key];
}
</script>
@endsection
