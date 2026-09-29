<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { useCartStore } from '@/Stores/cartStore';
import { useWishlistStore } from '@/Stores/wishlistStore';
import Footer from '@/Components/Footer.vue';
import HeroCarousel from '@/Components/HeroCarousel.vue';
import CartDrawer from '@/Components/CartDrawer.vue';
import WishlistDrawer from '@/Components/WishlistDrawer.vue';
import QuickAddModal from '@/Components/QuickAddModal.vue';
import HeroIcon from '@/Components/HeroIcon.vue';

import LoadingOverlay from '@/Components/LoadingOverlay.vue';
import NavigationBar from '@/Components/NavigationBar.vue';

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
    },
    heroSlides: {
        type: Array,
        required: true,
    },
    lookbooks: {
        type: Array,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    }
});

// Search and Filter States
const searchQuery = ref('');
const selectedCategoryId = ref(null);
const sortBy = ref('latest');

// Computed Filtered & Sorted Products for main showcase
onMounted(() => {
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

    if (window.location.hash) {
        setTimeout(() => {
            const element = document.querySelector(window.location.hash);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }, 100);
    }
});

const { cartCount } = useCartStore();
const { wishlistCount, toggleWishlist, isInWishlist } = useWishlistStore();

const showWishlistDrawer = ref(false);
const showCartDrawer = ref(false);
const showSelectionModal = ref(false);
const selectedProduct = ref(null);
const showSuccessAlert = ref(false);
const isFiltering = ref(false);

watch([selectedCategoryId, searchQuery, sortBy], () => {
    isFiltering.value = true;
    setTimeout(() => {
        isFiltering.value = false;
    }, 450);
});

const openProductModal = (product) => {
    selectedProduct.value = product;
    showSelectionModal.value = true;
};

const triggerSuccessAlert = () => {
    showSuccessAlert.value = true;
    setTimeout(() => {
        showSuccessAlert.value = false;
    }, 6000);
};

const selectedProductColors = ref({});
const getUniqueColors = (variants) => {
    if (!variants) return [];
    return [...new Set(variants.map(v => v.color))];
};

const getUniqueSizes = (variants) => {
    if (!variants) return [];
    const sizes = variants.map(v => v.size);
    return [...new Set(sizes)].filter(Boolean);
};

const getActiveProductImage = (product) => {
    const selectedColor = selectedProductColors.value[product.id];
    if (selectedColor && product.images) {
        const normalizedColor = selectedColor.toLowerCase().trim();
        
        let matchingImage = product.images.find(img => 
            img.color && img.color.toLowerCase().trim() === normalizedColor
        );
        
        if (!matchingImage) {
            matchingImage = product.images.find(img => 
                img.image_path && img.image_path.toLowerCase().includes(normalizedColor)
            );
        }
        
        if (matchingImage) return matchingImage.image_path;
    }
    return product.images && product.images.length > 0 ? product.images[0].image_path : null;
};

const getActiveColor = (product) => {
    if (selectedProductColors.value[product.id]) {
        return selectedProductColors.value[product.id];
    }
    const colors = getUniqueColors(product.variants);
    return colors[0] || null;
};

const setProductColor = (productId, color) => {
    selectedProductColors.value[productId] = color;
};

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
};

const getColorStyle = (colorName) => {
    if (!colorName) return { backgroundColor: '#718093' };
    const norm = colorName.toLowerCase().trim();
    return { backgroundColor: colorMap[norm] || norm };
};

// Filtered items logic
const filteredProducts = computed(() => {
    let result = Array.isArray(props.products) ? [...props.products] : Object.values(props.products || {});

    if (selectedCategoryId.value !== null) {
        result = result.filter(product => product.category_id === selectedCategoryId.value);
    }

    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(product => 
            product.name.toLowerCase().includes(query) || 
            (product.description && product.description.toLowerCase().includes(query))
        );
    }

    if (sortBy.value === 'latest') {
        result.sort((a, b) => b.id - a.id);
    } else if (sortBy.value === 'price_asc') {
        result.sort((a, b) => parseFloat(a.base_price) - parseFloat(b.base_price));
    } else if (sortBy.value === 'price_desc') {
        result.sort((a, b) => parseFloat(b.base_price) - parseFloat(a.base_price));
    }

    return result;
});

</script>

<template>
    <Head title="ESTY Garments - Premium Clothing" />

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

        <!-- Floating Header/Navigation -->
        <NavigationBar 
            :can-login="canLogin" 
            :can-register="canRegister" 
            active="home" 
            @open-wishlist="showWishlistDrawer = true" 
        />

        <!-- Hero Slideshow Carousel Section -->
        <HeroCarousel :slides="heroSlides" />

        <!-- Value Highlights Banner -->
        <section class="py-12 border-b border-white/[0.04] bg-white/[0.01] relative z-10">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="flex items-start gap-4 reveal" v-if="settings.feature_1_title">
                    <div class="w-10 h-10 rounded-xl bg-white/[0.03] border border-white/5 flex items-center justify-center text-indigo-400 shrink-0">
                        <HeroIcon :name="settings.feature_1_icon || 'truck'" />
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-xs sm:text-sm tracking-wide uppercase">{{ settings.feature_1_title }}</h4>
                        <p class="text-slate-400 text-xs mt-1">{{ settings.feature_1_subtitle }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 reveal" v-if="settings.feature_2_title">
                    <div class="w-10 h-10 rounded-xl bg-white/[0.03] border border-white/5 flex items-center justify-center text-purple-400 shrink-0">
                        <HeroIcon :name="settings.feature_2_icon || 'arrow-path'" />
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-xs sm:text-sm tracking-wide uppercase">{{ settings.feature_2_title }}</h4>
                        <p class="text-slate-400 text-xs mt-1">{{ settings.feature_2_subtitle }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 reveal" v-if="settings.feature_3_title">
                    <div class="w-10 h-10 rounded-xl bg-white/[0.03] border border-white/5 flex items-center justify-center text-pink-400 shrink-0">
                        <HeroIcon :name="settings.feature_3_icon || 'check-badge'" />
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-xs sm:text-sm tracking-wide uppercase">{{ settings.feature_3_title }}</h4>
                        <p class="text-slate-400 text-xs mt-1">{{ settings.feature_3_subtitle }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 reveal" v-if="settings.feature_4_title">
                    <div class="w-10 h-10 rounded-xl bg-white/[0.03] border border-white/5 flex items-center justify-center text-teal-400 shrink-0">
                        <HeroIcon :name="settings.feature_4_icon || 'adjustments'" />
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-xs sm:text-sm tracking-wide uppercase">{{ settings.feature_4_title }}</h4>
                        <p class="text-slate-400 text-xs mt-1">{{ settings.feature_4_subtitle }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Shop by Category Section -->
        <section class="py-16 px-6 max-w-7xl mx-auto relative z-10 border-b border-white/[0.04] reveal">
            <div class="mb-10 text-center lg:text-left">
                <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Collections</span>
                <h2 class="text-3xl font-extrabold text-white tracking-tight mt-1">Shop by Category</h2>
                <p class="text-slate-400 text-sm mt-1.5 font-medium">Browse premium garments curated by seasonal collections.</p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <Link 
                    v-for="category in categories" 
                    :key="category.id" 
                    :href="route('products.index', { category: category.id })"
                    class="glass-card hover:bg-white/[0.06] hover:border-white/[0.12] hover:shadow-[0_12px_40px_rgba(0,0,0,0.5)] group relative aspect-[4/3] rounded-3xl overflow-hidden flex flex-col justify-end p-5 border border-white/5 active:scale-[0.98] transition-all duration-300 cursor-pointer"
                >
                    <!-- Background image banner -->
                    <div class="absolute inset-0 w-full h-full">
                        <img 
                            v-if="category.image_path"
                            :src="category.image_path" 
                            :alt="category.name"
                            class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                        />
                        <div v-else class="w-full h-full bg-slate-900/60 flex items-center justify-center text-slate-700">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                    </div>
                    
                    <div class="relative z-10">
                        <h4 class="font-extrabold text-white text-base tracking-tight">{{ category.name }}</h4>
                        <p class="text-slate-300 text-[10px] font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-300 line-clamp-1 mt-0.5">
                            {{ category.description || 'Explore collection' }}
                        </p>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Product Grid Section -->
        <section id="shop" class="py-16 px-4 max-w-7xl mx-auto relative z-10">
            <!-- Search & Filters -->
            <div class="flex flex-col gap-6 mb-12">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-3.5">
                            <h2 class="text-3xl font-extrabold text-white tracking-tight">Featured Garments</h2>
                            <Link 
                                :href="route('products.index')" 
                                class="glass-button text-[11px] font-bold py-1.5 px-3.5 rounded-full border border-white/5 text-indigo-400 hover:text-indigo-300 flex items-center gap-1 hover:bg-white/[0.05] transition-all active:scale-[0.97]"
                            >
                                View All Products
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                        <p class="text-slate-400 font-medium mt-1">Modern essentials engineered with variant-level precision.</p>
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
            
            <div class="relative min-h-[300px]">
                <LoadingOverlay :active="isFiltering" message="Updating garments catalog..." />

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
                            <svg class="w-10 h-10 stroke-current" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs uppercase font-bold tracking-widest text-slate-600">No Image</span>
                        </div>
                        
                        <!-- Category Badge -->
                        <span class="absolute top-4 left-4 text-[10px] font-bold tracking-widest text-white uppercase bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                            {{ product.category?.name || 'Garment' }}
                        </span>

                        <!-- Wishlist Toggle -->
                        <button 
                            @click.stop.prevent="toggleWishlist(product)"
                            class="absolute top-4 right-4 w-9 h-9 rounded-full flex items-center justify-center border transition-all duration-300 focus:outline-none backdrop-blur-md z-10"
                            :class="isInWishlist(product.id) ? 'bg-rose-500 border-rose-400 text-white shadow-lg shadow-rose-500/20' : 'bg-black/40 border-white/10 text-white hover:bg-black/60'"
                        >
                            <svg class="w-4 h-4" :fill="isInWishlist(product.id) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                        
                        <!-- Floating Add to Cart Quick Action -->
                        <button 
                            @click.stop.prevent="openProductModal(product)"
                            class="absolute bottom-4 right-4 w-11 h-11 rounded-full bg-white text-slate-950 flex items-center justify-center shadow-lg transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 hover:bg-indigo-600 hover:text-white"
                        >
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
                            <span class="font-extrabold text-white text-lg whitespace-nowrap shrink-0">{{ parseFloat(product.base_price).toLocaleString() }} Ks</span>
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
            <div class="flex justify-center mt-14">
                <Link 
                    :href="route('products.index')" 
                    class="glass-button-primary text-sm font-bold py-3.5 px-8 rounded-full border border-white/10 flex items-center gap-2 hover:scale-[1.03] active:scale-[0.97] transition-all duration-300 shadow-[0_8px_30px_rgba(99,102,241,0.15)] hover:shadow-[0_8px_40px_rgba(99,102,241,0.35)]"
                >
                    <span>Explore All Products & Garments</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </Link>
            </div>
        </section>

        <!-- Featured Collections Lookbook Section -->
        <section class="py-20 px-6 max-w-7xl mx-auto relative z-10 border-t border-white/[0.04]">
            <div class="text-center mb-16">
                <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Seasonal Lookbook</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mt-2">Curated Style Guides</h2>
                <p class="text-slate-400 text-sm mt-2 font-medium max-w-lg mx-auto">Explore editorial drops and streetwear essentials curated for the AW26 collection.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div 
                    v-for="(item, idx) in lookbooks" 
                    :key="item.id" 
                    :class="[idx % 2 === 0 ? 'reveal-left' : 'reveal-right']"
                    class="group relative h-[450px] rounded-3xl overflow-hidden border border-white/[0.06] shadow-2xl flex flex-col justify-end p-8 bg-slate-950"
                >
                    <div class="absolute inset-0 w-full h-full overflow-hidden">
                        <img 
                            :src="item.image_path" 
                            :alt="item.title" 
                            class="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>
                    </div>
                    <div class="relative z-10 space-y-3">
                        <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest bg-indigo-500/10 px-2.5 py-1 rounded-full border border-indigo-500/20">
                            {{ item.badge }}
                        </span>
                        <h3 class="text-2xl font-extrabold text-white tracking-tight">{{ item.title }}</h3>
                        <p class="text-slate-300 text-xs sm:text-sm max-w-xs leading-relaxed">{{ item.description }}</p>
                        <a :href="item.cta_link || '#shop'" class="glass-button text-xs font-bold px-5 py-2.5 rounded-full inline-block hover:bg-white/[0.12] transition-colors mt-2">
                            {{ item.cta_text || 'Explore Lookbook' }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Brand Story Editorial Section -->
        <section class="py-24 px-6 max-w-7xl mx-auto relative z-10 border-t border-white/[0.04]">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <!-- Left Editorial Text -->
                <div class="lg:col-span-7 space-y-6 flex flex-col justify-center text-center lg:text-left reveal-left">
                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Our Philosophy</span>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight" v-html="settings.brand_story_title"></h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed max-w-xl mx-auto lg:mx-0">
                        {{ settings.brand_story_text_1 }}
                    </p>
                    <p class="text-slate-500 text-xs sm:text-sm leading-relaxed max-w-xl mx-auto lg:mx-0">
                        {{ settings.brand_story_text_2 }}
                    </p>
                    <div class="pt-4">
                        <div class="flex items-center gap-6 justify-center lg:justify-start">
                            <div>
                                <span class="block text-2xl font-extrabold text-white">{{ settings.brand_story_stat_1_val }}</span>
                                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-1">{{ settings.brand_story_stat_1_lbl }}</span>
                            </div>
                            <div class="h-8 w-px bg-white/10"></div>
                            <div>
                                <span class="block text-2xl font-extrabold text-white">{{ settings.brand_story_stat_2_val }}</span>
                                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-1">{{ settings.brand_story_stat_2_lbl }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Close-up Detail Image -->
                <div class="lg:col-span-5 relative h-[400px] lg:h-[480px] rounded-3xl overflow-hidden border border-white/[0.06] shadow-2xl bg-slate-950 reveal-right">
                    <img 
                        :src="settings.brand_story_image" 
                        alt="Bespoke Tailoring Details" 
                        class="w-full h-full object-cover object-center hover:scale-105 transition-transform duration-700"
                    />
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

        <!-- Modals & Drawers -->
        <QuickAddModal 
            :show="showSelectionModal" 
            :product="selectedProduct" 
            @close="showSelectionModal = false" 
            @added-success="showCartDrawer = true" 
        />

        <CartDrawer 
            :show="showCartDrawer" 
            @close="showCartDrawer = false" 
            @order-success="triggerSuccessAlert" 
        />

        <WishlistDrawer 
            :show="showWishlistDrawer" 
            @close="showWishlistDrawer = false" 
            @open-product-modal="openProductModal" 
        />

        <Footer />
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
