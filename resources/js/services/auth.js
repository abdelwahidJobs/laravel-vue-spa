import api from './api';

export default {
    async login(credentials) {
        // Sanctum CSRF cookie
        await api.get('/sanctum/csrf-cookie');
        return api.post('/api/login', credentials);
    },

    async user() {
        return api.get('/api/user');
    },


    logout() {
        return api.post('/api/logout');
    }
};
