<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const { props } = usePage();
const userName = ref(props.auth.user?.name || 'Admin');
const userEmail = ref(props.auth.user?.email || 'admin@verone.com');

defineProps({
    products: {
        type: Array,
        required: true
    }
});

const showProfileDropdown = ref(false);

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
</script>

<template>
    <Head title="Products Inventory - VÉRONE" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Left Sidebar -->
        <aside class="w-64 fixed inset-y-0 left-0 bg-black/[0.15] backdrop-blur-3xl border-r border-white/[0.06] flex flex-col z-20">
            <div class="h-16 flex items-center px-6 border-b border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-md shadow-indigo-500/20">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4a3 3 0 00-3 3v1h6V7a3 3 0 00-3-3zM3 19a2 2 0 002 2h14a2 2 0 002-2M5 11h14l1 8H4l1-8z" />
                        </svg>
                    </div>
                    <span class="text-white font-bold tracking-tight text-base uppercase">VÉRONE <span class="text-xs text-indigo-400 font-medium tracking-normal lowercase ml-1">admin</span></span>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2">
                <Link 
                    :href="route('dashboard')"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border border-transparent text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    Dashboard
                </Link>

                <Link 
                    :href="route('products.index')"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border bg-white/[0.08] text-white shadow-inner border-white/10 transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Products
                </Link>

                <a 
                    href="#" 
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border border-transparent text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Orders
                </a>

                <a 
                    href="#" 
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border border-transparent text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
            </nav>

            <div class="p-4 border-t border-white/[0.06] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center font-bold text-white">
                        {{ userName.charAt(0) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-white leading-tight">{{ userName }}</span>
                        <span class="text-[10px] text-slate-400 font-medium truncate max-w-[120px]">{{ userEmail }}</span>
                    </div>
                </div>
            </div>
        </aside>

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
                                    v-for="product in products" 
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
                                        <span 
                                            :class="[product.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20']"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full border"
                                        >
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </span>
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
                                <tr v-if="products.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                        No products in catalog yet. Click "Add New Product" to populate items.
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
