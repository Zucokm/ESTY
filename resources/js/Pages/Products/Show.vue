<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useCart } from '@/Composables/useCart';

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
});

const { cart, addToCart, removeFromCart, updateQuantity, clearCart, cartCount, cartTotal } = useCart();

// Image Gallery
const activeImageIndex = ref(0);
const activeImageUrl = computed(() => {
    if (props.product.images && props.product.images.length > 0) {
        return props.product.images[activeImageIndex.value].image_path;
    }
    return null;
});

// Selection States
const getUniqueSizes = (variants) => {
    if (!variants) return [];
    return [...new Set(variants.map(v => v.size))];
};

const getUniqueColors = (variants) => {
    if (!variants) return [];
    return [...new Set(variants.map(v => v.color))];
};

const sizes = getUniqueSizes(props.product.variants);
const colors = getUniqueColors(props.product.variants);

const selectedSize = ref(sizes[0] || '');
const selectedColor = ref(colors[0] || '');
const quantity = ref(1);

// Cart Drawer States
const showCartDrawer = ref(false);
const checkoutStep = ref('cart'); // 'cart' or 'checkout'
const shippingAddress = ref('');
const phone = ref('');
const showSuccessAlert = ref(false);

const checkoutForm = useForm({
    shipping_address: '',
    phone: '',
    items: []
});

// Helper color swatches map for clothing tags
const colorMap = {
    oatmeal: 'bg-[#e5dcd3]',
    charcoal: 'bg-[#2f3542]',
    navy: 'bg-[#1e272e]',
    black: 'bg-[#111111]',
    sage: 'bg-[#a3b19b]',
    beige: 'bg-[#d2b48c]',
    khaki: 'bg-[#c3b091]',
    gray: 'bg-[#718093]',
    white: 'bg-[#f5f6fa]',
    olive: 'bg-[#57606f]',
    rose: 'bg-[#fda7df]',
    red: 'bg-[#ff7675]',
    blue: 'bg-[#74b9ff]',
    green: 'bg-[#55efc4]',
};

const getColorClass = (colorName) => {
    if (!colorName) return 'bg-slate-500';
    const norm = colorName.toLowerCase().trim();
    for (const key in colorMap) {
        if (norm.includes(key)) {
            return colorMap[key];
        }
    }
    return 'bg-slate-500';
};

// Computes the active variant based on size & color choices
const selectedVariant = computed(() => {
    if (!selectedSize.value || !selectedColor.value) return null;
    return props.product.variants.find(
        v => v.size === selectedSize.value && v.color === selectedColor.value
    );
});

// Calculate price dynamically
const finalPrice = computed(() => {
    if (!selectedVariant.value) return parseFloat(props.product.base_price);
    return parseFloat(props.product.base_price) + parseFloat(selectedVariant.value.additional_price || 0);
});

// Add to Cart
const handleAddToCart = () => {
    if (!selectedVariant.value) return;
    
    const primaryImg = props.product.images.find(img => img.is_primary) || props.product.images[0];
    const imagePath = primaryImg ? primaryImg.image_path : null;

    addToCart(
        props.product.id,
        selectedVariant.value.id,
        selectedSize.value,
        selectedColor.value,
        quantity.value,
        finalPrice.value,
        imagePath,
        props.product.name
    );

    // Reset details and open cart drawer
    showCartDrawer.value = true;
    checkoutStep.value = 'cart';
};

// Checkout Handlers
const handleCheckoutProceed = () => {
    checkoutStep.value = 'checkout';
};

const submitCheckout = () => {
    checkoutForm.shipping_address = shippingAddress.value;
    checkoutForm.phone = phone.value;
    checkoutForm.items = cart.value.map(item => ({
        product_id: item.product_id,
        variant_id: item.variant_id,
        quantity: item.quantity,
        price: item.price
    }));

    checkoutForm.post(route('checkout.store'), {
        onSuccess: () => {
            clearCart();
            showCartDrawer.value = false;
            showSuccessAlert.value = true;
            shippingAddress.value = '';
            phone.value = '';
            setTimeout(() => {
                showSuccessAlert.value = false;
            }, 6000);
        }
    });
};
</script>

<template>
    <Head :title="`${product.name} - ESTY`" />

    <div class="min-h-screen relative overflow-hidden pb-20 selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Top Success Toast Alert -->
        <div 
            v-if="showSuccessAlert"
            class="fixed top-8 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md p-0.5 rounded-3xl bg-gradient-to-r from-emerald-500/50 to-teal-500/50 shadow-2xl border border-white/20 backdrop-blur-2xl transition-all"
        >
            <div class="bg-slate-950/80 rounded-[22px] p-5 flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-white tracking-tight text-base mb-1">Order Placed Successfully</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Thank you for shopping at ESTY. Our administrators are processing your garments delivery.</p>
                </div>
                <button @click="showSuccessAlert = false" class="text-slate-500 hover:text-white transition-colors">✕</button>
            </div>
        </div>

        <!-- Floating Header/Navigation (Dynamic Island Style) -->
        <div class="fixed top-6 left-0 right-0 z-40 flex justify-center px-4">
            <nav class="glass-card px-6 py-3.5 w-full max-w-4xl flex items-center justify-between shadow-[0_12px_40px_0_rgba(0,0,0,0.3)] rounded-full border-white/10">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2">
                    <span class="text-white font-bold tracking-tight text-lg">ESTY</span>
                </Link>

                <!-- Main Nav Links -->
                <div class="hidden md:flex items-center gap-7">
                    <Link href="/" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Home</Link>
                    <Link href="/#shop" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Shop</Link>
                    <Link v-if="$page.props.auth.user" :href="route('orders.index')" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">My Orders</Link>
                </div>

                <!-- Auth Navigation -->
                <div class="flex items-center gap-3">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="glass-button text-xs py-2 px-4 rounded-full border-white/10"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-xs font-semibold text-slate-300 hover:text-white">Sign In</Link>
                        <Link :href="route('register')" class="glass-button text-xs py-2 px-4 rounded-full border-white/10">Register</Link>
                    </template>
                </div>
            </nav>
        </div>

        <!-- Main Product Detail View -->
        <main class="pt-32 px-4 max-w-5xl mx-auto relative z-10">
            <div class="glass-card p-6 md:p-10 rounded-[3rem] border-white/10 grid grid-cols-1 md:grid-cols-2 gap-10">
                
                <!-- Left: Image Gallery & Previews -->
                <div class="flex flex-col gap-4">
                    <div class="aspect-[4/5] rounded-[2rem] overflow-hidden bg-slate-950/50 border border-white/5 relative">
                        <img 
                            v-if="activeImageUrl"
                            :src="activeImageUrl" 
                            :alt="product.name" 
                            class="w-full h-full object-cover object-top"
                        />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500 gap-2">
                            <svg class="w-12 h-12 stroke-current" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs font-bold tracking-widest uppercase">No Image available</span>
                        </div>
                        <span class="absolute top-4 left-4 text-[10px] font-bold tracking-widest text-white uppercase bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                            {{ product.category?.name || 'Garment' }}
                        </span>
                    </div>

                    <!-- Thumbnails row -->
                    <div v-if="product.images && product.images.length > 1" class="flex gap-3 overflow-x-auto py-1">
                        <button 
                            v-for="(img, idx) in product.images" 
                            :key="img.id"
                            @click="activeImageIndex = idx"
                            class="w-20 h-24 rounded-xl overflow-hidden bg-slate-900 border transition-all duration-200 shrink-0"
                            :class="activeImageIndex === idx ? 'border-indigo-500 ring-2 ring-indigo-500/20 scale-95' : 'border-white/10 hover:border-white/30'"
                        >
                            <img :src="img.image_path" class="w-full h-full object-cover object-top" />
                        </button>
                    </div>
                </div>

                <!-- Right: Product Information & Checkout Options -->
                <div class="flex flex-col justify-between py-2">
                    <div class="space-y-6">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight leading-tight">{{ product.name }}</h1>
                            <div class="flex items-center gap-3 mt-3">
                                <span class="text-2xl font-black text-white">${{ finalPrice.toFixed(2) }}</span>
                                <span v-if="selectedVariant && selectedVariant.additional_price > 0" class="text-xs text-indigo-300 font-semibold bg-indigo-500/10 px-2.5 py-1 rounded-full border border-indigo-500/20">
                                    +${{ parseFloat(selectedVariant.additional_price).toFixed(2) }} variant
                                </span>
                            </div>
                        </div>

                        <p class="text-slate-300 text-sm leading-relaxed font-medium">
                            {{ product.description || 'Elevate your aesthetic style with this custom-engineered garment from ESTY. Designed with modern fabric blends and variant-level specifications.' }}
                        </p>

                        <!-- Interactive Variant Swatches -->
                        <div class="space-y-5">
                            <!-- Color Chooser -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2.5">Select Color: <span class="text-white">{{ selectedColor }}</span></label>
                                <div class="flex flex-wrap gap-2.5">
                                    <button 
                                        v-for="color in colors" 
                                        :key="color"
                                        @click="selectedColor = color"
                                        class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all relative group"
                                        :class="selectedColor === color ? 'border-indigo-500 scale-110 shadow-lg shadow-indigo-500/20' : 'border-white/10 hover:border-white/30'"
                                        :title="color"
                                    >
                                        <span :class="getColorClass(color)" class="w-7 h-7 rounded-full inline-block"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Size Chooser -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2.5">Select Size: <span class="text-white">{{ selectedSize }}</span></label>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        v-for="size in sizes" 
                                        :key="size"
                                        @click="selectedSize = size"
                                        class="glass-button text-xs py-2.5 px-5 rounded-xl font-bold tracking-wider transition-all duration-200"
                                        :class="selectedSize === size ? 'bg-white/[0.12] text-white border-white/20 scale-105' : 'text-slate-400 border-transparent hover:text-slate-200'"
                                    >
                                        {{ size }}
                                    </button>
                                </div>
                            </div>

                            <!-- Inventory stock status -->
                            <div v-if="selectedVariant" class="text-xs font-semibold">
                                <span v-if="selectedVariant.stock_quantity > 0" class="text-emerald-400">
                                    ✓ In Stock ({{ selectedVariant.stock_quantity }} items left)
                                </span>
                                <span v-else class="text-rose-400">
                                    ✕ Out of Stock
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Actions -->
                    <div class="mt-8 pt-6 border-t border-white/[0.06] flex items-center gap-4">
                        <!-- Quantity Controller -->
                        <div class="flex items-center gap-3 bg-white/[0.04] border border-white/[0.08] rounded-2xl px-4 py-3 shrink-0">
                            <button 
                                @click="quantity = Math.max(1, quantity - 1)" 
                                class="text-slate-400 hover:text-white font-black text-sm w-5 text-center"
                                :disabled="!selectedVariant || selectedVariant.stock_quantity <= 0"
                            >-</button>
                            <span class="text-sm text-white font-extrabold w-6 text-center">{{ quantity }}</span>
                            <button 
                                @click="quantity = selectedVariant ? Math.min(selectedVariant.stock_quantity, quantity + 1) : quantity + 1" 
                                class="text-slate-400 hover:text-white font-black text-sm w-5 text-center"
                                :disabled="!selectedVariant || quantity >= selectedVariant.stock_quantity"
                            >+</button>
                        </div>

                        <!-- Main Add to Cart Button -->
                        <button 
                            @click="handleAddToCart"
                            :disabled="!selectedVariant || selectedVariant.stock_quantity <= 0"
                            class="glass-button-primary flex-1 py-4 font-extrabold text-sm rounded-2xl flex justify-center items-center shadow-lg shadow-indigo-500/25 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            {{ selectedVariant ? (selectedVariant.stock_quantity > 0 ? 'Add to Shopping Bag' : 'Out of Stock') : 'Select Options First' }}
                        </button>
                    </div>

                </div>

            </div>
        </main>

        <!-- Floating Shopping Cart Button (Bottom Right) -->
        <button 
            @click="showCartDrawer = true; checkoutStep = 'cart';"
            class="fixed bottom-8 right-8 z-30 w-16 h-16 rounded-full glass-card hover:bg-white/[0.08] flex items-center justify-center border-white/20 shadow-[0_12px_40px_0_rgba(0,0,0,0.5)] hover:scale-110 active:scale-95 transition-all duration-300 pointer-events-auto"
        >
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 text-white font-bold text-xs flex items-center justify-center border border-white/20 shadow-lg shadow-indigo-500/40 animate-pulse">
                {{ cartCount }}
            </span>
        </button>

        <!-- Slide-out Cart Drawer -->
        <div 
            class="fixed inset-0 z-50 overflow-hidden pointer-events-none"
            :class="{ 'pointer-events-auto': showCartDrawer }"
        >
            <!-- Backdrop -->
            <Transition name="fade">
                <div 
                    v-if="showCartDrawer" 
                    @click="showCartDrawer = false" 
                    class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity duration-300 pointer-events-auto"
                ></div>
            </Transition>

            <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
                <!-- Drawer Content -->
                <Transition name="slide">
                    <div 
                        v-if="showCartDrawer" 
                        class="w-screen max-w-md bg-slate-950/40 border-l border-white/10 backdrop-blur-3xl shadow-[0_0_50px_0_rgba(0,0,0,0.6)] flex flex-col justify-between pointer-events-auto h-full"
                    >
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-white/[0.08] flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <h2 class="text-lg font-bold text-white tracking-tight">
                                    {{ checkoutStep === 'cart' ? 'Shopping Bag' : 'Shipping Details' }} ({{ cartCount }})
                                </h2>
                            </div>
                            <button @click="showCartDrawer = false" class="text-slate-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Drawer Step 1: Cart Items List -->
                        <div v-if="checkoutStep === 'cart'" class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div 
                                v-for="item in cart" 
                                :key="item.variant_id"
                                class="p-3.5 rounded-2xl bg-white/[0.02] border border-white/[0.04] flex gap-4 relative group hover:border-white/[0.08] transition-all"
                            >
                                <!-- Mini Thumbnail -->
                                <div class="w-16 h-20 rounded-xl overflow-hidden bg-slate-900 shrink-0">
                                    <img v-if="item.image" :src="item.image" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-indigo-500/10 text-indigo-300 text-xs font-bold uppercase">
                                        {{ item.name.charAt(0) }}
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="flex-1 flex flex-col justify-between">
                                    <div>
                                        <h3 class="font-bold text-white text-sm tracking-tight line-clamp-1">{{ item.name }}</h3>
                                        <span class="inline-block mt-1 text-[10px] font-bold text-slate-400 bg-white/[0.05] border border-white/[0.08] px-2 py-0.5 rounded-md">
                                            {{ item.size }} / {{ item.color }}
                                        </span>
                                    </div>

                                    <!-- Quantity / Price Controllers -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 bg-white/[0.04] border border-white/[0.06] rounded-lg px-2 py-1">
                                            <button @click="updateQuantity(item.variant_id, item.quantity - 1)" class="text-slate-400 hover:text-white font-bold text-xs">-</button>
                                            <span class="text-xs text-white font-bold px-1.5">{{ item.quantity }}</span>
                                            <button @click="updateQuantity(item.variant_id, item.quantity + 1)" class="text-slate-400 hover:text-white font-bold text-xs">+</button>
                                        </div>
                                        <span class="font-extrabold text-white text-sm">${{ (item.price * item.quantity).toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Delete button -->
                                <button 
                                    @click="removeFromCart(item.variant_id)" 
                                    class="absolute top-2 right-2 text-slate-500 hover:text-rose-400 opacity-0 group-hover:opacity-100 transition-all"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Empty state -->
                            <div v-if="cart.length === 0" class="h-64 flex flex-col items-center justify-center text-slate-500 text-center gap-3">
                                <svg class="w-10 h-10 stroke-current text-slate-600" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span class="text-sm font-semibold tracking-wide">Your Shopping bag is empty.</span>
                            </div>
                        </div>

                        <!-- Drawer Step 2: Shipping Form -->
                        <div v-else class="flex-1 overflow-y-auto p-6 space-y-6">
                            <div class="space-y-4">
                                <!-- Shipping Address -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Shipping Address</label>
                                    <textarea 
                                        v-model="shippingAddress" 
                                        rows="4" 
                                        class="glass-input" 
                                        placeholder="Enter your complete home address for delivery..."
                                        required
                                    ></textarea>
                                    <span v-if="checkoutForm.errors.shipping_address" class="text-xs text-rose-400 mt-1 block ml-1">{{ checkoutForm.errors.shipping_address }}</span>
                                </div>

                                <!-- Phone Number -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Phone Number</label>
                                    <input 
                                        type="text" 
                                        v-model="phone" 
                                        class="glass-input" 
                                        placeholder="+95 9..."
                                        required
                                    />
                                    <span v-if="checkoutForm.errors.phone" class="text-xs text-rose-400 mt-1 block ml-1">{{ checkoutForm.errors.phone }}</span>
                                </div>

                                <!-- Stock Error Flash -->
                                <div v-if="checkoutForm.errors.items" class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs font-semibold text-rose-400 leading-relaxed">
                                    {{ checkoutForm.errors.items }}
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Action & Total -->
                        <div class="p-6 border-t border-white/[0.08] space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-400">Total Price</span>
                                <span class="text-2xl font-black text-white">${{ cartTotal.toFixed(2) }}</span>
                            </div>

                            <!-- Button logic for Step 1 -->
                            <div v-if="checkoutStep === 'cart'" class="space-y-2">
                                <template v-if="$page.props.auth.user">
                                    <button 
                                        @click="handleCheckoutProceed"
                                        :disabled="cart.length === 0"
                                        class="glass-button-primary w-full py-4 font-bold text-base rounded-2xl flex justify-center items-center shadow-lg shadow-indigo-500/25 disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
                                    >
                                        Proceed to Checkout
                                    </button>
                                </template>
                                <template v-else>
                                    <Link 
                                        :href="route('login')"
                                        class="glass-button-primary w-full py-4 font-bold text-base rounded-2xl flex justify-center items-center shadow-lg shadow-indigo-500/25 text-center"
                                    >
                                        Sign In to Checkout
                                    </Link>
                                </template>
                            </div>

                            <!-- Button logic for Step 2 -->
                            <div v-else class="flex gap-3">
                                <button 
                                    @click="checkoutStep = 'cart'" 
                                    class="glass-button py-4 px-6 rounded-2xl text-slate-300 font-semibold"
                                >
                                    Back
                                </button>
                                <button 
                                    @click="submitCheckout"
                                    :disabled="checkoutForm.processing || !shippingAddress || !phone"
                                    class="glass-button-primary flex-1 py-4 font-bold text-base rounded-2xl flex justify-center items-center shadow-lg shadow-indigo-500/25 disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
                                >
                                    <span v-if="checkoutForm.processing" class="inline-block animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                                    Place Order
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Fade Backdrop Transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Slide Panel Transition */
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}
</style>
