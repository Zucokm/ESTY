import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const wishlist = ref([]);
let isInitialized = false;

export function useWishlistStore() {
    const page = usePage();
    const isLoggedIn = computed(() => !!page.props.auth?.user);

    const initWishlist = async () => {
        if (isInitialized) return;
        
        if (isLoggedIn.value) {
            try {
                // If using ziggy for routes globally
                const response = await axios.get(route('wishlist.index'));
                wishlist.value = response.data;
            } catch (e) {
                console.error('Failed to fetch wishlist', e);
            }
        } else {
            const saved = localStorage.getItem('esty_wishlist');
            if (saved) {
                try {
                    wishlist.value = JSON.parse(saved);
                } catch (e) {}
            }
        }
        isInitialized = true;
    };

    // Watcher to save to localstorage only if not logged in
    watch(wishlist, (newWishlist) => {
        if (!isLoggedIn.value) {
            localStorage.setItem('esty_wishlist', JSON.stringify(newWishlist));
        }
    }, { deep: true });

    const addToWishlist = async (product) => {
        if (!wishlist.value.some(p => p.id === product.id)) {
            wishlist.value.push(product);
            if (isLoggedIn.value) {
                try {
                    await axios.post(route('wishlist.toggle'), { product_id: product.id });
                } catch(e) {}
            }
        }
    };

    const removeFromWishlist = async (productId) => {
        wishlist.value = wishlist.value.filter(p => p.id !== productId);
        if (isLoggedIn.value) {
            try {
                await axios.post(route('wishlist.toggle'), { product_id: productId });
            } catch(e) {}
        }
    };

    const toggleWishlist = (product) => {
        if (isInWishlist(product.id)) {
            removeFromWishlist(product.id);
        } else {
            addToWishlist(product);
        }
    };

    const isInWishlist = (productId) => {
        return wishlist.value.some(p => p.id === productId);
    };

    const wishlistCount = computed(() => wishlist.value.length);

    // Call init when hook is used
    if (typeof window !== 'undefined') {
        initWishlist();
    }

    return {
        wishlist,
        addToWishlist,
        removeFromWishlist,
        toggleWishlist,
        isInWishlist,
        wishlistCount
    };
}
