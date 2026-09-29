<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import { orderApi } from '@/Services/api';

const { props: pageProps } = usePage();
const userName = ref(pageProps.auth.user?.name || 'Admin');
const userEmail = ref(pageProps.auth.user?.email || 'admin@verone.com');

const props = defineProps({
    orders: {
        type: Array,
        required: true
    }
});

const showProfileDropdown = ref(false);

// Search and Filter States
const searchQuery = ref('');
const selectedStatus = ref('All');
const sortBy = ref('date_desc'); // 'date_desc', 'date_asc', 'amount_desc', 'amount_asc'

// Accordion Expandable Order ID
const expandedOrderId = ref(null);

const toggleExpandOrder = (orderId) => {
    expandedOrderId.value = expandedOrderId.value === orderId ? null : orderId;
};

// Executive Summary Stats calculated from catalog orders
const statsSummary = computed(() => {
    const active = props.orders.filter(o => o.status !== 'cancelled');
    const revenue = active.reduce((acc, o) => acc + parseFloat(o.total_amount), 0);
    const pending = props.orders.filter(o => ['pending', 'processing', 'packing', 'shipping', 'delivered'].includes(o.status.toLowerCase())).length;
    const completed = props.orders.filter(o => o.status === 'completed').length;
    
    return {
        totalOrders: props.orders.length,
        revenue: revenue.toLocaleString() + ' Ks',
        pending,
        completed
    };
});

// Reactively filter and sort orders
const filteredOrders = computed(() => {
    let result = [...props.orders];

    // 1. Search filter
    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase().trim();
        result = result.filter(o => {
            const idMatch = `#vr-${o.id}`.includes(query) || o.id.toString() === query;
            const userMatch = o.user?.name?.toLowerCase().includes(query);
            const addressMatch = o.shipping_address?.toLowerCase().includes(query);
            const phoneMatch = o.phone?.toLowerCase().includes(query);
            const itemMatch = o.items?.some(i => i.product?.name?.toLowerCase().includes(query));
            return idMatch || userMatch || addressMatch || phoneMatch || itemMatch;
        });
    }

    // 2. Status filter
    if (selectedStatus.value !== 'All') {
        result = result.filter(o => o.status.toLowerCase() === selectedStatus.value.toLowerCase());
    }

    // 3. Sorting
    result.sort((a, b) => {
        if (sortBy.value === 'date_desc') {
            return new Date(b.created_at) - new Date(a.created_at);
        } else if (sortBy.value === 'date_asc') {
            return new Date(a.created_at) - new Date(b.created_at);
        } else if (sortBy.value === 'amount_desc') {
            return parseFloat(b.total_amount) - parseFloat(a.total_amount);
        } else if (sortBy.value === 'amount_asc') {
            return parseFloat(a.total_amount) - parseFloat(b.total_amount);
        }
        return 0;
    });

    return result;
});

const updateOrderStatus = (orderId, newStatus) => {
    orderApi.updateStatus(orderId, {
        status: newStatus
    }, {
        preserveScroll: true
    });
};

const getStatusClass = (status) => {
    switch (status.toLowerCase()) {
        case 'pending':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
        case 'processing':
            return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        case 'packing':
            return 'bg-pink-500/10 text-pink-400 border-pink-500/20';
        case 'shipping':
            return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
        case 'delivered':
            return 'bg-teal-500/10 text-teal-400 border-teal-500/20';
        case 'completed':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        case 'returned':
            return 'bg-orange-500/10 text-orange-400 border-orange-500/20';
        case 'refunded':
            return 'bg-slate-500/10 text-slate-400 border-slate-500/20';

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

const getPrimaryImageUrl = (images) => {
    if (!images || images.length === 0) return null;
    const primary = images.find(img => img.is_primary === 1 || img.is_primary === true);
    return primary ? primary.image_path : images[0].image_path;
};

const toggleSidebar = () => {
    window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'));
};
</script>

<template>
    <Head title="Orders Management - ESTY" />

    <div class="min-h-screen bg-[#080b11] text-slate-100 flex relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Ambient Shifts -->
        <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>

        <!-- Left Sidebar -->
        <AdminSidebar active="orders" />

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0 md:ml-64 flex flex-col min-h-screen relative z-10">
            
            <!-- Top Navigation -->
            <header class="h-16 bg-white/[0.02] backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-4 sm:px-8 sticky top-0 z-10">
                <!-- Hamburger Menu Button -->
                <button 
                    @click="toggleSidebar" 
                    class="md:hidden p-2 text-slate-400 hover:text-white transition-colors focus:outline-none"
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
                        placeholder="Search orders, shipping details, clients..." 
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
            <main class="flex-1 min-w-0 p-4 sm:p-8 space-y-6">
                <!-- Page Title -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Orders Management</h1>
                        <p class="text-slate-400 text-sm font-medium">Fulfill client purchases and modify order statuses.</p>
                    </div>
                    <a 
                        :href="route('admin.reports.export')" 
                        target="_blank"
                        class="px-5 py-2.5 bg-emerald-500/10 border border-emerald-500/30 hover:bg-emerald-500/20 text-emerald-400 font-bold rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.1)] transition-all flex items-center justify-center gap-2 whitespace-nowrap"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Export CSV
                    </a>
                </div>

                <!-- Stats Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Metric 1: Total Sales -->
                    <div class="glass-card p-5 border-white/5 relative overflow-hidden group hover:border-white/10 transition-all duration-300">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-indigo-500/5 group-hover:scale-125 transition-transform duration-500 blur-xl"></div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Sales (Orders)</span>
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-white tracking-tight">{{ statsSummary.totalOrders }}</span>
                            <span class="text-xs text-slate-500 font-medium">orders total</span>
                        </div>
                    </div>

                    <!-- Metric 2: Accumulated Revenue -->
                    <div class="glass-card p-5 border-white/5 relative overflow-hidden group hover:border-white/10 transition-all duration-300">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-indigo-500/5 group-hover:scale-125 transition-transform duration-500 blur-xl"></div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Accumulated Revenue</span>
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-white tracking-tight">{{ statsSummary.revenue }}</span>
                        </div>
                    </div>

                    <!-- Metric 3: Pending Fulfillments -->
                    <div class="glass-card p-5 border-white/5 relative overflow-hidden group hover:border-white/10 transition-all duration-300">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-amber-500/5 group-hover:scale-125 transition-transform duration-500 blur-xl"></div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Fulfillments</span>
                            <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-white tracking-tight">{{ statsSummary.pending }}</span>
                            <span class="text-xs text-slate-500 font-medium">active pipeline</span>
                        </div>
                    </div>

                    <!-- Metric 4: Completed Deliveries -->
                    <div class="glass-card p-5 border-white/5 relative overflow-hidden group hover:border-white/10 transition-all duration-300">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-emerald-500/5 group-hover:scale-125 transition-transform duration-500 blur-xl"></div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Completed Deliveries</span>
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-white tracking-tight">{{ statsSummary.completed }}</span>
                            <span class="text-xs text-slate-500 font-medium">delivered successfully</span>
                        </div>
                    </div>
                </div>

                <!-- Filters Panel -->
                <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between bg-black/20 backdrop-blur-md border border-white/5 p-4 rounded-2xl">
                    <!-- Status Filter Tabs -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 lg:pb-0 scrollbar-thin">
                        <button 
                            v-for="status in ['All', 'Pending', 'Processing', 'Packing', 'Shipping', 'Delivered', 'Completed', 'Cancelled']" 
                            :key="status"
                            @click="selectedStatus = status"
                            :class="[
                                selectedStatus === status 
                                    ? 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30 shadow-[0_0_15px_rgba(99,102,241,0.15)]' 
                                    : 'bg-transparent text-slate-400 border-transparent hover:text-slate-200 hover:bg-white/[0.02]'
                            ]"
                            class="px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200 cursor-pointer whitespace-nowrap"
                        >
                            {{ status }}
                        </button>
                    </div>

                    <!-- Sorting options dropdown -->
                    <div class="relative shrink-0">
                        <select 
                            v-model="sortBy"
                            class="glass-input pl-3 pr-8 py-2 text-xs font-bold rounded-xl bg-slate-955/40 border border-white/5 focus:outline-none cursor-pointer appearance-none text-slate-300 hover:text-white transition-colors"
                        >
                            <option value="date_desc" class="bg-[#0b0f19] text-slate-300">Placed: Newest First</option>
                            <option value="date_asc" class="bg-[#0b0f19] text-slate-300">Placed: Oldest First</option>
                            <option value="amount_desc" class="bg-[#0b0f19] text-slate-300">Total: High to Low</option>
                            <option value="amount_asc" class="bg-[#0b0f19] text-slate-300">Total: Low to High</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="glass-card overflow-hidden border border-white/5 shadow-[0_24px_60px_-15px_rgba(0,0,0,0.6)]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="border-b border-white/[0.06] bg-white/[0.01] text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    <th class="whitespace-nowrap px-6 py-4.5">Order Details</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Client Name</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Shipping Info</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Items Summary</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Total Amount</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Date Placed</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Status</th>
                                    <th class="whitespace-nowrap px-6 py-4.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.02]">
                                <template v-for="order in filteredOrders" :key="order.id">
                                    <tr 
                                        @click="toggleExpandOrder(order.id)"
                                        :class="[expandedOrderId === order.id ? 'bg-indigo-500/[0.04] border-l border-indigo-500' : 'hover:bg-white/[0.03] hover:translate-x-0.5']"
                                        class="text-sm text-slate-200 transition-all duration-200 cursor-pointer"
                                    >
                                    <!-- Order ID -->
                                    <td class="whitespace-nowrap px-6 py-5 font-mono text-xs font-bold text-indigo-400">#VR-{{ order.id }}</td>
                                    
                                    <!-- Client Name -->
                                    <td class="whitespace-nowrap px-6 py-5 font-extrabold text-white">
                                        {{ order.user?.name || 'Guest Customer' }}
                                    </td>

                                    <!-- Shipping Info -->
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div class="flex flex-col text-xs text-slate-400 max-w-[180px] truncate" :title="order.shipping_address">
                                            <span v-if="order.township" class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-0.5">{{ order.township }}</span>

                                            <span class="truncate font-semibold text-slate-300">{{ order.shipping_address }}</span>
                                            <span class="mt-1.5 font-mono text-[10px] text-slate-500">{{ order.phone }}</span>
                                        </div>
                                    </td>

                                    <!-- Items Summary -->
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div class="flex flex-col gap-1">
                                            <span 
                                                v-for="item in order.items" 
                                                :key="item.id" 
                                                class="text-xs text-slate-300 font-semibold"
                                            >
                                                {{ item.product?.name }} <span class="text-slate-500 font-bold">x{{ item.quantity }}</span>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Total Price Column -->
                                    <td class="whitespace-nowrap px-6 py-5 font-extrabold text-white text-base">
                                        {{ Number(order.total_amount).toLocaleString() }} Ks
                                    </td>

                                    <!-- Date Column -->
                                    <td class="whitespace-nowrap px-6 py-5 text-xs font-semibold text-slate-400">
                                        {{ formatDate(order.created_at) }}
                                    </td>

                                    <!-- Status Column -->
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div class="relative inline-block" @click.stop>
                                            <select 
                                                :value="order.status"
                                                @change="updateOrderStatus(order.id, $event.target.value)"
                                                :class="getStatusClass(order.status)"
                                                class="pl-3.5 pr-8 py-1.5 text-xs font-extrabold rounded-xl border bg-slate-950/60 focus:outline-none cursor-pointer appearance-none select-none transition-all duration-200 active:scale-95"
                                            >
                                                <option value="pending" class="bg-[#0b0f19] text-amber-400 font-bold">pending</option>
                                                <option value="processing" class="bg-[#0b0f19] text-blue-400 font-bold">processing</option>
                                                <option value="packing" class="bg-[#0b0f19] text-pink-400 font-bold">packing</option>
                                                <option value="shipping" class="bg-[#0b0f19] text-indigo-400 font-bold">shipping</option>
                                                <option value="delivered" class="bg-[#0b0f19] text-teal-400 font-bold">delivered</option>
                                                <option value="completed" class="bg-[#0b0f19] text-emerald-400 font-bold">completed</option>
                                                <option value="cancelled" class="bg-[#0b0f19] text-rose-400 font-bold">cancelled</option>
                                                <option value="returned" class="bg-[#0b0f19] text-orange-400 font-bold">returned</option>
                                                <option value="refunded" class="bg-[#0b0f19] text-slate-400 font-bold">refunded</option>

                                            </select>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Actions Column -->
                                    <td class="whitespace-nowrap px-6 py-5 text-right" @click.stop>
                                        <button 
                                            @click="toggleExpandOrder(order.id)"
                                            class="glass-button text-xs py-2 px-4 rounded-xl border border-white/5 hover:border-white/10 hover:bg-white/[0.06] inline-flex items-center gap-1.5 transition-all duration-200"
                                        >
                                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{'rotate-180': expandedOrderId === order.id}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            <span>{{ expandedOrderId === order.id ? 'Collapse' : 'Details' }}</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Expandable Accordion Row Details -->
                                <tr v-if="expandedOrderId === order.id" class="bg-white/[0.01] border-b border-white/[0.04]">
                                    <td colspan="8" class="p-6 bg-slate-950/[0.15]">
                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in">
                                            <!-- Left side: Items Invoice -->
                                            <div class="lg:col-span-2 space-y-4">
                                                <h4 class="text-white font-extrabold text-sm tracking-tight border-b border-white/[0.06] pb-2.5 flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                    <span>Order Items ({{ order.items?.length || 0 }})</span>
                                                </h4>
                                                
                                                <div class="divide-y divide-white/[0.03] space-y-3.5">
                                                    <div 
                                                        v-for="item in order.items" 
                                                        :key="item.id"
                                                        class="flex gap-4 pt-3.5 first:pt-0"
                                                    >
                                                        <!-- Image -->
                                                        <div class="w-12 h-16 rounded-xl overflow-hidden bg-slate-900 border border-white/10 shrink-0 shadow-md">
                                                            <img 
                                                                v-if="getPrimaryImageUrl(item.product?.images)" 
                                                                :src="getPrimaryImageUrl(item.product.images)" 
                                                                class="w-full h-full object-cover" 
                                                            />
                                                            <div v-else class="w-full h-full flex items-center justify-center bg-indigo-500/10 text-indigo-300 text-xs font-extrabold uppercase">
                                                                {{ item.product?.name ? item.product.name.charAt(0) : '' }}
                                                            </div>
                                                        </div>
                                                        <!-- Details -->
                                                        <div class="flex-1 flex justify-between items-start">
                                                            <div class="space-y-1">
                                                                <span class="font-extrabold text-white text-sm block leading-tight">{{ item.product?.name }}</span>
                                                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mt-1">
                                                                    Size: <span class="text-slate-300 font-semibold bg-white/[0.04] px-1.5 py-0.5 rounded border border-white/5">{{ item.variant?.size }}</span> &nbsp;|&nbsp; 
                                                                    Color: <span class="text-slate-300 font-semibold bg-white/[0.04] px-1.5 py-0.5 rounded border border-white/5">{{ item.variant?.color }}</span>
                                                                </span>
                                                            </div>
                                                            <div class="text-right">
                                                                <span class="font-mono text-xs text-slate-400 block">{{ parseFloat(item.price).toLocaleString() }} Ks x {{ item.quantity }}</span>
                                                                <span class="font-extrabold text-white text-sm block mt-1">{{ (parseFloat(item.price) * item.quantity).toLocaleString() }} Ks</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Right side: Shipping card & Actions -->
                                            <div class="space-y-6" @click.stop>
                                                <div class="bg-white/[0.02] border border-white/[0.06] p-5 rounded-2xl space-y-4 shadow-inner">
                                                    <h4 class="text-white font-bold text-xs uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <span>Shipping Details</span>
                                                    </h4>
                                                    
                                                    <!-- Customer -->
                                                    <div class="flex flex-col text-xs gap-1 border-b border-white/[0.03] pb-2.5">
                                                        <span class="text-slate-500 font-bold uppercase tracking-wider">Client Name</span>
                                                        <span class="text-slate-200 font-extrabold text-sm">{{ order.user?.name || 'Guest Customer' }}</span>
                                                    </div>
                                                    
                                                    <!-- Address -->
                                                    <div class="flex flex-col text-xs gap-1 border-b border-white/[0.03] pb-2.5">
                                                        <span class="text-slate-500 font-bold uppercase tracking-wider">Shipping Address</span>
                                                        <span class="text-slate-200 font-medium leading-relaxed">{{ order.shipping_address }}</span>
                                                    </div>

                                                    <!-- Phone -->
                                                    <div class="flex flex-col text-xs gap-1">
                                                        <span class="text-slate-500 font-bold uppercase tracking-wider">Contact Phone</span>
                                                        <span class="text-slate-200 font-mono font-bold text-sm text-indigo-300">{{ order.phone }}</span>
                                                    </div>
                                                </div>

                                                <!-- Quick state controls -->
                                                <div class="flex flex-col gap-2.5">
                                                    <span class="text-slate-500 text-[10px] font-bold uppercase tracking-widest ml-1">Fulfillment Actions</span>
                                                    <div class="flex flex-wrap gap-2.5">
                                                        <button 
                                                            v-if="order.status === 'pending'"
                                                            @click="updateOrderStatus(order.id, 'processing')"
                                                            class="flex-1 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition-all active:scale-[0.97] cursor-pointer shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40"
                                                        >
                                                            Start Processing
                                                        </button>
                                                        <button 
                                                            v-if="order.status === 'processing'"
                                                            @click="updateOrderStatus(order.id, 'packing')"
                                                            class="flex-1 py-3 bg-pink-500 hover:bg-pink-600 text-white rounded-xl text-xs font-bold transition-all active:scale-[0.97] cursor-pointer shadow-lg shadow-pink-500/20 hover:shadow-pink-500/40"
                                                        >
                                                            Start Packing
                                                        </button>
                                                        <button 
                                                            v-if="order.status === 'packing'"
                                                            @click="updateOrderStatus(order.id, 'shipping')"
                                                            class="flex-1 py-3 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-xs font-bold transition-all active:scale-[0.97] cursor-pointer shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40"
                                                        >
                                                            Ship Product
                                                        </button>
                                                        <button 
                                                            v-if="order.status === 'shipping'"
                                                            @click="updateOrderStatus(order.id, 'delivered')"
                                                            class="flex-1 py-3 bg-teal-500 hover:bg-teal-600 text-white rounded-xl text-xs font-bold transition-all active:scale-[0.97] cursor-pointer shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40"
                                                        >
                                                            Mark as Delivered
                                                        </button>
                                                        <button 
                                                            v-if="order.status === 'delivered'"
                                                            @click="updateOrderStatus(order.id, 'completed')"
                                                            class="flex-1 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition-all active:scale-[0.97] cursor-pointer shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40"
                                                        >
                                                            Complete Order
                                                        </button>
                                                        <button 
                                                            v-if="['pending', 'processing', 'packing', 'shipping'].includes(order.status)"
                                                            @click="updateOrderStatus(order.id, 'cancelled')"
                                                            class="py-3 px-4 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white border border-rose-500/20 rounded-xl text-xs font-bold transition-all active:scale-[0.97] cursor-pointer"
                                                        >
                                                            Cancel Order
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                                <tr v-if="filteredOrders.length === 0">
                                    <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                                        {{ orders.length === 0 ? 'No customer orders found in the database.' : 'No orders matching your search or filters.' }}
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
