<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const { props: pageProps } = usePage();
const userName = ref(pageProps.auth.user?.name || 'Admin');
const userEmail = ref(pageProps.auth.user?.email || 'admin@verone.com');

const props = defineProps({
    products: {
        type: Array,
        required: true
    }
});

const showProfileDropdown = ref(false);

// Search and Filter States
const searchQuery = ref('');
const selectedCategory = ref('All');
const stockFilter = ref('all'); // 'all' or 'low' (stock <= 10)
const sortBy = ref('name'); // 'name', 'price_asc', 'price_desc', 'stock_asc', 'stock_desc'

// Helper to sum stock of variants
const getTotalStock = (variants) => {
    return variants ? variants.reduce((total, variant) => total + parseInt(variant.stock_quantity || 0), 0) : 0;
};

// Computed Stats for Premium Dashboard Cards
const totalProductsCount = computed(() => props.products.length);

const totalStockQuantity = computed(() => {
    return props.products.reduce((acc, p) => acc + getTotalStock(p.variants), 0);
});

const lowStockCount = computed(() => {
    return props.products.filter(p => getTotalStock(p.variants) <= 10).length;
});

const activeProductsCount = computed(() => {
    return props.products.filter(p => p.is_active).length;
});

const activeRatio = computed(() => {
    const total = props.products.length;
    return total ? Math.round((activeProductsCount.value / total) * 100) : 0;
});

// Helper to get variants summary text
const getVariantsSummary = (variants) => {
    if (!variants || variants.length === 0) return 'No variants';
    const sizes = [...new Set(variants.map(v => v.size))];
    const colors = [...new Set(variants.map(v => v.color))];
    return `${sizes.join(', ')} / ${colors.join(', ')}`;
};

// Helper to find primary image or first image
const getPrimaryImageUrl = (images) => {
    if (!images || images.length === 0) return null;
    const primary = images.find(img => img.is_primary === 1 || img.is_primary === true);
    return primary ? primary.image_path : images[0].image_path;
};

// Extract unique categories from actual products list for dynamic filter tabs
const categoriesList = computed(() => {
    const list = new Set();
    props.products.forEach(p => {
        if (p.category?.name) {
            list.add(p.category.name);
        }
    });
    return ['All', ...Array.from(list)];
});

// Reactively filter and sort products
const filteredProducts = computed(() => {
    let result = [...props.products];

    // 1. Search Query Filter
    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase().trim();
        result = result.filter(p => {
            const nameMatch = p.name.toLowerCase().includes(query);
            const slugMatch = p.slug.toLowerCase().includes(query);
            const categoryMatch = p.category?.name?.toLowerCase().includes(query);
            const variantMatch = p.variants?.some(v => 
                v.size.toLowerCase().includes(query) || 
                v.color.toLowerCase().includes(query) ||
                (v.sku && v.sku.toLowerCase().includes(query))
            );
            return nameMatch || slugMatch || categoryMatch || variantMatch;
        });
    }

    // 2. Category Filter
    if (selectedCategory.value !== 'All') {
        result = result.filter(p => p.category?.name === selectedCategory.value);
    }

    // 3. Stock Level Filter
    if (stockFilter.value === 'low') {
        result = result.filter(p => getTotalStock(p.variants) <= 10);
    }

    // 4. Sorting
    result.sort((a, b) => {
        if (sortBy.value === 'name') {
            return a.name.localeCompare(b.name);
        } else if (sortBy.value === 'price_asc') {
            return parseFloat(a.base_price) - parseFloat(b.base_price);
        } else if (sortBy.value === 'price_desc') {
            return parseFloat(b.base_price) - parseFloat(a.base_price);
        } else if (sortBy.value === 'stock_asc') {
            return getTotalStock(a.variants) - getTotalStock(b.variants);
        } else if (sortBy.value === 'stock_desc') {
            return getTotalStock(b.variants) - getTotalStock(a.variants);
        }
        return 0;
    });

    return result;
});

// Toggle product active status direct from list
const toggleProductStatus = (productId) => {
    router.patch(route('admin.products.toggleStatus', productId), {}, {
        preserveScroll: true
    });
};

const toggleSidebar = () => {
    window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'));
};
</script>

<template>
    <Head title="Products Inventory - ESTY" />

    <div class="min-h-screen bg-[#080b11] text-slate-100 flex relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Ambient Shifts -->
        <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>

        <!-- Left Sidebar -->
        <AdminSidebar active="products" />

        <!-- Main Content Area -->
        <div class="flex-1 lg:ml-64 flex flex-col min-h-screen relative z-10">
            
            <!-- Top Navigation -->
            <header class="h-16 bg-white/[0.02] backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-4 sm:px-8 sticky top-0 z-10">
                <!-- Hamburger Menu Button -->
                <button 
                    @click="toggleSidebar" 
                    class="lg:hidden p-2 text-slate-400 hover:text-white transition-colors focus:outline-none"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="w-full max-w-[160px] sm:max-w-xs relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        v-model="searchQuery"
                        placeholder="Search products, sizes, colors..." 
                        class="glass-input pl-9 py-2 text-sm rounded-full"
                    />
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button 
                            @click="showProfileDropdown = !showProfileDropdown"
                            class="flex items-center gap-2 py-1 px-3 rounded-full hover:bg-white/[0.04] transition-colors focus:outline-none"
                        >
                            <span class="text-sm font-semibold text-white">{{ userName }}</span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': showProfileDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div 
                            v-if="showProfileDropdown" 
                            class="absolute right-0 mt-2 w-48 glass-card border-white/10 shadow-2xl p-2 rounded-2xl flex flex-col z-30"
                        >
                            <Link 
                                :href="route('profile.edit')" 
                                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/[0.05] transition-colors"
                            >
                                Profile Settings
                            </Link>
                            <Link 
                                :href="route('logout')" 
                                method="post" 
                                as="button" 
                                class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Body -->
            <main class="flex-1 p-4 sm:p-8 space-y-6">
                <!-- Page Title -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Products Inventory</h1>
                        <p class="text-slate-400 text-sm font-medium">Manage base catalog items and clothing SKU variants.</p>
                    </div>
                    
                    <Link 
                        :href="route('products.create')" 
                        class="glass-button-primary flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition-all hover:scale-[1.02] active:scale-[0.98] w-full sm:w-auto"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Product
                    </Link>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Metric 1: Total Products -->
                    <div class="glass-card p-5 border-white/5 relative overflow-hidden group hover:border-white/10 transition-all duration-300">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-indigo-500/5 group-hover:scale-125 transition-transform duration-500 blur-xl"></div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Products</span>
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-white tracking-tight">{{ totalProductsCount }}</span>
                            <span class="text-xs text-slate-500 font-medium">items in catalog</span>
                        </div>
                    </div>

                    <!-- Metric 2: Total Stock -->
                    <div class="glass-card p-5 border-white/5 relative overflow-hidden group hover:border-white/10 transition-all duration-300">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-emerald-500/5 group-hover:scale-125 transition-transform duration-500 blur-xl"></div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Stock</span>
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-white tracking-tight">{{ totalStockQuantity }}</span>
                            <span class="text-xs text-slate-500 font-medium">pcs total stock</span>
                        </div>
                    </div>

                    <!-- Metric 3: Low Stock Alerts -->
                    <div class="glass-card p-5 border-white/5 relative overflow-hidden group hover:border-white/10 transition-all duration-300">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-rose-500/5 group-hover:scale-125 transition-transform duration-500 blur-xl"></div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Low Stock Alerts</span>
                            <div class="w-8 h-8 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400" :class="{'bg-rose-500/20 text-rose-300': lowStockCount > 0}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold tracking-tight" :class="[lowStockCount > 0 ? 'text-rose-400' : 'text-white']">{{ lowStockCount }}</span>
                            <span class="text-xs text-slate-500 font-medium">items need restock</span>
                        </div>
                    </div>

                    <!-- Metric 4: Active Ratio -->
                    <div class="glass-card p-5 border-white/5 relative overflow-hidden group hover:border-white/10 transition-all duration-300">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-purple-500/5 group-hover:scale-125 transition-transform duration-500 blur-xl"></div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Catalog</span>
                            <div class="w-8 h-8 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-white tracking-tight">{{ activeRatio }}%</span>
                            <span class="text-xs text-slate-500 font-medium">({{ activeProductsCount }}/{{ totalProductsCount }} active)</span>
                        </div>
                    </div>
                </div>

                <!-- Filters & Search Controls -->
                <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between bg-black/20 backdrop-blur-md border border-white/5 p-4 rounded-2xl">
                    <!-- Category Tabs -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 lg:pb-0 scrollbar-thin">
                        <button 
                            v-for="cat in categoriesList" 
                            :key="cat"
                            @click="selectedCategory = cat"
                            :class="[selectedCategory === cat ? 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30 shadow-[0_0_15px_rgba(99,102,241,0.15)]' : 'bg-transparent text-slate-400 border-transparent hover:text-slate-200 hover:bg-white/[0.02]']"
                            class="px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200 cursor-pointer whitespace-nowrap"
                        >
                            {{ cat }}
                        </button>
                    </div>

                    <!-- Stock & Sort Options -->
                    <div class="flex flex-wrap items-center gap-3.5">
                        <!-- Stock Level Filter -->
                        <div class="flex items-center bg-white/[0.02] border border-white/5 rounded-xl p-1 shrink-0">
                            <button 
                                @click="stockFilter = 'all'"
                                :class="[stockFilter === 'all' ? 'bg-white/[0.06] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200']"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer"
                            >
                                All Stock
                            </button>
                            <button 
                                @click="stockFilter = 'low'"
                                :class="[stockFilter === 'low' ? 'bg-rose-500/15 text-rose-300 border-rose-500/30 shadow-[0_0_15px_rgba(244,63,94,0.15)]' : 'text-slate-400 hover:text-slate-200 border-transparent']"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all duration-200 flex items-center gap-1.5 cursor-pointer"
                            >
                                Low Stock
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            </button>
                        </div>

                        <!-- Sorting Selector -->
                        <div class="relative shrink-0">
                            <select 
                                v-model="sortBy"
                                class="glass-input pl-3 pr-8 py-2 text-xs font-bold rounded-xl bg-slate-955/40 border border-white/5 focus:outline-none cursor-pointer appearance-none text-slate-300 hover:text-white transition-colors"
                            >
                                <option value="name" class="bg-[#0b0f19] text-slate-300">Sort by: Name</option>
                                <option value="price_asc" class="bg-[#0b0f19] text-slate-300">Price: Low to High</option>
                                <option value="price_desc" class="bg-[#0b0f19] text-slate-300">Price: High to Low</option>
                                <option value="stock_asc" class="bg-[#0b0f19] text-slate-300">Stock: Low to High</option>
                                <option value="stock_desc" class="bg-[#0b0f19] text-slate-300">Stock: High to Low</option>
                            </select>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="glass-card overflow-hidden border border-white/5 shadow-[0_24px_60px_-15px_rgba(0,0,0,0.6)]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/[0.06] bg-white/[0.01] text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    <th class="px-6 py-4.5">Product Info</th>
                                    <th class="px-6 py-4.5">Category</th>
                                    <th class="px-6 py-4.5">Base Price</th>
                                    <th class="px-6 py-4.5">Total Stock</th>
                                    <th class="px-6 py-4.5">Variants Detail (Sizes / Colors)</th>
                                    <th class="px-6 py-4.5">Status</th>
                                    <th class="px-6 py-4.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.02]">
                                <tr 
                                    v-for="product in filteredProducts" 
                                    :key="product.id"
                                    class="hover:bg-white/[0.03] hover:translate-x-0.5 text-sm text-slate-200 transition-all duration-200"
                                >
                                    <!-- Product Info Column -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-11 h-11 rounded-2xl overflow-hidden bg-slate-900 border border-white/10 shrink-0 shadow-lg relative group">
                                                <img 
                                                    v-if="getPrimaryImageUrl(product.images)" 
                                                    :src="getPrimaryImageUrl(product.images)" 
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
                                                />
                                                <div v-else class="w-full h-full flex items-center justify-center bg-indigo-500/10 text-indigo-300 font-extrabold text-sm uppercase">
                                                    {{ product.name.charAt(0) }}
                                                </div>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-extrabold text-white text-base leading-tight hover:text-indigo-400 transition-colors cursor-default">{{ product.name }}</span>
                                                <span class="font-mono text-[10px] text-slate-500 mt-1.5 uppercase tracking-wider">{{ product.slug }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Category Column -->
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.6)]"></span>
                                            <span class="font-bold text-slate-300">{{ product.category?.name || 'Unassigned' }}</span>
                                        </div>
                                    </td>

                                    <!-- Base Price Column -->
                                    <td class="px-6 py-5 font-extrabold text-white text-base">
                                        ${{ parseFloat(product.base_price).toFixed(2) }}
                                    </td>

                                    <!-- Total Stock Column -->
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-1.5 min-w-[100px]">
                                            <div class="flex items-center justify-between gap-2">
                                                <span 
                                                    :class="[
                                                        getTotalStock(product.variants) === 0 ? 'text-rose-400 font-extrabold' : 
                                                        getTotalStock(product.variants) <= 10 ? 'text-amber-400 font-extrabold' : 
                                                        'text-emerald-400 font-bold'
                                                    ]"
                                                    class="text-xs"
                                                >
                                                    {{ getTotalStock(product.variants) }} pcs
                                                </span>
                                                <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold">
                                                    {{ getTotalStock(product.variants) === 0 ? 'Out of Stock' : getTotalStock(product.variants) <= 10 ? 'Low Stock' : 'In Stock' }}
                                                </span>
                                            </div>
                                            <!-- Mini progress bar -->
                                            <div class="w-full h-1 bg-white/[0.04] rounded-full overflow-hidden">
                                                <div 
                                                    :class="[
                                                        getTotalStock(product.variants) === 0 ? 'bg-rose-500' : 
                                                        getTotalStock(product.variants) <= 10 ? 'bg-amber-500' : 
                                                        'bg-emerald-500'
                                                    ]"
                                                    class="h-full rounded-full transition-all duration-500" 
                                                    :style="{ width: Math.min(100, (getTotalStock(product.variants) / 100) * 100) + '%' }"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Variants Info Column -->
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-semibold text-slate-400 leading-relaxed">{{ getVariantsSummary(product.variants) }}</span>
                                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">{{ product.variants?.length || 0 }} SKUs total</span>
                                        </div>
                                    </td>

                                    <!-- Status Column -->
                                    <td class="px-6 py-5">
                                        <button 
                                            @click="toggleProductStatus(product.id)"
                                            :class="[
                                                product.is_active 
                                                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 shadow-[0_0_15px_rgba(16,185,129,0.06)] hover:bg-emerald-500/20' 
                                                    : 'bg-white/[0.02] text-slate-400 border-white/5 hover:bg-white/[0.06]'
                                            ]"
                                            class="px-3.5 py-1.5 text-xs font-bold rounded-xl border transition-all duration-200 cursor-pointer select-none active:scale-95 flex items-center gap-1.5"
                                            title="Click to toggle availability"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="[product.is_active ? 'bg-emerald-400 shadow-[0_0_8px_#34d399]' : 'bg-slate-500']"></span>
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>

                                    <!-- Actions Column -->
                                    <td class="px-6 py-5 text-right">
                                        <Link 
                                            :href="route('products.edit', product.id)" 
                                            class="glass-button text-xs py-2 px-4 rounded-xl border border-white/5 hover:border-white/10 hover:bg-white/[0.06] hover:text-white transition-all duration-200 flex items-center gap-1.5 inline-flex"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            <span>Edit</span>
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="filteredProducts.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center gap-2 text-slate-500 py-6">
                                            <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <span class="text-sm font-semibold">{{ products.length === 0 ? 'No products in catalog yet. Click "Add New Product" to populate items.' : 'No products matching your search or filters.' }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
