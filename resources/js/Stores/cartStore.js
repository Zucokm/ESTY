import { ref, computed, watch } from 'vue';

const cart = ref([]);

// Load cart from localStorage on initialization
if (typeof window !== 'undefined') {
    const savedCart = localStorage.getItem('verone_cart');
    if (savedCart) {
        try {
            const parsed = JSON.parse(savedCart);
            cart.value = Array.isArray(parsed)
                ? parsed.filter(item => item && item.name && item.variant_id)
                : [];
        } catch (e) {
            console.error('Failed to parse cart storage', e);
        }
    }
}

// Watch cart to save to localStorage
watch(cart, (newCart) => {
    localStorage.setItem('verone_cart', JSON.stringify(newCart));
}, { deep: true });

export function useCartStore() {
    
    const addToCart = (product, variant, quantity = 1, customImage = null) => {
        const existingItem = cart.value.find(item => item.variant_id === variant.id);
        
        if (existingItem) {
            existingItem.quantity += quantity;
            if (customImage) {
                existingItem.image = customImage;
            }
        } else {
            const price = parseFloat(product.base_price) + parseFloat(variant.additional_price || 0);
            const image = customImage || (product.images && product.images.length > 0 ? product.images[0].image_path : null);
            
            cart.value.push({
                product_id: product.id,
                variant_id: variant.id,
                name: product.name,
                size: variant.size,
                color: variant.color,
                price: price,
                image: image,
                quantity: quantity,
                max_stock: variant.stock_quantity
            });
        }
    };

    const removeFromCart = (variantId) => {
        cart.value = cart.value.filter(item => item.variant_id !== variantId);
    };

    const updateQuantity = (variantId, quantity) => {
        const item = cart.value.find(item => item.variant_id === variantId);
        if (item) {
            item.quantity = Math.max(1, Math.min(quantity, item.max_stock));
        }
    };

    const clearCart = () => {
        cart.value = [];
    };

    const cartCount = computed(() => {
        return cart.value.reduce((total, item) => total + item.quantity, 0);
    });

    const cartTotal = computed(() => {
        return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0);
    });

    return {
        cart,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        cartCount,
        cartTotal
    };
}
