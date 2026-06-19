<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';

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
        revenue: '$' + revenue.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
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
    router.put(route('admin.orders.updateStatus', orderId), {
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
</script>

<template>
    <Head title="Orders Management - ESTY" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Left Sidebar -->
        <AdminSidebar active="orders" />

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
            <main class="flex-1 p-8 space-y-6">
                <!-- Page Title -->
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Orders Management</h1>
                    <p class="text-slate-400 text-sm font-medium">Fulfill client purchases and modify order statuses.</p>
                </div>

                <!-- Stats Summary -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="glass-card p-4 flex flex-col">
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Total Sales (Orders)</span>
                        <h3 class="text-xl font-extrabold text-white mt-1">{{ statsSummary.totalOrders }}</h3>
                    </div>
                    <div class="glass-card p-4 flex flex-col">
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Accumulated Revenue</span>
                        <h3 class="text-xl font-extrabold text-indigo-400 mt-1">{{ statsSummary.revenue }}</h3>
                    </div>
                    <div class="glass-card p-4 flex flex-col">
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Pending Fulfillments</span>
                        <h3 class="text-xl font-extrabold text-amber-400 mt-1">{{ statsSummary.pending }}</h3>
                    </div>
                    <div class="glass-card p-4 flex flex-col">
                        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Completed Deliveries</span>
                        <h3 class="text-xl font-extrabold text-emerald-400 mt-1">{{ statsSummary.completed }}</h3>
                    </div>
                </div>

                <!-- Filters Panel -->
                <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between bg-white/[0.02] border border-white/[0.04] p-4 rounded-2xl">
                    <!-- Status Filter Tabs -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                        <button 
                            v-for="status in ['All', 'Pending', 'Processing', 'Packing', 'Shipping', 'Delivered', 'Completed', 'Cancelled']" 
                            :key="status"
                            @click="selectedStatus = status"
                            :class="[
                                selectedStatus === status 
                                    ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' 
                                    : 'bg-transparent text-slate-400 border-transparent hover:text-slate-200'
                            ]"
                            class="px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer"
                        >
                            {{ status }}
                        </button>
                    </div>

                    <!-- Sorting options dropdown -->
                    <select 
                        v-model="sortBy"
                        class="glass-input py-2 px-3 text-xs font-semibold rounded-xl max-w-[170px] bg-slate-900/60 border border-white/[0.08] focus:outline-none cursor-pointer"
                    >
                        <option value="date_desc" class="bg-[#0f172a] text-slate-300 font-semibold">Placed: Newest First</option>
                        <option value="date_asc" class="bg-[#0f172a] text-slate-300 font-semibold">Placed: Oldest First</option>
                        <option value="amount_desc" class="bg-[#0f172a] text-slate-300 font-semibold">Total: High to Low</option>
                        <option value="amount_asc" class="bg-[#0f172a] text-slate-300 font-semibold">Total: Low to High</option>
                    </select>
                </div>

                <!-- Orders Table -->
                <div class="glass-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/[0.04] text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    <th class="px-6 py-4">Order Details</th>
                                    <th class="px-6 py-4">Client Name</th>
                                    <th class="px-6 py-4">Shipping Info</th>
                                    <th class="px-6 py-4">Items Summary</th>
                                    <th class="px-6 py-4">Total Amount</th>
                                    <th class="px-6 py-4">Date Placed</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.02]">
                                <template v-for="order in filteredOrders" :key="order.id">
                                    <tr 
                                        @click="toggleExpandOrder(order.id)"
                                        :class="{'bg-white/[0.03] border-indigo-500/30': expandedOrderId === order.id}"
                                        class="hover:bg-white/[0.02] text-sm text-slate-200 transition-colors duration-150 animate-fade-in cursor-pointer"
                                    >
                                    <!-- Order ID -->
                                    <td class="px-6 py-4.5 font-mono text-xs text-slate-400">#VR-{{ order.id }}</td>
                                    
                                    <!-- Client Name -->
                                    <td class="px-6 py-4.5 font-semibold text-white">
                                        {{ order.user?.name || 'Guest Customer' }}
                                    </td>

                                    <!-- Shipping Info -->
                                    <td class="px-6 py-4.5">
                                        <div class="flex flex-col text-xs text-slate-400 max-w-[180px] truncate" :title="order.shipping_address">
                                            <span class="truncate font-semibold">{{ order.shipping_address }}</span>
                                            <span class="mt-1 font-mono text-[10px] text-slate-500">{{ order.phone }}</span>
                                        </div>
                                    </td>

                                    <!-- Items Summary -->
                                    <td class="px-6 py-4.5">
                                        <div class="flex flex-col gap-1">
                                            <span 
                                                v-for="item in order.items" 
                                                :key="item.id" 
                                                class="text-xs text-slate-300"
                                            >
                                                {{ item.product?.name }} (x{{ item.quantity }})
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Total Price Column -->
                                    <td class="px-6 py-4.5 font-bold text-white">
                                        ${{ parseFloat(order.total_amount).toFixed(2) }}
                                    </td>

                                    <!-- Date Column -->
                                    <td class="px-6 py-4.5 text-xs text-slate-400">
                                        {{ formatDate(order.created_at) }}
                                    </td>

                                    <!-- Status Column -->
                                    <td class="px-6 py-4.5" @click.stop>
                                        <select 
                                            :value="order.status"
                                            @change="updateOrderStatus(order.id, $event.target.value)"
                                            :class="getStatusClass(order.status)"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full border bg-slate-900/60 focus:outline-none cursor-pointer focus:ring-1 focus:ring-indigo-500/50"
                                        >
                                            <option value="pending" class="bg-[#0f172a] text-amber-400 font-semibold">pending</option>
                                            <option value="processing" class="bg-[#0f172a] text-blue-400 font-semibold">processing</option>
                                            <option value="packing" class="bg-[#0f172a] text-pink-400 font-semibold">packing</option>
                                            <option value="shipping" class="bg-[#0f172a] text-indigo-400 font-semibold">shipping</option>
                                            <option value="delivered" class="bg-[#0f172a] text-teal-400 font-semibold">delivered</option>
                                            <option value="completed" class="bg-[#0f172a] text-emerald-400 font-semibold">completed</option>
                                            <option value="cancelled" class="bg-[#0f172a] text-rose-400 font-semibold">cancelled</option>
                                        </select>
                                    </td>

                                    <!-- Actions Column -->
                                    <td class="px-6 py-4.5 text-right" @click.stop>
                                        <button 
                                            @click="toggleExpandOrder(order.id)"
                                            class="glass-button text-xs py-1 px-3.5 rounded-full hover:bg-white/[0.08] inline-flex items-center gap-1.5"
                                        >
                                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{'rotate-180': expandedOrderId === order.id}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            {{ expandedOrderId === order.id ? 'Collapse' : 'Details' }}
                                        </button>
                                    </td>
                                </tr>

                                <!-- Expandable Accordion Row Details -->
                                <tr v-if="expandedOrderId === order.id" class="bg-white/[0.01] border-b border-white/[0.04]">
                                    <td colspan="8" class="p-6">
                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in">
                                            <!-- Left side: Items Invoice -->
                                            <div class="lg:col-span-2 space-y-4">
                                                <h4 class="text-white font-bold text-sm tracking-tight border-b border-white/[0.06] pb-2">Order items ({{ order.items?.length || 0 }})</h4>
                                                <div class="divide-y divide-white/[0.03] space-y-3">
                                                    <div 
                                                        v-for="item in order.items" 
                                                        :key="item.id"
                                                        class="flex gap-4 pt-3 first:pt-0"
                                                    >
                                                        <!-- Image -->
                                                        <div class="w-12 h-15 rounded-lg overflow-hidden bg-slate-900 border border-white/10 shrink-0">
                                                            <img 
                                                                v-if="getPrimaryImageUrl(item.product?.images)" 
                                                                :src="getPrimaryImageUrl(item.product.images)" 
                                                                class="w-full h-full object-cover" 
                                                            />
                                                            <div v-else class="w-full h-full flex items-center justify-center bg-indigo-500/10 text-indigo-300 text-xs font-bold uppercase">
                                                                {{ item.product?.name ? item.product.name.charAt(0) : '' }}
                                                            </div>
                                                        </div>
                                                        <!-- Details -->
                                                        <div class="flex-1 flex justify-between items-start">
                                                            <div class="space-y-0.5">
                                                                <span class="font-bold text-white text-sm block leading-tight">{{ item.product?.name }}</span>
                                                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                                                    Size: <span class="text-slate-300 font-semibold">{{ item.variant?.size }}</span> &nbsp;|&nbsp; 
                                                                    Color: <span class="text-slate-300 font-semibold">{{ item.variant?.color }}</span>
                                                                </span>
                                                            </div>
                                                            <div class="text-right">
                                                                <span class="font-mono text-xs text-slate-400 block">${{ parseFloat(item.price).toFixed(2) }} x {{ item.quantity }}</span>
                                                                <span class="font-bold text-white text-sm block mt-0.5">${{ (parseFloat(item.price) * item.quantity).toFixed(2) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Right side: Shipping card & Actions -->
                                            <div class="space-y-5" @click.stop>
                                                <div class="bg-white/[0.02] border border-white/[0.06] p-4 rounded-xl space-y-3">
                                                    <h4 class="text-white font-bold text-xs uppercase tracking-wider text-slate-400">Shipping Details</h4>
                                                    
                                                    <!-- Customer -->
                                                    <div class="flex flex-col text-xs">
                                                        <span class="text-slate-500 font-bold">Client Name</span>
                                                        <span class="text-slate-200 font-semibold mt-0.5">{{ order.user?.name || 'Guest Customer' }}</span>
                                                    </div>
                                                    
                                                    <!-- Address -->
                                                    <div class="flex flex-col text-xs">
                                                        <span class="text-slate-500 font-bold">Shipping Address</span>
                                                        <span class="text-slate-200 mt-0.5 leading-relaxed">{{ order.shipping_address }}</span>
                                                    </div>

                                                    <!-- Phone -->
                                                    <div class="flex flex-col text-xs">
                                                        <span class="text-slate-500 font-bold">Contact Phone</span>
                                                        <span class="text-slate-200 font-mono mt-0.5">{{ order.phone }}</span>
                                                    </div>
                                                </div>

                                                <!-- Quick state controls -->
                                                <div class="flex flex-col gap-2">
                                                    <span class="text-slate-500 text-[10px] font-bold uppercase tracking-wider ml-1">Fulfillment Actions</span>
                                                    <div class="flex flex-wrap gap-2">
                                                        <button 
                                                            v-if="order.status === 'pending'"
                                                            @click="updateOrderStatus(order.id, 'processing')"
                                                            class="flex-1 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition-all active:scale-95 cursor-pointer shadow-lg shadow-blue-500/10"
                                                        >
                                                            Start Processing
                                                        </button>
                                                        <button 
                                                            v-if="order.status === 'processing'"
                                                            @click="updateOrderStatus(order.id, 'packing')"
                                                            class="flex-1 py-2.5 bg-pink-500 hover:bg-pink-600 text-white rounded-xl text-xs font-bold transition-all active:scale-95 cursor-pointer shadow-lg shadow-pink-500/10"
                                                        >
                                                            Start Packing
                                                        </button>
                                                        <button 
                                                            v-if="order.status === 'packing'"
                                                            @click="updateOrderStatus(order.id, 'shipping')"
                                                            class="flex-1 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-xs font-bold transition-all active:scale-95 cursor-pointer shadow-lg shadow-indigo-500/10"
                                                        >
                                                            Ship Product
                                                        </button>
                                                        <button 
                                                            v-if="order.status === 'shipping'"
                                                            @click="updateOrderStatus(order.id, 'delivered')"
                                                            class="flex-1 py-2.5 bg-teal-500 hover:bg-teal-600 text-white rounded-xl text-xs font-bold transition-all active:scale-95 cursor-pointer shadow-lg shadow-teal-500/10"
                                                        >
                                                            Mark as Delivered
                                                        </button>
                                                        <button 
                                                            v-if="order.status === 'delivered'"
                                                            @click="updateOrderStatus(order.id, 'completed')"
                                                            class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition-all active:scale-95 cursor-pointer shadow-lg shadow-emerald-500/10"
                                                        >
                                                            Complete Order
                                                        </button>
                                                        <button 
                                                            v-if="['pending', 'processing', 'packing', 'shipping'].includes(order.status)"
                                                            @click="updateOrderStatus(order.id, 'cancelled')"
                                                            class="py-2.5 px-4 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white border border-rose-500/20 rounded-xl text-xs font-bold transition-all active:scale-95 cursor-pointer"
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
