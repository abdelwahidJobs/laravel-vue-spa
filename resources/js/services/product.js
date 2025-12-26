import api from './api';

export default {
    getProducts(page = 1) {
        return api.get(`api/products?page=${page}`).then(res => res.data);
    },
    create(form){
        return api.post(`api/products`, form).then(res => res.data);
    },
    findBySlug(slug){
        return api.get(`api/products/${slug}`).then(res => res.data);
    },
    update(slug, form){
        return api.put(`api/products/${slug}`, form)
            .then(res  => res.data);
    },
    delete(slug){
        return api.delete(`api/products/${slug}`)
            .then(res  => res.data);
    }
};
