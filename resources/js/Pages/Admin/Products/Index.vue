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
    return variants.reduce((total, variant) => total + parseInt(variant.stock_quantity), 0);
};

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
</script>

<template>
    <Head title="Products Inventory - ESTY" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Left Sidebar -->
        <AdminSidebar active="products" />

        <!-- Main Content Area -->
        <div class="flex-1 pl-64 flex flex-col min-h-screen">
            
            <!-- Top Navigation -->
            <header class="h-16 bg-white/[0.02] backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-8 sticky top-0 z-10">
                <div class="w-80 relative">
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
            <main class="flex-1 p-8 space-y-6">
                <!-- Page Title -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Products Inventory</h1>
                        <p class="text-slate-400 text-sm font-medium">Manage base catalog items and clothing SKU variants.</p>
                    </div>
                    
                    <Link 
                        :href="route('products.create')" 
                        class="glass-button-primary flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Product
                    </Link>
                </div>

                <!-- Filters Bar -->
                <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between bg-white/[0.02] border border-white/[0.04] p-4 rounded-2xl">
                    <!-- Category Tabs -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                        <button 
                            v-for="cat in categoriesList" 
                            :key="cat"
                            @click="selectedCategory = cat"
                            :class="[selectedCategory === cat ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-transparent text-slate-400 border-transparent hover:text-slate-200']"
                            class="px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer"
                        >
                            {{ cat }}
                        </button>
                    </div>

                    <!-- Stock & Sort Options -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Stock Level Filter -->
                        <div class="flex items-center bg-white/[0.03] border border-white/[0.06] rounded-xl p-1 shrink-0">
                            <button 
                                @click="stockFilter = 'all'"
                                :class="[stockFilter === 'all' ? 'bg-white/[0.05] text-white' : 'text-slate-400 hover:text-slate-200']"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                            >
                                All Stock
                            </button>
                            <button 
                                @click="stockFilter = 'low'"
                                :class="[stockFilter === 'low' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'text-slate-400 hover:text-slate-200']"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold border border-transparent transition-all flex items-center gap-1 cursor-pointer"
                            >
                                Low Stock
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            </button>
                        </div>

                        <!-- Sorting Selector -->
                        <select 
                            v-model="sortBy"
                            class="glass-input py-2 px-3 text-xs font-semibold rounded-xl max-w-[160px] bg-slate-900/60 border border-white/[0.08] focus:outline-none cursor-pointer"
                        >
                            <option value="name" class="bg-[#0f172a] text-slate-300 font-semibold">Sort by: Name</option>
                            <option value="price_asc" class="bg-[#0f172a] text-slate-300 font-semibold">Price: Low to High</option>
                            <option value="price_desc" class="bg-[#0f172a] text-slate-300 font-semibold">Price: High to Low</option>
                            <option value="stock_asc" class="bg-[#0f172a] text-slate-300 font-semibold">Stock: Low to High</option>
                            <option value="stock_desc" class="bg-[#0f172a] text-slate-300 font-semibold">Stock: High to Low</option>
                        </select>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="glass-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/[0.04] text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    <th class="px-6 py-4">Product Info</th>
                                    <th class="px-6 py-4">Category</th>
                                    <th class="px-6 py-4">Base Price</th>
                                    <th class="px-6 py-4">Total Stock</th>
                                    <th class="px-6 py-4">Variants Detail (Sizes / Colors)</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.02]">
                                <tr 
                                    v-for="product in filteredProducts" 
                                    :key="product.id"
                                    class="hover:bg-white/[0.02] text-sm text-slate-200 transition-colors duration-150"
                                >
                                    <!-- Product Info Column -->
                                    <td class="px-6 py-4.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-900 border border-white/10 shrink-0 shadow-sm">
                                                <img 
                                                    v-if="getPrimaryImageUrl(product.images)" 
                                                    :src="getPrimaryImageUrl(product.images)" 
                                                    class="w-full h-full object-cover" 
                                                />
                                                <div v-else class="w-full h-full flex items-center justify-center bg-indigo-500/10 text-indigo-400 font-bold text-xs uppercase">
                                                    {{ product.name.charAt(0) }}
                                                </div>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-white text-base leading-tight">{{ product.name }}</span>
                                                <span class="font-mono text-xs text-slate-500 mt-1">{{ product.slug }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Category Column -->
                                    <td class="px-6 py-4.5">
                                        <span class="px-2.5 py-1 text-xs font-semibold bg-white/[0.04] border border-white/[0.06] rounded-full text-slate-300">
                                            {{ product.category?.name || 'N/A' }}
                                        </span>
                                    </td>

                                    <!-- Base Price Column -->
                                    <td class="px-6 py-4.5 font-bold text-white">
                                        ${{ parseFloat(product.base_price).toFixed(2) }}
                                    </td>

                                    <!-- Total Stock Column -->
                                    <td class="px-6 py-4.5">
                                        <span 
                                            :class="[getTotalStock(product.variants) > 10 ? 'text-slate-300' : 'text-rose-400 font-bold']"
                                            class="text-sm"
                                        >
                                            {{ getTotalStock(product.variants) }} pcs
                                        </span>
                                    </td>

                                    <!-- Variants Info Column -->
                                    <td class="px-6 py-4.5">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="text-xs text-slate-400">{{ getVariantsSummary(product.variants) }}</span>
                                            <span class="text-[10px] text-slate-500 font-semibold">{{ product.variants?.length || 0 }} SKU variants total</span>
                                        </div>
                                    </td>

                                    <!-- Status Column -->
                                    <td class="px-6 py-4.5">
                                        <button 
                                            @click="toggleProductStatus(product.id)"
                                            :class="[
                                                product.is_active 
                                                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' 
                                                    : 'bg-rose-500/10 text-rose-400 border-rose-500/20 hover:bg-rose-500/20'
                                            ]"
                                            class="px-3 py-1 text-xs font-semibold rounded-full border transition-all cursor-pointer select-none active:scale-95"
                                            title="Click to toggle product availability"
                                        >
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>

                                    <!-- Actions Column -->
                                    <td class="px-6 py-4.5 text-right">
                                        <Link 
                                            :href="route('products.edit', product.id)" 
                                            class="glass-button text-xs py-1.5 px-3.5 rounded-full hover:bg-white/[0.08]"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="filteredProducts.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                        {{ products.length === 0 ? 'No products in catalog yet. Click "Add New Product" to populate items.' : 'No products matching your search or filters.' }}
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
