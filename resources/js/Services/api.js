import axios from 'axios';
import { router } from '@inertiajs/vue3';

export const categoryApi = {
    store: (data) => axios.post(route('admin.categories.store'), data),
    destroy: (id, options = {}) => router.delete(route('categories.destroy', id), options)
};

export const homepageApi = {
    destroySlide: (id, options = {}) => router.delete(route('admin.homepage.destroySlide', id), options),
    destroyLookbook: (id, options = {}) => router.delete(route('admin.homepage.destroyLookbook', id), options)
};

export const orderApi = {
    updateStatus: (orderId, data, options = {}) => router.put(route('admin.orders.updateStatus', orderId), data, options),
    cancel: (orderId, options = {}) => router.post(route('orders.cancel', orderId), {}, options)
};

export const productApi = {
    toggleStatus: (productId, options = {}) => router.patch(route('admin.products.toggleStatus', productId), {}, options)
};
