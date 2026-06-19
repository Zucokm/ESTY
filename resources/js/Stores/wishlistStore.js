import { ref, computed, watch } from 'vue';

const wishlist = ref([]);

// Load from localStorage on initialization
if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('esty_wishlist');
    if (saved) {
        try {
            wishlist.value = JSON.parse(saved);
        } catch (e) {
            console.error('Error parsing wishlist from localStorage', e);
        }
    }
}

// Sync with localStorage
watch(wishlist, (newWishlist) => {
    localStorage.setItem('esty_wishlist', JSON.stringify(newWishlist));
}, { deep: true });

export function useWishlistStore() {
    const addToWishlist = (product) => {
        if (!wishlist.value.some(p => p.id === product.id)) {
            wishlist.value.push({
                id: product.id,
                name: product.name,
                slug: product.slug,
                base_price: product.base_price,
                images: product.images,
                category: product.category,
                variants: product.variants
            });
        }
    };

    const removeFromWishlist = (productId) => {
        wishlist.value = wishlist.value.filter(p => p.id !== productId);
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

    return {
        wishlist,
        addToWishlist,
        removeFromWishlist,
        toggleWishlist,
        isInWishlist,
        wishlistCount
    };
}
