window.axios = require('axios');

// Keep this true for Sanctum/Cookies
window.axios.defaults.withCredentials = true;

// Force JSON for everything
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';

// Comment this out temporarily to match your Postman success
// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';