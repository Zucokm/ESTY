<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const props = defineProps({
    inventory: Array
});
</script>

<template>
    <Head title="Inventory & Low Stock - Admin" />

    <div class="min-h-screen bg-[#080b11] text-slate-100 flex relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Ambient Shifts -->
        <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>
        
        <!-- Left Sidebar -->
        <AdminSidebar active="inventory" />

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0 md:ml-64 flex flex-col min-h-screen relative z-10">
            
            <!-- Top Navigation -->
            <header class="h-16 bg-white/[0.02] backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-4 sm:px-8 sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <button @click="() => window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'))" class="md:hidden p-2 -ml-2 text-slate-400 hover:text-white transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-sm font-bold text-white tracking-wide uppercase">Inventory</h2>
                </div>
            </header>

            <!-- Main Body -->
            <main class="flex-1 min-w-0 p-4 sm:p-8 space-y-6">
                <!-- Page Title -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight">Advanced Inventory</h1>
                        <p class="text-slate-400 font-medium mt-1">Track low stock items and variant availability across your catalog.</p>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="glass-card overflow-hidden border border-white/5 shadow-[0_24px_60px_-15px_rgba(0,0,0,0.6)]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="border-b border-white/[0.06] bg-white/[0.01] text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    <th class="whitespace-nowrap px-6 py-4.5">Product Name</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Size / Color</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">SKU / ID</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Current Stock</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Status</th>
                                    <th class="whitespace-nowrap px-6 py-4.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.06]">
                                <tr 
                                    v-for="item in inventory" 
                                    :key="item.id"
                                    class="hover:bg-white/[0.03] text-sm text-slate-200 transition-colors duration-200"
                                >
                                    <td class="whitespace-nowrap px-6 py-5 font-bold text-white">
                                        {{ item.product?.name || 'Unknown Product' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div class="flex gap-2">
                                            <span class="px-2 py-1 bg-slate-800 rounded text-xs font-semibold text-slate-300">{{ item.size }}</span>
                                            <span class="px-2 py-1 bg-slate-800 rounded text-xs font-semibold text-slate-300">{{ item.color }}</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 font-mono text-xs text-slate-400">
                                        {{ item.sku || `VAR-${item.id}` }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 font-extrabold text-base" :class="item.stock <= 5 ? 'text-rose-400' : (item.stock <= 20 ? 'text-amber-400' : 'text-emerald-400')">
                                        {{ item.stock }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <span 
                                            class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider rounded-full"
                                            :class="item.stock === 0 ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : (item.stock <= 5 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20')"
                                        >
                                            {{ item.stock === 0 ? 'Out of Stock' : (item.stock <= 5 ? 'Low Stock' : 'In Stock') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 text-right">
                                        <Link 
                                            :href="route('admin.products.edit', item.product_id)"
                                            class="px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200 bg-indigo-500/10 border-indigo-500/20 text-indigo-400 hover:bg-indigo-500/20"
                                        >
                                            Manage Product
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!inventory.length">
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 font-medium">
                                        No inventory variants found.
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
