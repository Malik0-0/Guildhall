import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Only initialize Echo if Reverb app key is available
const reverbAppKey = import.meta.env.VITE_REVERB_APP_KEY;
if (reverbAppKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbAppKey,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        },
    });
} else {
    // Create a dummy Echo instance to prevent errors
    console.warn('Reverb app key not found. WebSocket features will be disabled.');
    window.Echo = {
        channel: () => ({ listen: () => {}, subscribed: () => {} }),
        private: () => ({ listen: () => {}, subscribed: () => {} }),
        join: () => ({ here: () => {}, joining: () => {}, leaving: () => {} }),
    };
}
