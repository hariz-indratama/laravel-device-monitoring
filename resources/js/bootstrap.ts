import axios from 'axios';

// ── Axios defaults ─────────────────────────────────────────────────────────────
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ── Single auth token source ───────────────────────────────────────────────────
// Auth token lives in one place: a <meta> tag injected by Laravel.
// No double-setting, no stale tokens. Everything reads from here.
function getCsrfToken(): string {
    return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';
}

// Axios interceptor: auto-attach CSRF token to mutating requests
axios.interceptors.request.use((config) => {
    const token = getCsrfToken();
    // POST/PUT/PATCH/DELETE must carry the token; GET never needs it
    if (token && ['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase() ?? '')) {
        config.headers['X-CSRF-TOKEN'] = token;
    }
    return config;
});

// Axios interceptor: catch 401 and redirect to login
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        return Promise.reject(error);
    },
);
