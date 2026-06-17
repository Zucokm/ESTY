<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const { props: pageProps } = usePage();
const userName = ref(pageProps.auth.user?.name || 'Admin');
const userEmail = ref(pageProps.auth.user?.email || 'admin@verone.com');

defineProps({
    orders: {
        type: Array,
        required: true
    }
});

// Menu toggle states
const activeTab = ref('dashboard');
const showProfileDropdown = ref(false);

// Mock Stats Data (calculated statically or from props in production)
const stats = ref([
    {
        title: 'Total Revenue',
        value: '$54,920',
        change: '+14.2%',
        iconPath: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        title: 'Active Orders',
        value: '184',
        change: '+8.6%',
        iconPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'
    },
    {
        title: 'Low Stock Alerts',
        value: '12',
        change: '-4.1%',
        iconPath: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
    },
    {
        title: 'Total Products',
        value: '1,240',
        change: '+3.5%',
        iconPath: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
    }
]);

const getStatusClass = (status) => {
    switch (status.toLowerCase()) {
        case 'pending':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
        case 'processing':
            return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        case 'completed':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        case 'cancelled':
            return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
        default:
            return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    }
};

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Admin Dashboard - ESTY" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Left Sidebar (macOS Dark Mode Style) -->
        <aside class="w-64 fixed inset-y-0 left-0 bg-black/[0.15] backdrop-blur-3xl border-r border-white/[0.06] flex flex-col z-20">
            <!-- Sidebar Brand Header -->
            <div class="h-16 flex items-center px-6 border-b border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-md shadow-indigo-500/20">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4a3 3 0 00-3 3v1h6V7a3 3 0 00-3-3zM3 19a2 2 0 002 2h14a2 2 0 002-2M5 11h14l1 8H4l1-8z" />
                        </svg>
                    </div>
                    <span class="text-white font-bold tracking-tight text-base uppercase">ESTY <span class="text-xs text-indigo-400 font-medium tracking-normal lowercase ml-1">admin</span></span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <Link 
                    :href="route('dashboard')"
                    :class="[activeTab === 'dashboard' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    Dashboard
                </Link>

                <Link 
                    :href="route('products.index')"
                    :class="[activeTab === 'products' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Products
                </Link>

                <Link 
                    :href="route('admin.orders.index')"
                    :class="[activeTab === 'orders' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Orders
                </Link>

                <a 
                    href="#" 
                    @click.prevent="activeTab = 'settings'"
                    :class="[activeTab === 'settings' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
            </nav>

            <!-- Bottom User Profile Section -->
            <div class="p-4 border-t border-white/[0.06] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center font-bold text-white shadow-md">
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
            
            <!-- Top Navigation Bar -->
            <header class="h-16 bg-white/[0.02] backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-8 sticky top-0 z-10">
                <!-- Search bar -->
                <div class="w-80 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        placeholder="Search garments, orders, variant details..." 
                        class="glass-input pl-9 py-2 text-sm rounded-full"
                    />
                </div>

                <!-- Admin Action items -->
                <div class="flex items-center gap-4">
                    <button class="relative w-8 h-8 rounded-full flex items-center justify-center text-slate-300 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1 right-1.5 w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    </button>

                    <!-- Profile Dropdown -->
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
                        
                        <!-- Dropdown Content -->
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

            <!-- Main Scrollable Body Area -->
            <main class="flex-1 p-8 space-y-8">
                <!-- Dashboard Welcome Title -->
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Overview</h1>
                    <p class="text-slate-400 text-sm font-medium">Variant inventories, live client sales, and tracking monitors.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="(stat, idx) in stats" 
                        :key="idx" 
                        class="glass-card p-6 flex items-center justify-between"
                    >
                        <div>
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">{{ stat.title }}</span>
                            <h3 class="text-2xl font-extrabold text-white mt-1.5">{{ stat.value }}</h3>
                            <span class="text-[11px] font-bold mt-1 inline-block" :class="[stat.change.startsWith('+') ? 'text-emerald-400' : 'text-rose-400']">
                                {{ stat.change }} <span class="text-slate-500 font-normal">from last month</span>
                            </span>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-white/[0.04] border border-white/[0.06] flex items-center justify-center text-slate-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.iconPath" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Data Table: Recent Orders (Real Database Data) -->
                <div class="glass-card overflow-hidden">
                    <!-- Table Header -->
                    <div class="px-6 py-5 border-b border-white/[0.06] flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-white tracking-tight">Recent Orders</h2>
                            <p class="text-slate-400 text-xs font-medium">Garment sales transactions updated in real-time.</p>
                        </div>
                        <button class="glass-button text-xs py-1.5 px-3 rounded-full hover:bg-white/[0.08]">
                            View All
                        </button>
                    </div>

                    <!-- Table Data -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/[0.04] text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    <th class="px-6 py-4">Order ID</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Shipping Details</th>
                                    <th class="px-6 py-4">Phone Number</th>
                                    <th class="px-6 py-4">Total Amount</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.02]">
                                <tr 
                                    v-for="(order, oIdx) in orders" 
                                    :key="order.id"
                                    class="hover:bg-white/[0.02] text-sm text-slate-200 transition-colors duration-150"
                                >
                                    <td class="px-6 py-4.5 font-mono text-xs text-slate-400">#VR-{{ order.id }}</td>
                                    <td class="px-6 py-4.5 font-semibold text-white">{{ order.user?.name || 'Guest' }}</td>
                                    <td class="px-6 py-4.5 text-xs text-slate-400 max-w-[200px] truncate">{{ order.shipping_address }}</td>
                                    <td class="px-6 py-4.5 font-mono text-xs text-slate-400">{{ order.phone }}</td>
                                    <td class="px-6 py-4.5 font-bold text-white">${{ parseFloat(order.total_amount).toFixed(2) }}</td>
                                    <td class="px-6 py-4.5 text-xs text-slate-400">{{ formatDate(order.created_at) }}</td>
                                    <td class="px-6 py-4.5">
                                        <span 
                                            :class="getStatusClass(order.status)"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full border"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="orders.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                        No recent checkout orders recorded in database.
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
