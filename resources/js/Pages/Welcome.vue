<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useCart } from '@/Composables/useCart';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    }
});

// Search and Filter States
const searchQuery = ref('');
const selectedCategoryId = ref(null);
const sortBy = ref('latest');

// Computed Filtered & Sorted Products
const filteredProducts = computed(() => {
    let result = [...props.products];

    // Filter by Category
    if (selectedCategoryId.value !== null) {
        result = result.filter(product => product.category_id === selectedCategoryId.value);
    }

    // Filter by Search Query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(product => 
            product.name.toLowerCase().includes(query) || 
            (product.description && product.description.toLowerCase().includes(query))
        );
    }

    // Sort
    if (sortBy.value === 'latest') {
        result.sort((a, b) => b.id - a.id);
    } else if (sortBy.value === 'price_asc') {
        result.sort((a, b) => parseFloat(a.base_price) - parseFloat(b.base_price));
    } else if (sortBy.value === 'price_desc') {
        result.sort((a, b) => parseFloat(b.base_price) - parseFloat(a.base_price));
    }

    return result;
});

const { cart, addToCart, removeFromCart, updateQuantity, clearCart, cartCount, cartTotal } = useCart();

// Selection Modal States
const showSelectionModal = ref(false);
const selectedProduct = ref(null);
const selectedSize = ref('');
const selectedColor = ref('');

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

const getUniqueSizes = (variants) => {
    if (!variants) return [];
    return [...new Set(variants.map(v => v.size))];
};

const getUniqueColors = (variants) => {
    if (!variants) return [];
    return [...new Set(variants.map(v => v.color))];
};

// Modal Interaction
const openProductModal = (product) => {
    selectedProduct.value = product;
    const sizes = getUniqueSizes(product.variants);
    const colors = getUniqueColors(product.variants);
    selectedSize.value = sizes[0] || '';
    selectedColor.value = colors[0] || '';
    showSelectionModal.value = true;
};

// Computes the active variant based on size & color choices
const selectedVariant = computed(() => {
    if (!selectedProduct.value || !selectedSize.value || !selectedColor.value) return null;
    return selectedProduct.value.variants.find(
        v => v.size === selectedSize.value && v.color === selectedColor.value
    );
});

// Calculate variant-specific price (base + additional)
const variantPrice = computed(() => {
    if (!selectedProduct.value) return 0;
    const base = parseFloat(selectedProduct.value.base_price);
    const add = selectedVariant.value ? parseFloat(selectedVariant.value.additional_price) : 0;
    return base + add;
});

const handleAddToCart = () => {
    if (selectedProduct.value && selectedVariant.value) {
        addToCart(selectedProduct.value, selectedVariant.value, 1);
        showSelectionModal.value = false;
        showCartDrawer.value = true;
    }
};

const handleCheckoutProceed = () => {
    checkoutStep.value = 'checkout';
};

const submitCheckout = () => {
    checkoutForm.items = cart.value.map(item => ({
        product_id: item.product_id,
        variant_id: item.variant_id,
        quantity: item.quantity,
        price: item.price
    }));
    checkoutForm.shipping_address = shippingAddress.value;
    checkoutForm.phone = phone.value;

    checkoutForm.post(route('checkout.store'), {
        onSuccess: () => {
            clearCart();
            shippingAddress.value = '';
            phone.value = '';
            checkoutStep.value = 'cart';
            showCartDrawer.value = false;
            showSuccessAlert.value = true;
            // Dismiss alert automatically
            setTimeout(() => {
                showSuccessAlert.value = false;
            }, 6000);
        }
    });
};
</script>

<template>
    <Head title="ESTY Garments - Premium Clothing" />

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

        <!-- Floating Header/Navigation (Dynamic Island / macOS Dock Style) -->
        <div class="fixed top-6 left-0 right-0 z-40 flex justify-center px-4">
            <nav class="glass-card px-6 py-3.5 w-full max-w-4xl flex items-center justify-between shadow-[0_12px_40px_0_rgba(0,0,0,0.3)] rounded-full border-white/10">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <span class="text-white font-bold tracking-tight text-lg">ESTY</span>
                </div>

                <!-- Main Nav Links -->
                <div class="hidden md:flex items-center gap-7">
                    <a href="#" class="text-sm font-semibold text-slate-200 hover:text-white transition-colors duration-200">Home</a>
                    <a href="#shop" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Shop</a>
                    <Link v-if="$page.props.auth.user" :href="route('orders.index')" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">My Orders</Link>
                    <a href="#" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Categories</a>
                    <a href="#" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">About</a>
                </div>

                <!-- Auth Navigation -->
                <div class="flex items-center gap-3">
                    <template v-if="canLogin">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="route('orders.index')"
                                class="text-xs font-semibold text-slate-300 hover:text-white px-3 py-2 transition-colors duration-200"
                            >
                                My Orders
                            </Link>
                            <Link
                                :href="route('dashboard')"
                                class="glass-button text-xs py-2 px-4 rounded-full border-white/10"
                            >
                                Dashboard
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-xs font-semibold text-rose-400 hover:text-rose-300 px-3 py-2 transition-colors duration-200"
                            >
                                Log Out
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-xs font-semibold text-slate-300 hover:text-white px-3 py-2 transition-colors duration-200"
                            >
                                Sign In
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="glass-button-primary text-xs py-2.5 px-4.5 rounded-full border-white/15"
                            >
                                Register
                            </Link>
                        </template>
                    </template>
                </div>
            </nav>
        </div>

        <!-- Hero Section -->
        <section class="pt-36 pb-16 px-4 max-w-7xl mx-auto flex flex-col items-center text-center relative z-10">
            <!-- Premium Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/[0.04] border border-white/[0.08] backdrop-blur-md mb-8">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-semibold tracking-wider text-slate-300 uppercase">Autumn / Winter '26 Collection</span>
            </div>
            
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-white mb-6 max-w-4xl leading-tight">
                Designed for Comfort. <br/>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-200 via-purple-300 to-pink-200">Crafted for Excellence.</span>
            </h1>
            
            <p class="text-lg md:text-xl text-slate-400 max-w-2xl mb-10 font-medium leading-relaxed">
                Discover clean silhouettes, premium Italian fabrics, and timeless styles designed to elevate your everyday wear.
            </p>

            <div class="flex gap-4">
                <a href="#shop" class="glass-button-primary px-8 py-3.5 text-base font-semibold rounded-full hover:scale-105 transition-transform">
                    Explore Shop
                </a>
                <a href="#" class="glass-button px-8 py-3.5 text-base font-semibold rounded-full hover:bg-white/[0.12] transition-colors">
                    Learn More
                </a>
            </div>
        </section>

        <!-- Product Grid Section -->
        <section id="shop" class="py-16 px-4 max-w-7xl mx-auto relative z-10">
            <!-- Search & Filters -->
            <div class="flex flex-col gap-6 mb-12">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-3xl font-extrabold text-white tracking-tight mb-2">Featured Garments</h2>
                        <p class="text-slate-400 font-medium">Modern essentials engineered with variant-level precision.</p>
                    </div>
                    
                    <!-- Search & Sort Controls -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Search Box -->
                        <div class="relative min-w-[240px]">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input 
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search products..."
                                class="w-full pl-10 pr-4 py-2 text-sm text-white bg-white/[0.04] border border-white/[0.08] hover:border-white/[0.15] focus:border-indigo-500/50 focus:bg-white/[0.08] rounded-full focus:outline-none transition-all duration-300 backdrop-blur-md"
                            />
                        </div>

                        <!-- Sort Dropdown -->
                        <div class="relative">
                            <select 
                                v-model="sortBy"
                                class="appearance-none pl-4 pr-10 py-2 text-sm text-slate-300 bg-white/[0.04] border border-white/[0.08] hover:border-white/[0.15] focus:border-indigo-500/50 rounded-full focus:outline-none transition-all duration-300 backdrop-blur-md cursor-pointer"
                            >
                                <option value="latest" class="bg-slate-900 text-white">Latest</option>
                                <option value="price_asc" class="bg-slate-900 text-white">Price: Low to High</option>
                                <option value="price_desc" class="bg-slate-900 text-white">Price: High to Low</option>
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Categories List -->
                <div class="flex flex-wrap gap-2 pb-2 border-b border-white/[0.06]">
                    <button 
                        @click="selectedCategoryId = null"
                        class="glass-button text-xs py-2 px-4 rounded-full transition-all duration-200"
                        :class="selectedCategoryId === null ? 'bg-white/[0.12] text-white border-white/20' : 'text-slate-400 border-transparent'"
                    >
                        All Products
                    </button>
                    <button 
                        v-for="category in categories"
                        :key="category.id"
                        @click="selectedCategoryId = category.id"
                        class="glass-button text-xs py-2 px-4 rounded-full transition-all duration-200"
                        :class="selectedCategoryId === category.id ? 'bg-white/[0.12] text-white border-white/20' : 'text-slate-400 border-transparent'"
                    >
                        {{ category.name }}
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div 
                v-if="filteredProducts.length === 0" 
                class="glass-card py-20 px-4 text-center rounded-[2.5rem] border-white/[0.06] flex flex-col items-center justify-center"
            >
                <div class="w-16 h-16 rounded-full bg-white/[0.03] border border-white/[0.08] flex items-center justify-center text-slate-400 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-1">No products found</h3>
                <p class="text-slate-400 text-sm max-w-sm">We couldn't find any garments matching your search criteria or category filter.</p>
            </div>

            <!-- Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div 
                    v-for="product in filteredProducts" 
                    :key="product.id" 
                    @click="openProductModal(product)"
                    class="glass-card glass-card-hover group flex flex-col h-full rounded-[2.5rem] overflow-hidden p-3 cursor-pointer"
                >
                    <!-- Product Image Container -->
                    <div class="relative aspect-[4/5] rounded-[2rem] overflow-hidden mb-4 bg-slate-900/40">
                        <img 
                            v-if="product.images && product.images.length > 0"
                            :src="product.images[0].image_path" 
                            :alt="product.name" 
                            class="w-full h-full object-cover object-top transition-transform duration-700 ease-in-out group-hover:scale-110"
                        />
                        <div 
                            v-else 
                            class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-tr from-slate-900/80 to-slate-800/80 text-slate-500 gap-2"
                        >
                            <svg class="w-10 h-10 stroke-current" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs uppercase font-bold tracking-widest text-slate-600">No Image</span>
                        </div>
                        
                        <!-- Category Badge -->
                        <span class="absolute top-4 left-4 text-[10px] font-bold tracking-widest text-white uppercase bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                            {{ product.category?.name || 'Garment' }}
                        </span>
                        
                        <!-- Floating Add to Cart Quick Action -->
                        <button class="absolute bottom-4 right-4 w-11 h-11 rounded-full bg-white text-slate-950 flex items-center justify-center shadow-lg transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 hover:bg-indigo-600 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>

                    <!-- Details -->
                    <div class="px-3 pb-3 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="font-bold text-white tracking-tight text-lg group-hover:text-indigo-200 transition-colors duration-200 line-clamp-1">
                                {{ product.name }}
                            </h3>
                            <span class="font-extrabold text-white text-lg">${{ parseFloat(product.base_price).toFixed(2) }}</span>
                        </div>

                        <!-- Variant Specs: Colors & Sizes -->
                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-white/[0.06]">
                            <!-- Color Swatches -->
                            <div class="flex items-center gap-1.5">
                                <div 
                                    v-for="(color, cIdx) in getUniqueColors(product.variants)" 
                                    :key="cIdx"
                                    :class="getColorClass(color)" 
                                    class="w-3.5 h-3.5 rounded-full ring-1 ring-white/20 cursor-pointer hover:scale-125 transition-transform"
                                    :title="color"
                                ></div>
                            </div>

                            <!-- Sizes List -->
                            <div class="flex gap-1">
                                <span 
                                    v-for="(size, sIdx) in getUniqueSizes(product.variants)" 
                                    :key="sIdx"
                                    class="text-[10px] font-bold text-slate-400 bg-white/[0.04] px-1.5 py-0.5 rounded border border-white/[0.05]"
                                >
                                    {{ size }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Floating Shopping Cart Button (Bottom Right) -->
        <button 
            @click="showCartDrawer = true; checkoutStep = 'cart';"
            class="fixed bottom-8 right-8 z-30 w-16 h-16 rounded-full glass-card hover:bg-white/[0.08] flex items-center justify-center border-white/20 shadow-[0_12px_40px_0_rgba(0,0,0,0.5)] hover:scale-110 active:scale-95 transition-all duration-300"
        >
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <!-- Pulsing Badge -->
            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 text-white font-bold text-xs flex items-center justify-center border border-white/20 shadow-lg shadow-indigo-500/40 animate-pulse">
                {{ cartCount }}
            </span>
        </button>

        <!-- Product Variant Selection Modal (Centered Glass) -->
        <div 
            v-if="showSelectionModal && selectedProduct" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <!-- Backdrop Overlay -->
            <div @click="showSelectionModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-md"></div>
            
            <!-- Modal Body -->
            <div class="glass-card w-full max-w-2xl overflow-hidden rounded-[2.5rem] relative z-10 border border-white/10 shadow-[0_24px_50px_-12px_rgba(0,0,0,0.7)] flex flex-col md:flex-row h-[500px] md:h-auto max-h-[90vh]">
                
                <!-- Left: Image Area -->
                <div class="md:w-1/2 relative bg-slate-900/60 aspect-[4/5] md:aspect-auto">
                    <img 
                        v-if="selectedProduct.images && selectedProduct.images.length > 0" 
                        :src="selectedProduct.images[0].image_path" 
                        class="w-full h-full object-cover object-top"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-600 bg-slate-950">
                        No Image
                    </div>
                    <!-- Close button for Mobile inside image panel -->
                    <button 
                        @click="showSelectionModal = false" 
                        class="md:hidden absolute top-4 right-4 w-9 h-9 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center text-white border border-white/10"
                    >
                        ✕
                    </button>
                </div>

                <!-- Right: Details & Selection Form -->
                <div class="p-6 md:p-8 md:w-1/2 flex flex-col justify-between overflow-y-auto">
                    <!-- Heading Area -->
                    <div>
                        <div class="flex justify-between items-start gap-4 mb-2">
                            <span class="text-[10px] font-bold tracking-widest text-indigo-400 uppercase bg-indigo-500/10 px-2.5 py-1 rounded-full border border-indigo-500/20">
                                {{ selectedProduct.category?.name || 'Garments' }}
                            </span>
                            <!-- Close Button Desktop -->
                            <button @click="showSelectionModal = false" class="hidden md:block text-slate-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-white tracking-tight mb-2">{{ selectedProduct.name }}</h2>
                        <p class="text-slate-400 text-sm font-medium leading-relaxed mb-5">{{ selectedProduct.description || 'No description available for this luxury product.' }}</p>
                    </div>

                    <!-- Options Area -->
                    <div class="space-y-5 my-4">
                        <!-- Select Size -->
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Select Size</span>
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="size in getUniqueSizes(selectedProduct.variants)" 
                                    :key="size"
                                    @click="selectedSize = size"
                                    :class="[selectedSize === size ? 'bg-indigo-600/90 border-indigo-400 text-white shadow-lg shadow-indigo-500/20' : 'bg-white/[0.03] border-white/10 text-slate-300 hover:bg-white/[0.08]']"
                                    class="px-4 py-2 text-xs font-bold rounded-xl border transition-all duration-200"
                                >
                                    {{ size }}
                                </button>
                            </div>
                        </div>

                        <!-- Select Color -->
                        <div>
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Select Color</span>
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="color in getUniqueColors(selectedProduct.variants)" 
                                    :key="color"
                                    @click="selectedColor = color"
                                    :class="[selectedColor === color ? 'bg-white/[0.08] border-white/30 text-white' : 'bg-white/[0.03] border-white/10 text-slate-400 hover:bg-white/[0.08]']"
                                    class="px-4 py-2 text-xs font-bold rounded-xl border flex items-center gap-2 transition-all duration-200"
                                >
                                    <span :class="getColorClass(color)" class="w-3.5 h-3.5 rounded-full ring-1 ring-white/20"></span>
                                    {{ color }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Price / Action Area -->
                    <div class="pt-5 border-t border-white/[0.08] flex items-center justify-between gap-4 mt-auto">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Total Price</span>
                            <span class="text-2xl font-black text-white">${{ variantPrice.toFixed(2) }}</span>
                        </div>

                        <button 
                            @click="handleAddToCart"
                            :disabled="!selectedVariant"
                            class="glass-button-primary flex-1 py-3.5 font-bold text-sm rounded-2xl flex justify-center items-center shadow-lg shadow-indigo-500/20 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-300 hover:scale-105 active:scale-95"
                        >
                            {{ selectedVariant ? 'Add to Shopping Bag' : 'Select Options First' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

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

        <!-- Dynamic Version/Footer Status -->
        <footer class="mt-20 py-8 text-center text-xs text-slate-500">
            ESTY Garments Platform &bull; Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
        </footer>
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
.slide-slide-leave-to,
.slide-leave-to {
    transform: translateX(100%);
}
</style>
