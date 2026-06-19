<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useCart } from '@/Composables/useCart';
import { useWishlist } from '@/Composables/useWishlist';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
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
const showInStockOnly = ref(false);

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

    // Filter by Stock Status
    if (showInStockOnly.value) {
        result = result.filter(product => {
            return product.variants && product.variants.some(v => v.stock_quantity > 0);
        });
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
const { wishlist, toggleWishlist, removeFromWishlist, isInWishlist, wishlistCount } = useWishlist();

const showWishlistDrawer = ref(false);

// Selection Modal States
const showSelectionModal = ref(false);
const selectedProduct = ref(null);
const selectedSize = ref('');
const selectedColor = ref('');

// Cart Drawer States
const showCartDrawer = ref(false);
const checkoutStep = ref('cart'); // 'cart' or 'checkout'
const page = usePage();
const shippingAddress = ref(page.props.auth.user?.shipping_address || '');
const phone = ref(page.props.auth.user?.phone || '');
const showSuccessAlert = ref(false);

const checkoutForm = useForm({
    shipping_address: '',
    phone: '',
    items: []
});

const colorMap = {
    oatmeal: '#e5dcd3',
    charcoal: '#2f3542',
    navy: '#1e272e',
    black: '#111111',
    sage: '#a3b19b',
    beige: '#d2b48c',
    khaki: '#c3b091',
    gray: '#718093',
    grey: '#718093',
    white: '#f5f6fa',
    olive: '#57606f',
    rose: '#fda7df',
    red: '#ff7675',
    blue: '#74b9ff',
    green: '#55efc4',
    pink: '#ff80ab',
    yellow: '#feca57',
    orange: '#ff9f43',
    purple: '#9c27b0',
    brown: '#8d6e63',
    cream: '#fffdd0',
    tan: '#d2b48c',
    maroon: '#800000',
    burgundy: '#800020',
    teal: '#008080',
    lavender: '#e6e6fa',
    mustard: '#e1ad01',
    camel: '#c19a6b',
    coral: '#ff7f50',
    sand: '#c2b280',
    mint: '#98ff98',
    indigo: '#4b0082',
    violet: '#ee82ee',
    plum: '#dda0dd',
};

const getColorStyle = (colorName) => {
    const cleanColor = colorName.toLowerCase().trim();
    return {
        backgroundColor: colorMap[cleanColor] || cleanColor
    };
};

const getUniqueSizes = (variants) => {
    if (!variants) return [];
    const sizes = variants.map(v => v.size);
    return [...new Set(sizes)].filter(Boolean);
};

const getUniqueColors = (variants) => {
    if (!variants) return [];
    const colors = variants.map(v => v.color);
    return [...new Set(colors)].filter(Boolean);
};

// Map to track active selected image or color swatch for each product cards
const productDisplayStates = ref({});

const getActiveProductImage = (product) => {
    const state = productDisplayStates.value[product.id];
    if (state && state.selectedImage) {
        return state.selectedImage;
    }
    const primary = product.images.find(img => img.is_primary);
    return primary ? primary.image_path : product.images[0]?.image_path;
};

const getActiveColor = (product) => {
    const state = productDisplayStates.value[product.id];
    return state ? state.activeColor : null;
};

const setProductColor = (productId, colorName) => {
    const product = props.products.find(p => p.id === productId);
    if (!product) return;

    if (!productDisplayStates.value[productId]) {
        productDisplayStates.value[productId] = {
            activeColor: null,
            selectedImage: null
        };
    }

    productDisplayStates.value[productId].activeColor = colorName;

    // Find image matching color
    const colorImage = product.images.find(img => img.color && img.color.toLowerCase() === colorName.toLowerCase());
    if (colorImage) {
        productDisplayStates.value[productId].selectedImage = colorImage.image_path;
    }
};

const openSelectionModal = (product) => {
    selectedProduct.value = product;
    
    // Set default selections
    const sizes = getUniqueSizes(product.variants);
    const colors = getUniqueColors(product.variants);
    selectedSize.value = sizes[0] || '';
    selectedColor.value = colors[0] || '';
    
    showSelectionModal.value = true;
};

const modalProductImage = computed(() => {
    if (!selectedProduct.value) return null;
    
    // If a color is selected, try to find image for that color
    if (selectedColor.value) {
        const colorImg = selectedProduct.value.images.find(img => img.color && img.color.toLowerCase() === selectedColor.value.toLowerCase());
        if (colorImg) return colorImg.image_path;
    }
    
    // Fallback to primary image
    const primary = selectedProduct.value.images.find(img => img.is_primary);
    return primary ? primary.image_path : selectedProduct.value.images[0]?.image_path;
});

const handleQuickAdd = () => {
    if (!selectedProduct.value) return;
    
    // Find matching variant
    const variant = selectedProduct.value.variants.find(v => 
        v.size === selectedSize.value && 
        v.color === selectedColor.value
    );
    
    if (!variant) {
        alert('Selected options combo not available. Please choose a different size or color.');
        return;
    }
    
    addToCart(selectedProduct.value, variant, 1);
    showSelectionModal.value = false;
    showCartDrawer.value = true;
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
            setTimeout(() => {
                showSuccessAlert.value = false;
            }, 6000);
        }
    });
};

onMounted(() => {
    // Check if category query param exists in URL
    const urlParams = new URLSearchParams(window.location.search);
    const categoryIdParam = urlParams.get('category');
    if (categoryIdParam) {
        selectedCategoryId.value = parseInt(categoryIdParam);
    }

    // Scroll reveal observer initialization
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale').forEach(el => {
        observer.observe(el);
    });
});
</script>

<template>
    <Head title="Products Catalog - ESTY" />

    <div class="min-h-screen relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        
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
                    <Link :href="route('projects.index')" class="text-sm font-semibold text-white transition-colors duration-200">Products</Link>
                    <button @click="showWishlistDrawer = true" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200 flex items-center gap-1.5 focus:outline-none">
                        Wishlist
                        <span v-if="wishlistCount > 0" class="px-2 py-0.5 rounded-full text-[10px] bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold">{{ wishlistCount }}</span>
                    </button>
                    <Link v-if="$page.props.auth.user" :href="route('orders.index')" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">My Orders</Link>
                </div>

                <!-- Auth Navigation -->
                <div class="flex items-center gap-3">
                    <template v-if="$page.props.auth.user">
                        <Link
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('dashboard')"
                            class="glass-button text-xs py-2 px-4 rounded-full border-white/10"
                        >
                            Admin Dashboard
                        </Link>
                        <Link
                            :href="route('profile.edit')"
                            class="glass-button text-xs py-2 px-4 rounded-full border-white/10"
                        >
                            Profile
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
                            :href="route('register')"
                            class="glass-button-primary text-xs py-2.5 px-4.5 rounded-full border-white/15"
                        >
                            Register
                        </Link>
                    </template>
                </div>
            </nav>
        </div>

        <!-- Page Intro Hero -->
        <section class="pt-36 pb-12 px-6 max-w-7xl mx-auto relative z-10 text-center reveal">
            <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest bg-indigo-500/10 px-3 py-1.5 rounded-full border border-indigo-500/20">
                Studio Portfolios
            </span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mt-6 mb-4">
                Explore Our Garment Products
            </h1>
            <p class="text-slate-400 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
                An archival showcase of all modern garments designed, tailored, and engineered with premium variant-level precision.
            </p>
        </section>

        <!-- Main Workspace: Filters & Catalog Grid -->
        <main class="pb-24 px-6 max-w-7xl mx-auto relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
                
                <!-- Sticky Sidebar Filters (Desktop) -->
                <aside class="glass-card p-6 border-white/10 space-y-6 lg:sticky lg:top-28 reveal-left">
                    <h3 class="text-lg font-bold text-white border-b border-white/[0.06] pb-3 flex items-center justify-between">
                        <span>Filters</span>
                        <button 
                            @click="searchQuery = ''; selectedCategoryId = null; sortBy = 'latest'; showInStockOnly = false;"
                            class="text-xs text-indigo-400 hover:text-indigo-300 font-medium"
                        >
                            Reset All
                        </button>
                    </h3>

                    <!-- Search -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Search</label>
                        <div class="relative">
                            <input 
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search designs..."
                                class="glass-input pl-10 text-sm"
                            />
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Categories</label>
                        <div class="flex flex-col gap-1.5">
                            <button 
                                @click="selectedCategoryId = null"
                                class="text-left py-2 px-3 rounded-xl text-sm transition-all duration-200 flex items-center justify-between"
                                :class="selectedCategoryId === null ? 'bg-white/[0.08] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-white/[0.03]'"
                            >
                                <span>All Categories</span>
                                <span class="text-xs text-slate-500">({{ products.length }})</span>
                            </button>
                            <button 
                                v-for="category in categories"
                                :key="category.id"
                                @click="selectedCategoryId = category.id"
                                class="text-left py-2 px-3 rounded-xl text-sm transition-all duration-200 flex items-center justify-between"
                                :class="selectedCategoryId === category.id ? 'bg-white/[0.08] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-white/[0.03]'"
                            >
                                <span>{{ category.name }}</span>
                                <span class="text-xs text-slate-500">
                                    ({{ products.filter(p => p.category_id === category.id).length }})
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Sort By</label>
                        <select 
                            v-model="sortBy"
                            class="glass-input text-sm cursor-pointer appearance-none bg-slate-950"
                        >
                            <option value="latest" class="bg-slate-950 text-white">Latest Additions</option>
                            <option value="price_asc" class="bg-slate-950 text-white">Price: Low to High</option>
                            <option value="price_desc" class="bg-slate-950 text-white">Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Stock availability filter -->
                    <div class="flex items-center gap-3 pt-2">
                        <input 
                            v-model="showInStockOnly"
                            type="checkbox" 
                            id="stock_filter"
                            class="rounded border-white/10 bg-white/[0.03] text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-950 w-4 h-4 cursor-pointer"
                        />
                        <label for="stock_filter" class="text-sm text-slate-300 font-medium cursor-pointer select-none">
                            Show In-Stock Only
                        </label>
                    </div>
                </aside>

                <!-- Product Catalog List Grid -->
                <div class="lg:col-span-3 reveal-right">
                    
                    <!-- Search info summary -->
                    <div class="flex items-center justify-between mb-6">
                        <p class="text-slate-400 text-sm">
                            Showing <span class="text-white font-semibold">{{ filteredProducts.length }}</span> garments
                        </p>
                        <div class="md:hidden flex items-center gap-2">
                            <!-- Mobile sorting dropdown selector -->
                            <select 
                                v-model="sortBy"
                                class="appearance-none pl-3 pr-8 py-1.5 text-xs text-slate-300 bg-white/[0.04] border border-white/[0.08] rounded-full focus:outline-none cursor-pointer"
                            >
                                <option value="latest">Latest</option>
                                <option value="price_asc">Price: Low-High</option>
                                <option value="price_desc">Price: High-Low</option>
                            </select>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div 
                        v-if="filteredProducts.length === 0" 
                        class="glass-card py-24 px-4 text-center rounded-[2.5rem] border-white/[0.06] flex flex-col items-center justify-center"
                    >
                        <div class="w-16 h-16 rounded-full bg-white/[0.03] border border-white/[0.08] flex items-center justify-center text-slate-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-1">No garments found</h3>
                        <p class="text-slate-400 text-sm max-w-sm">We couldn't find any projects matching your selection. Try resetting filters.</p>
                    </div>

                    <!-- Grid -->
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link 
                            v-for="product in filteredProducts" 
                            :key="product.id" 
                            :href="route('products.show', product.slug)"
                            class="glass-card glass-card-hover group flex flex-col h-full rounded-[2.5rem] overflow-hidden p-3 cursor-pointer"
                        >
                            <!-- Product Image Container -->
                            <div class="relative aspect-[4/5] rounded-[2rem] overflow-hidden mb-4 bg-slate-900/40">
                                <img 
                                    v-if="product.images && product.images.length > 0"
                                    :src="getActiveProductImage(product)" 
                                    :alt="product.name" 
                                    class="w-full h-full object-cover object-top transition-transform duration-700 ease-in-out group-hover:scale-110"
                                />
                                <div 
                                    v-else 
                                    class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-tr from-slate-900/80 to-slate-800/80 text-slate-500 gap-2"
                                >
                                    <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs">No Image</span>
                                </div>
                                
                                <!-- Floating Quick Actions -->
                                <div class="absolute inset-x-4 bottom-4 z-20 translate-y-3 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 flex gap-2">
                                    <button 
                                        @click.stop.prevent="openSelectionModal(product)"
                                        class="flex-1 py-3 bg-white text-slate-950 font-bold text-xs rounded-full hover:bg-slate-100 transition-all shadow-lg hover:shadow-white/20 hover:scale-[1.02] flex items-center justify-center gap-1.5"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Quick Add
                                    </button>
                                    <button 
                                        @click.stop.prevent="toggleWishlist(product)"
                                        class="w-11 h-11 bg-slate-950/80 backdrop-blur-md text-white rounded-full border border-white/10 flex items-center justify-center transition-all hover:bg-slate-900 hover:border-white/20 active:scale-90"
                                    >
                                        <svg 
                                            class="w-4 h-4 transition-colors"
                                            :class="isInWishlist(product.id) ? 'fill-rose-500 text-rose-500' : 'text-slate-300 group-hover/btn:text-white'"
                                            fill="none" 
                                            stroke="currentColor" 
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Product Details Text Info -->
                            <div class="p-5 flex flex-col flex-1">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">
                                        {{ product.category?.name || 'Garment' }}
                                    </span>
                                </div>

                                <div class="flex justify-between items-start gap-4 mb-4">
                                    <h3 class="font-bold text-white text-base tracking-tight leading-tight group-hover:text-indigo-400 transition-colors">
                                        {{ product.name }}
                                    </h3>
                                    <span class="font-extrabold text-white text-lg">${{ parseFloat(product.base_price).toFixed(2) }}</span>
                                </div>

                                <!-- Variant Specs: Colors & Sizes -->
                                <div class="flex items-center justify-between mt-auto pt-4 border-t border-white/[0.06]">
                                    <!-- Color Swatches -->
                                    <div class="flex items-center gap-1.5">
                                        <button 
                                            v-for="(color, cIdx) in getUniqueColors(product.variants)" 
                                            :key="cIdx"
                                            @click.stop.prevent="setProductColor(product.id, color)"
                                            :class="[
                                                getActiveColor(product) === color ? 'ring-2 ring-indigo-400 scale-125 z-10' : 'ring-1 ring-white/20 hover:scale-110'
                                            ]" 
                                            class="w-3.5 h-3.5 rounded-full transition-transform focus:outline-none overflow-hidden flex items-center justify-center"
                                            :title="color"
                                        >
                                            <span :style="getColorStyle(color)" class="w-full h-full block"></span>
                                        </button>
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
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Floating Shopping Cart Button (Bottom Right) -->
        <button 
            @click="showCartDrawer = true; checkoutStep = 'cart';"
            class="fixed bottom-8 right-8 z-30 w-16 h-16 rounded-full glass-card hover:bg-white/[0.08] flex items-center justify-center border-white/20 shadow-[0_12px_40px_0_rgba(0,0,0,0.5)] hover:scale-110 active:scale-95 transition-all duration-300"
        >
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 text-white font-bold text-xs flex items-center justify-center border border-white/20 shadow-lg shadow-indigo-500/40 animate-pulse">
                {{ cartCount }}
            </span>
        </button>

        <!-- Product Variant Selection Modal (Centered Glass) -->
        <div 
            v-if="showSelectionModal && selectedProduct" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div @click="showSelectionModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-md"></div>
            
            <div class="glass-card w-full max-w-2xl overflow-hidden rounded-[2.5rem] relative z-10 border border-white/10 shadow-[0_24px_50px_-12px_rgba(0,0,0,0.7)] flex flex-col md:flex-row h-[500px] md:h-auto max-h-[90vh]">
                
                <div class="md:w-1/2 relative bg-slate-900/60 aspect-[4/5] md:aspect-auto">
                    <img 
                        v-if="modalProductImage" 
                        :src="modalProductImage" 
                        class="w-full h-full object-cover object-top"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-600 bg-slate-950">
                        No Image
                    </div>
                    <button 
                        @click="showSelectionModal = false" 
                        class="md:hidden absolute top-4 right-4 w-9 h-9 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center text-white border border-white/10"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6 md:p-8 md:w-1/2 flex flex-col justify-between overflow-y-auto">
                    <div>
                        <div class="flex justify-between items-start gap-4 mb-2">
                            <span class="text-[10px] font-bold tracking-widest text-indigo-400 uppercase bg-indigo-500/10 px-2.5 py-1 rounded-full border border-indigo-500/20">
                                {{ selectedProduct.category?.name || 'Garments' }}
                            </span>
                            <button @click="showSelectionModal = false" class="hidden md:block text-slate-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-white tracking-tight mb-2">{{ selectedProduct.name }}</h2>
                        <p class="text-slate-400 text-sm font-medium leading-relaxed mb-5">{{ selectedProduct.description || 'No description available for this luxury product.' }}</p>
                    </div>

                    <div class="space-y-5 my-4">
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
                                    <span class="w-3.5 h-3.5 rounded-full ring-1 ring-white/20 border border-white/10 overflow-hidden inline-flex">
                                        <span :style="getColorStyle(color)" class="w-full h-full block"></span>
                                    </span>
                                    <span class="capitalize">{{ color }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/[0.06] flex items-center justify-between gap-4">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Price</span>
                            <span class="text-xl font-extrabold text-white">${{ parseFloat(selectedProduct.base_price).toFixed(2) }}</span>
                        </div>
                        <button 
                            @click="handleQuickAdd"
                            class="flex-1 glass-button-primary font-bold text-sm py-3 px-6 rounded-2xl flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Add to Bag
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wishlist Drawer Slider (Glass Panel) -->
        <div 
            class="fixed inset-y-0 right-0 z-50 w-full max-w-md pointer-events-none transition-all duration-500 ease-in-out-cubic"
            :class="[showWishlistDrawer ? 'translate-x-0' : 'translate-x-full']"
        >
            <div 
                class="absolute inset-y-0 right-0 w-full bg-slate-950/80 backdrop-blur-3xl border-l border-white/10 shadow-[0_0_50px_0_rgba(0,0,0,0.8)] pointer-events-auto flex flex-col justify-between"
            >
                <div class="p-6 overflow-y-auto flex-1">
                    <div class="flex items-center justify-between border-b border-white/[0.06] pb-4 mb-6">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-500 fill-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <h2 class="text-xl font-bold text-white tracking-tight">Wishlist ({{ wishlistCount }})</h2>
                        </div>
                        <button @click="showWishlistDrawer = false" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div v-if="wishlistCount === 0" class="py-20 text-center flex flex-col items-center justify-center">
                        <div class="w-14 h-14 rounded-full bg-white/[0.02] border border-white/5 flex items-center justify-center text-slate-500 mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <p class="text-slate-400 text-sm">Your wishlist is empty.</p>
                        <button 
                            @click="showWishlistDrawer = false" 
                            class="text-indigo-400 hover:text-indigo-300 font-semibold text-xs mt-3 uppercase tracking-wider"
                        >
                            Discover Garments
                        </button>
                    </div>

                    <!-- Items List -->
                    <div v-else class="space-y-4">
                        <div 
                            v-for="item in wishlist" 
                            :key="item.id"
                            class="glass-card p-4 border-white/5 flex gap-4 relative group"
                        >
                            <div class="w-20 h-24 rounded-xl overflow-hidden bg-slate-900 shrink-0">
                                <img 
                                    v-if="item.images && item.images.length > 0" 
                                    :src="item.images.find(i => i.is_primary)?.image_path || item.images[0].image_path" 
                                    class="w-full h-full object-cover object-top"
                                />
                            </div>

                            <div class="flex-1 flex flex-col justify-between py-1">
                                <div>
                                    <h4 class="font-bold text-white text-sm line-clamp-1 pr-6">{{ item.name }}</h4>
                                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-0.5 block">
                                        {{ item.category?.name }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-extrabold text-white">${{ parseFloat(item.base_price).toFixed(2) }}</span>
                                    <button 
                                        @click="openSelectionModal(item); showWishlistDrawer = false;" 
                                        class="glass-button text-[10px] font-bold py-1.5 px-3 rounded-lg border-white/5 text-indigo-400"
                                    >
                                        Add to Bag
                                    </button>
                                </div>
                            </div>

                            <button 
                                @click="removeFromWishlist(item.id)" 
                                class="absolute top-4 right-4 text-slate-500 hover:text-rose-400 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Drawer Slider (Glass Panel) -->
        <div 
            class="fixed inset-y-0 right-0 z-50 w-full max-w-md pointer-events-none transition-all duration-500 ease-in-out-cubic"
            :class="[showCartDrawer ? 'translate-x-0' : 'translate-x-full']"
        >
            <div 
                class="absolute inset-y-0 right-0 w-full bg-slate-950/80 backdrop-blur-3xl border-l border-white/10 shadow-[0_0_50px_0_rgba(0,0,0,0.8)] pointer-events-auto flex flex-col justify-between"
            >
                <div class="p-6 border-b border-white/[0.06] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h2 class="text-xl font-bold text-white tracking-tight">
                            {{ checkoutStep === 'cart' ? 'Shopping Bag' : 'Shipping Details' }} ({{ cartCount }})
                        </h2>
                    </div>
                    <button @click="showCartDrawer = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    <div v-if="checkoutStep === 'checkout'" class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Shipping Address</label>
                            <textarea 
                                v-model="shippingAddress" 
                                class="glass-input text-sm h-24 resize-none"
                                placeholder="Enter your full street address, apartment, city, and zip..."
                            ></textarea>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Contact Phone Number</label>
                            <input 
                                v-model="phone" 
                                type="text"
                                class="glass-input text-sm"
                                placeholder="+95 9..."
                            />
                        </div>
                        <div class="p-4 bg-white/[0.02] border border-white/5 rounded-2xl mt-4">
                            <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-2">Order Review</h4>
                            <div class="flex justify-between text-slate-400 text-xs mb-1">
                                <span>Items Subtotal</span>
                                <span>${{ cartTotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-400 text-xs mb-2">
                                <span>Delivery Fee</span>
                                <span class="text-emerald-400">FREE</span>
                            </div>
                            <div class="h-px bg-white/10 my-2"></div>
                            <div class="flex justify-between text-white font-bold text-sm">
                                <span>Grand Total</span>
                                <span>${{ cartTotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-else>
                        <div v-if="cartCount === 0" class="py-20 text-center flex flex-col items-center justify-center">
                            <div class="w-14 h-14 rounded-full bg-white/[0.02] border border-white/5 flex items-center justify-center text-slate-500 mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <p class="text-slate-400 text-sm">Your shopping bag is empty.</p>
                            <button 
                                @click="showCartDrawer = false" 
                                class="text-indigo-400 hover:text-indigo-300 font-semibold text-xs mt-3 uppercase tracking-wider"
                            >
                                Continue Shopping
                            </button>
                        </div>

                        <div v-else class="space-y-4">
                            <div 
                                v-for="item in cart" 
                                :key="`${item.product_id}-${item.variant_id}`"
                                class="glass-card p-4 border-white/5 flex gap-4 relative group"
                            >
                                <div class="w-20 h-24 rounded-xl overflow-hidden bg-slate-900 shrink-0">
                                    <img 
                                        v-if="item.product_image" 
                                        :src="item.product_image" 
                                        class="w-full h-full object-cover object-top"
                                    />
                                </div>

                                <div class="flex-1 flex flex-col justify-between py-1">
                                    <div>
                                        <h4 class="font-bold text-white text-sm line-clamp-1 pr-6">{{ item.name }}</h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] font-bold text-slate-400 bg-white/[0.04] px-1.5 py-0.5 rounded border border-white/[0.05] uppercase">
                                                Size {{ item.size }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 bg-white/[0.04] px-1.5 py-0.5 rounded border border-white/[0.05] capitalize flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full inline-block" :style="getColorStyle(item.color)"></span>
                                                {{ item.color }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-extrabold text-white">${{ parseFloat(item.price).toFixed(2) }}</span>
                                        
                                        <div class="flex items-center bg-white/[0.03] border border-white/10 rounded-lg p-0.5 overflow-hidden">
                                            <button 
                                                @click="updateQuantity(item.product_id, item.variant_id, item.quantity - 1)" 
                                                class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-white font-bold transition-colors"
                                            >
                                                -
                                            </button>
                                            <span class="w-6 text-center text-xs font-bold text-white">{{ item.quantity }}</span>
                                            <button 
                                                @click="updateQuantity(item.product_id, item.variant_id, item.quantity + 1)" 
                                                class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-white font-bold transition-colors"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button 
                                    @click="removeFromCart(item.product_id, item.variant_id)" 
                                    class="absolute top-4 right-4 text-slate-500 hover:text-rose-400 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="cartCount > 0" class="p-6 border-t border-white/[0.06] space-y-4">
                    <div class="flex justify-between items-center text-white">
                        <span class="font-semibold text-sm">Bag Subtotal</span>
                        <span class="text-xl font-extrabold">${{ cartTotal.toFixed(2) }}</span>
                    </div>

                    <div class="flex gap-2">
                        <button 
                            v-if="checkoutStep === 'checkout'" 
                            @click="checkoutStep = 'cart'" 
                            class="w-12 h-12 bg-white/[0.05] hover:bg-white/[0.1] text-white rounded-2xl flex items-center justify-center border border-white/10 active:scale-95 transition-all"
                        >
                            ←
                        </button>
                        
                        <button 
                            v-if="checkoutStep === 'cart'"
                            @click="checkoutStep = 'checkout'"
                            class="flex-1 glass-button-primary font-bold text-sm py-3 px-6 rounded-2xl flex items-center justify-center gap-2"
                        >
                            Proceed to Checkout
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <button 
                            v-else
                            @click="submitCheckout"
                            :disabled="checkoutForm.processing || !shippingAddress || !phone"
                            class="flex-1 glass-button-primary font-bold text-sm py-3 px-6 rounded-2xl flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ checkoutForm.processing ? 'Placing Order...' : 'Place Cash Order' }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer component -->
        <Footer />
    </div>
</template>
