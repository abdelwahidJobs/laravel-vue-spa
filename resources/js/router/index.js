import Vue from 'vue';
import VueRouter from 'vue-router';

import Login from '../views/Login.vue';
import Products from '../views/Products.vue';
Vue.use(VueRouter);

const routes = [
    {
        path: '/login',
        name: 'login', // Added name for consistency
        component: Login,
    },
    {
        path: '/products',
        name: 'products.index', // Added name for consistency
        component: Products,
    },
    {
        path: '/products/:slug/edit',
        name: 'product.edit',
        component: () => import('../views/ProductEdit.vue'),
        props: true
    },
    {
        path: '/products/create',
        name: 'product.create',
        component: () => import('../views/ProductCreate.vue'),
        props: true
    },
    // ALWAYS move the wildcard to the very last position
    {
        path: '*',
        redirect: '/login'
    },
];

export default new VueRouter({
    mode: 'history',
    routes,
});
