@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-0">
    <!-- Back Button -->
    <a href="{{ route('quests.show', $quest->id) }}" class="inline-flex items-center text-gray-400 hover:text-white mb-6">
        ← Back to Quest
    </a>

    <!-- Quest Info Header -->
    <div class="bg-gray-900 border border-gray-800 rounded-lg p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white mb-2">{{ $quest->title }}</h1>
                <p class="text-sm text-gray-400">
                    Conversation with 
                    <span class="text-indigo-400">{{ $otherParty ? $otherParty->name : 'N/A' }}</span>
                </p>
            </div>
            <div class="text-left sm:text-right">
                <div class="text-lg sm:text-xl font-bold text-yellow-400">{{ number_format($quest->price) }}</div>
                <div class="text-xs sm:text-sm text-gray-400">Gold Coins</div>
            </div>
        </div>
    </div>

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

    <!-- Messages Thread -->
    <div id="messages-container" class="bg-gray-900 border border-gray-800 rounded-lg p-4 sm:p-6 mb-6" style="max-height: 500px; overflow-y: auto;">
        @if($messages->count() > 0)
            <div id="messages-list" class="space-y-4">
                @foreach($messages as $message)
                    <div class="message-item flex {{ $message->sender_id === Auth::id() ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
                        <div class="max-w-[80%] sm:max-w-[70%]">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs text-gray-400">
                                    {{ $message->sender->name }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $message->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="rounded-lg p-3 {{ $message->sender_id === Auth::id() ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-100' }}">
                                <p class="text-sm whitespace-pre-wrap">{{ $message->message }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div id="messages-list" class="space-y-4">
                <div class="text-center py-12" id="no-messages">
                    <p class="text-gray-400">No messages yet. Start the conversation!</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Message Form -->
    <div class="bg-gray-900 border border-gray-800 rounded-lg p-4 sm:p-6">
        <form id="message-form" method="POST" action="{{ route('messages.send', $quest->id) }}" class="space-y-4">
            @csrf

            <div id="form-errors" class="hidden">
                <div class="bg-red-600 text-white px-4 py-2 rounded">
                    <ul class="list-disc list-inside text-sm" id="errors-list"></ul>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-600 text-white px-4 py-2 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label for="message" class="block text-sm font-medium mb-2">
                    Send a message
                </label>
                <textarea 
                    id="message"
                    name="message" 
                    rows="3"
                    required
                    placeholder="Type your message here..."
                    class="w-full bg-gray-800 border border-gray-700 rounded px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-vertical"
                >{{ old('message') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Maximum 5000 characters</p>
            </div>

            <div class="flex gap-3">
                <button 
                    type="submit" 
                    id="send-button"
                    class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded font-medium transition-colors text-sm sm:text-base disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Send Message
                </button>
                <a 
                    href="{{ route('quests.show', $quest->id) }}" 
                    class="bg-gray-800 hover:bg-gray-700 px-6 py-2 rounded font-medium transition-colors text-sm sm:text-base text-center"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messages-container');
    const messagesList = document.getElementById('messages-list');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message');
    const sendButton = document.getElementById('send-button');
    const formErrors = document.getElementById('form-errors');
    const errorsList = document.getElementById('errors-list');
    const questId = {{ $quest->id }};
    const currentUserId = {{ Auth::id() }};

    // Auto-scroll to bottom on page load
    function scrollToBottom() {
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
    scrollToBottom();

    // Function to add message to the UI
    function addMessageToUI(messageData) {
        // Check if message already exists (prevent duplicates)
        const existingMessage = messagesList.querySelector(`[data-message-id="${messageData.id}"]`);
        if (existingMessage) {
            return; // Message already exists, don't add duplicate
        }

        // Remove "no messages" placeholder if exists
        const noMessages = document.getElementById('no-messages');
        if (noMessages) {
            noMessages.remove();
        }

        const isOwnMessage = messageData.sender_id === currentUserId;
        const messageHtml = `
            <div class="message-item flex ${isOwnMessage ? 'justify-end' : 'justify-start'}" data-message-id="${messageData.id}">
                <div class="max-w-[80%] sm:max-w-[70%]">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-gray-400">
                            ${messageData.sender.name}
                        </span>
                        <span class="text-xs text-gray-500">
                            ${messageData.created_at_human}
                        </span>
                    </div>
                    <div class="rounded-lg p-3 ${isOwnMessage ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-100'}">
                        <p class="text-sm whitespace-pre-wrap">${escapeHtml(messageData.message)}</p>
                    </div>
                </div>
            </div>
        `;

        messagesList.insertAdjacentHTML('beforeend', messageHtml);
        scrollToBottom();
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Listen for real-time messages via Echo
    if (window.Echo) {
        window.Echo.private(`quest.${questId}`)
            .listen('.message.sent', (e) => {
                addMessageToUI(e);
            });
    }

    // Handle form submission via AJAX
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const message = messageInput.value.trim();
        if (!message) {
            return;
        }

        // Disable form while sending
        sendButton.disabled = true;
        formErrors.classList.add('hidden');
        errorsList.innerHTML = '';

        try {
            const response = await fetch(messageForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();

            if (data.success) {
                // Clear input
                messageInput.value = '';
                // Message will be added via WebSocket broadcast
                // But we can also add it immediately for better UX
                addMessageToUI(data.message);
            } else {
                // Show errors
                if (data.errors) {
                    Object.values(data.errors).forEach(errorArray => {
                        errorArray.forEach(error => {
                            const li = document.createElement('li');
                            li.textContent = error;
                            errorsList.appendChild(li);
                        });
                    });
                } else if (data.error) {
                    const li = document.createElement('li');
                    li.textContent = data.error;
                    errorsList.appendChild(li);
                }
                formErrors.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            const li = document.createElement('li');
            li.textContent = 'Failed to send message. Please try again.';
            errorsList.appendChild(li);
            formErrors.classList.remove('hidden');
        } finally {
            sendButton.disabled = false;
        }
    });
});
</script>
@endsection

