<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
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
    },
    stats: {
        type: Object,
        required: true
    },
    charts: {
        type: Object,
        required: true
    }
});

// Menu toggle states
const activeTab = ref('dashboard');
const showProfileDropdown = ref(false);

// SVG Chart Hover State
const hoveredPointIndex = ref(null);

// Calculate maximum revenue for vertical scaling (with headroom)
const maxRevenueValue = computed(() => {
    const vals = props.charts.revenueTrend.map(d => d.value);
    const maxVal = Math.max(...vals, 100);
    return maxVal * 1.15;
});

// Translate daily revenue numbers into SVG coordinate points
const revenuePoints = computed(() => {
    const width = 500;
    const height = 150;
    const padding = 25;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;
    
    const data = props.charts.revenueTrend;
    if (data.length === 0) return [];
    
    const maxVal = maxRevenueValue.value;
    
    return data.map((d, i) => {
        const x = padding + (i / (data.length - 1)) * chartWidth;
        const y = padding + chartHeight - (d.value / maxVal) * chartHeight;
        return { x, y, label: d.label, value: d.value };
    });
});

// Main trend line path (SVG M/L format)
const revenueLinePath = computed(() => {
    const pts = revenuePoints.value;
    if (pts.length === 0) return '';
    return 'M ' + pts.map(p => `${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' L ');
});

// Filled area path under the trend line
const revenueAreaPath = computed(() => {
    const pts = revenuePoints.value;
    if (pts.length === 0) return '';
    const width = 500;
    const height = 150;
    const padding = 25;
    const chartHeight = height - padding * 2;
    
    const startX = pts[0].x;
    const endX = pts[pts.length - 1].x;
    const baseY = padding + chartHeight;
    
    return `M ${startX.toFixed(1)} ${baseY.toFixed(1)} L ` + pts.map(p => `${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' L ') + ` L ${endX.toFixed(1)} ${baseY.toFixed(1)} Z`;
});

// Map database-driven props to UI layout format
const statsData = computed(() => [
    {
        title: 'Total Revenue',
        value: props.stats.totalRevenue.value,
        change: props.stats.totalRevenue.change,
        iconPath: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        title: 'Active Orders',
        value: props.stats.activeOrders.value,
        change: props.stats.activeOrders.change,
        iconPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'
    },
    {
        title: 'Low Stock Alerts',
        value: props.stats.lowStockAlerts.value,
        change: props.stats.lowStockAlerts.change,
        iconPath: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
    },
    {
        title: 'Total Products',
        value: props.stats.totalProducts.value,
        change: props.stats.totalProducts.change,
        iconPath: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
    }
]);

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

const updateOrderStatus = (orderId, newStatus) => {
    orderApi.updateStatus(orderId, {
        status: newStatus
    }, {
        preserveScroll: true
    });
};

const toggleSidebar = () => {
    window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'));
};
</script>

<template>
    <Head title="Admin Dashboard - ESTY" />

    <div class="min-h-screen bg-[#080b11] text-slate-100 flex relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Ambient Shifts -->
        <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>
        
        <!-- Left Sidebar -->
        <AdminSidebar active="dashboard" />

        <!-- Main Content Area -->
        <div class="flex-1 lg:ml-64 flex flex-col min-h-screen relative z-10">
            
            <!-- Top Navigation Bar -->
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
                <!-- Search bar -->
                <div class="w-full max-w-[160px] sm:max-w-xs relative">
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
            <main class="flex-1 p-4 sm:p-8 space-y-6 sm:space-y-8">
                <!-- Dashboard Welcome Title -->
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Overview</h1>
                    <p class="text-slate-400 text-sm font-medium">Variant inventories, live client sales, and tracking monitors.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="(stat, idx) in statsData" 
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

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Revenue Trend Area Chart (col-span-2) -->
                    <div class="glass-card p-6 flex flex-col justify-between col-span-1 lg:col-span-2 min-h-[320px]">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-white font-bold text-base tracking-tight">Revenue Analytics</h3>
                                    <p class="text-slate-400 text-xs font-medium">Daily transaction volumes over the last 7 days.</p>
                                </div>
                                <span class="px-2.5 py-1 text-[10px] font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-full">
                                    Last 7 Days
                                </span>
                            </div>

                            <!-- SVG Area Graph -->
                            <div class="relative w-full h-[180px] mt-2 select-none">
                                <svg class="w-full h-full overflow-visible" viewBox="0 0 500 150" preserveAspectRatio="none">
                                    <defs>
                                        <!-- Gradient Fill Under Area Chart -->
                                        <linearGradient id="areaGradient" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="rgb(99, 102, 241)" stop-opacity="0.35" />
                                            <stop offset="100%" stop-color="rgb(99, 102, 241)" stop-opacity="0.00" />
                                        </linearGradient>
                                        
                                        <!-- Glowing Line Drop-Shadow Filter -->
                                        <filter id="glow" x="-10%" y="-10%" width="120%" height="120%">
                                            <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="rgba(99, 102, 241, 0.4)" />
                                        </filter>
                                    </defs>

                                    <!-- Grid lines (Horizontal) -->
                                    <line x1="25" y1="25" x2="475" y2="25" stroke="rgba(255,255,255,0.03)" stroke-width="1" />
                                    <line x1="25" y1="62.5" x2="475" y2="62.5" stroke="rgba(255,255,255,0.03)" stroke-width="1" />
                                    <line x1="25" y1="100" x2="475" y2="100" stroke="rgba(255,255,255,0.03)" stroke-width="1" />
                                    <line x1="25" y1="125" x2="475" y2="125" stroke="rgba(255,255,255,0.08)" stroke-width="1" />

                                    <!-- Area Path (Gradient) -->
                                    <path :d="revenueAreaPath" fill="url(#areaGradient)" />

                                    <!-- Stroke Line Path -->
                                    <path :d="revenueLinePath" fill="none" stroke="rgb(99, 102, 241)" stroke-width="3" filter="url(#glow)" stroke-linecap="round" stroke-linejoin="round" />

                                    <!-- Interactive Circles (Points) and Hover Zones -->
                                    <g v-for="(pt, idx) in revenuePoints" :key="idx">
                                        <!-- Outer Pulse Ring on Hover -->
                                        <circle 
                                            v-if="hoveredPointIndex === idx"
                                            :cx="pt.x" 
                                            :cy="pt.y" 
                                            r="7" 
                                            fill="rgba(99, 102, 241, 0.3)"
                                            stroke="rgb(99, 102, 241)"
                                            stroke-width="1.5"
                                        />
                                        <!-- Main point dot -->
                                        <circle 
                                            :cx="pt.x" 
                                            :cy="pt.y" 
                                            :r="hoveredPointIndex === idx ? 4 : 3" 
                                            fill="#ffffff"
                                            stroke="rgb(99, 102, 241)"
                                            :stroke-width="hoveredPointIndex === idx ? 2.5 : 2"
                                            class="transition-all duration-150"
                                        />
                                        <!-- Vertical dotted line on hover -->
                                        <line
                                            v-if="hoveredPointIndex === idx"
                                            :x1="pt.x"
                                            :y1="pt.y"
                                            :x2="pt.x"
                                            y2="125"
                                            stroke="rgb(99, 102, 241)"
                                            stroke-width="1"
                                            stroke-dasharray="3,3"
                                            opacity="0.6"
                                        />
                                        <!-- Transparent overlay rect for hover area detection -->
                                        <rect 
                                            :x="pt.x - 25" 
                                            y="10" 
                                            width="50" 
                                            height="130" 
                                            fill="transparent" 
                                            class="cursor-pointer"
                                            @mouseenter="hoveredPointIndex = idx"
                                            @mouseleave="hoveredPointIndex = null"
                                        />
                                    </g>
                                </svg>

                                <!-- X Axis Labels -->
                                <div class="flex justify-between px-5 mt-1.5 text-[9px] font-bold text-slate-500 uppercase tracking-wider">
                                    <span v-for="d in charts.revenueTrend" :key="d.label">{{ d.label }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tooltip display area when hovering points -->
                        <div class="h-10 mt-4 flex items-center justify-between px-4 py-2 bg-white/[0.02] border border-white/[0.04] rounded-xl text-xs">
                            <span class="text-slate-400 font-medium">
                                {{ hoveredPointIndex !== null ? `Daily Revenue on ${charts.revenueTrend[hoveredPointIndex].label}:` : 'Hover graph points to view detailed daily sales volume.' }}
                            </span>
                            <span v-if="hoveredPointIndex !== null" class="text-white font-extrabold text-sm tracking-tight text-indigo-400">
                                ${{ charts.revenueTrend[hoveredPointIndex].value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                            </span>
                        </div>
                    </div>

                    <!-- Order Distribution Bar Chart (col-span-1) -->
                    <div class="glass-card p-6 flex flex-col justify-between min-h-[320px]">
                        <div>
                            <h3 class="text-white font-bold text-base tracking-tight mb-1">Order Distribution</h3>
                            <p class="text-slate-400 text-xs font-medium mb-6">Total sales separated by order fulfillment status.</p>

                            <!-- Simple visual horizontal bar chart -->
                            <div class="space-y-4">
                                <div v-for="item in charts.orderDistribution" :key="item.label" class="space-y-1">
                                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                                        <span class="text-slate-400">{{ item.label }}</span>
                                        <span class="text-white">{{ item.value }}</span>
                                    </div>
                                    <div class="h-2 w-full bg-white/[0.03] border border-white/[0.06] rounded-full overflow-hidden">
                                        <div 
                                            :style="{ width: `${Math.min((item.value / Math.max(...charts.orderDistribution.map(d => d.value), 1)) * 100, 100)}%` }"
                                            :class="[
                                                item.label.toLowerCase() === 'pending' ? 'bg-amber-400 shadow-md shadow-amber-500/20' : '',
                                                item.label.toLowerCase() === 'processing' ? 'bg-blue-400 shadow-md shadow-blue-500/20' : '',
                                                item.label.toLowerCase() === 'completed' ? 'bg-emerald-400 shadow-md shadow-emerald-500/20' : '',
                                                item.label.toLowerCase() === 'cancelled' ? 'bg-rose-400 shadow-md shadow-rose-500/20' : ''
                                            ]"
                                            class="h-full rounded-full transition-all duration-1000 ease-out"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Footer -->
                        <div class="mt-4 text-[10px] text-slate-500 font-semibold tracking-wider text-center uppercase">
                            Total Orders: {{ charts.orderDistribution.reduce((acc, curr) => acc + curr.value, 0) }}
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
                                            </select>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </span>
                                        </div>
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
