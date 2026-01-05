import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Get Reverb config from Vite env (build time) or meta tags (runtime)
const getMetaContent = (name) => {
    const meta = document.querySelector(`meta[name="${name}"]`);
    return meta ? meta.getAttribute('content') : null;
};

const reverbAppKey = import.meta.env.VITE_REVERB_APP_KEY || getMetaContent('reverb-app-key');
const reverbHost = import.meta.env.VITE_REVERB_HOST || getMetaContent('reverb-host') || window.location.hostname;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || getMetaContent('reverb-scheme') || 'https';

if (reverbAppKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbAppKey,
        wsHost: reverbHost,
        wsPort: reverbScheme === 'https' ? 443 : 80,
        wssPort: 443,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        },
        // Disable automatic path generation - use root path since Nginx proxies /app/ to Reverb
        disableStats: true,
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
