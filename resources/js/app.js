// require('./bootstrap');
import Vue from 'vue';
import App from './views/App.vue';
import router from './router';
import Login from "./views/Login.vue";

Vue.config.productionTip = false;
Vue.component('Login', Login);

new Vue({
    router,
    render: h => h(App),
}).$mount('#app');
