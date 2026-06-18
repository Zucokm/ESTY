<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    orders: {
        type: Array,
        required: true
    }
});

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
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const activeCancelOrderId = ref(null);

const confirmCancel = (orderId) => {
    activeCancelOrderId.value = orderId;
};

const cancelOrder = () => {
    if (activeCancelOrderId.value) {
        router.post(route('orders.cancel', activeCancelOrderId.value), {}, {
            onSuccess: () => {
                activeCancelOrderId.value = null;
            }
        });
    }
};

const getItemImage = (item) => {
    if (!item.product || !item.product.images || item.product.images.length === 0) return null;
    if (item.variant && item.variant.color) {
        const color = item.variant.color.toLowerCase().trim();
        // 1. Try exact color match
        let matched = item.product.images.find(img => img.color && img.color.toLowerCase().trim() === color);
        // 2. Try substring match
        if (!matched) {
            matched = item.product.images.find(img => img.image_path && img.image_path.toLowerCase().includes(color));
        }
        if (matched) return matched.image_path;
    }
    // Fallback to primary image or first image
    const primary = item.product.images.find(img => img.is_primary);
    return primary ? primary.image_path : item.product.images[0].image_path;
};
</script>

<template>
    <Head title="My Orders - ESTY" />

    <div class="min-h-screen relative overflow-hidden pb-20 selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Floating Header/Navigation (Dynamic Island Style) -->
        <div class="fixed top-6 left-0 right-0 z-40 flex justify-center px-4">
            <nav class="glass-card px-6 py-3.5 w-full max-w-4xl flex items-center justify-between shadow-[0_12px_40px_0_rgba(0,0,0,0.3)] rounded-full border-white/10">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <span class="text-white font-bold tracking-tight text-lg">ESTY</span>
                </div>

                <!-- Main Nav Links -->
                <div class="hidden md:flex items-center gap-7">
                    <Link href="/" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Home</Link>
                    <Link :href="route('orders.index')" class="text-sm font-semibold text-slate-200 hover:text-white transition-colors duration-200">My Orders</Link>
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
                </div>
            </nav>
        </div>

        <!-- Body Area -->
        <main class="pt-32 px-4 max-w-4xl mx-auto space-y-8 relative z-10">
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Order History</h1>
                <p class="text-slate-400 font-medium mt-1">Review your premium garment checkout records and tracking details.</p>
            </div>

            <!-- Orders Listing -->
            <div class="space-y-6">
                <div 
                    v-for="order in orders" 
                    :key="order.id"
                    class="glass-card p-6 sm:p-8 space-y-6 relative overflow-hidden"
                >
                    <div class="absolute inset-0 bg-gradient-to-tr from-white/[0.01] to-transparent pointer-events-none"></div>
                    
                    <!-- Order Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-white/[0.06] pb-4 gap-4 relative z-10">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Order ID</span>
                            <h2 class="text-lg font-bold text-white tracking-tight">#VR-{{ order.id }}</h2>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:flex sm:items-center gap-6">
                            <div>
                                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Date Placed</span>
                                <span class="text-sm text-slate-300 font-semibold mt-1 block">{{ formatDate(order.created_at) }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Bill</span>
                                <span class="text-sm text-white font-extrabold mt-1 block">${{ parseFloat(order.total_amount).toFixed(2) }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</span>
                                <span 
                                    :class="getStatusClass(order.status)"
                                    class="inline-block mt-1 px-3 py-1 text-xs font-bold rounded-full border"
                                >
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Progress Timeline -->
                    <div class="relative py-4 px-2 relative z-10 border-b border-white/[0.04] mb-6">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-4">Order Track</span>
                        
                        <div class="flex items-center justify-between max-w-lg relative">
                            <!-- Connecting Line -->
                            <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-0.5 bg-white/[0.08] z-0"></div>
                            <div 
                                class="absolute left-0 top-1/2 -translate-y-1/2 h-0.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 transition-all duration-500 z-0"
                                :style="{
                                    width: order.status.toLowerCase() === 'pending' ? '0%' 
                                           : order.status.toLowerCase() === 'processing' ? '50%' 
                                           : order.status.toLowerCase() === 'completed' ? '100%' 
                                           : '100%',
                                    background: order.status.toLowerCase() === 'cancelled' 
                                                ? 'linear-gradient(to right, #818cf8, #f43f5e)' 
                                                : undefined
                                }"
                            ></div>

                            <!-- Steps -->
                            <template v-if="order.status.toLowerCase() === 'cancelled'">
                                <!-- Step 1: Placed -->
                                <div class="flex flex-col items-center gap-2 relative z-10">
                                    <div class="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold ring-4 ring-indigo-500/20">
                                        ✓
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-400">Order Placed</span>
                                </div>
                                <!-- Step 2: Cancelled -->
                                <div class="flex flex-col items-center gap-2 relative z-10">
                                    <div class="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs font-bold ring-4 ring-rose-500/20">
                                        ✕
                                    </div>
                                    <span class="text-[11px] font-bold text-rose-400">Cancelled</span>
                                </div>
                            </template>

                            <template v-else>
                                <!-- Step 1: Pending (Order Placed) -->
                                <div class="flex flex-col items-center gap-2 relative z-10">
                                    <div 
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300"
                                        :class="order.status.toLowerCase() !== 'pending' 
                                            ? 'bg-indigo-500 text-white ring-4 ring-indigo-500/20' 
                                            : 'bg-indigo-600 text-white ring-4 ring-indigo-500/30 animate-pulse'"
                                    >
                                        ✓
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-300">Placed</span>
                                </div>

                                <!-- Step 2: Processing (Garment Tailoring/Packing) -->
                                <div class="flex flex-col items-center gap-2 relative z-10">
                                    <div 
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300"
                                        :class="order.status.toLowerCase() === 'completed'
                                            ? 'bg-indigo-500 text-white ring-4 ring-indigo-500/20'
                                            : order.status.toLowerCase() === 'processing'
                                            ? 'bg-purple-600 text-white ring-4 ring-purple-600/30 animate-pulse'
                                            : 'bg-slate-900 text-slate-500 border border-white/10'"
                                    >
                                        <span v-if="order.status.toLowerCase() === 'completed'">✓</span>
                                        <span v-else>2</span>
                                    </div>
                                    <span 
                                        class="text-[11px] font-bold"
                                        :class="order.status.toLowerCase() === 'processing' || order.status.toLowerCase() === 'completed' ? 'text-slate-300' : 'text-slate-500'"
                                    >
                                        Processing
                                    </span>
                                </div>

                                <!-- Step 3: Completed (Delivered) -->
                                <div class="flex flex-col items-center gap-2 relative z-10">
                                    <div 
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300"
                                        :class="order.status.toLowerCase() === 'completed'
                                            ? 'bg-emerald-500 text-white ring-4 ring-emerald-500/20'
                                            : 'bg-slate-900 text-slate-500 border border-white/10'"
                                    >
                                        ✓
                                    </div>
                                    <span 
                                        class="text-[11px] font-bold"
                                        :class="order.status.toLowerCase() === 'completed' ? 'text-emerald-400' : 'text-slate-500'"
                                    >
                                        Completed
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Items list -->
                    <div class="space-y-4 relative z-10">
                        <div 
                            v-for="item in order.items" 
                            :key="item.id"
                            class="flex gap-4 items-center"
                        >
                            <!-- Image -->
                            <div class="w-12 h-16 rounded-lg overflow-hidden bg-slate-900 shrink-0 border border-white/5">
                                <img 
                                    v-if="getItemImage(item)" 
                                    :src="getItemImage(item)" 
                                    class="w-full h-full object-cover object-top" 
                                />
                                <div v-else class="w-full h-full flex items-center justify-center bg-indigo-500/10 text-indigo-300 text-xs font-bold">
                                    {{ item.product?.name ? item.product.name.charAt(0) : '' }}
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-white text-sm truncate">{{ item.product?.name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-bold text-slate-400 bg-white/[0.05] border border-white/[0.08] px-2 py-0.5 rounded">
                                        {{ item.variant?.size }} / {{ item.variant?.color }}
                                    </span>
                                    <span class="text-xs text-slate-500">Quantity: {{ item.quantity }}</span>
                                </div>
                            </div>

                            <!-- Price -->
                            <span class="text-sm font-extrabold text-white shrink-0">${{ (parseFloat(item.price) * item.quantity).toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Order Footer -->
                    <div class="pt-4 border-t border-white/[0.06] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                        <div class="text-xs text-slate-400">
                            <span class="font-bold text-slate-500 uppercase block tracking-wider mb-1">Shipping Details</span>
                            <span>{{ order.shipping_address }} &bull; {{ order.phone }}</span>
                        </div>
                        
                        <!-- Cancellation Controls -->
                        <div class="flex flex-col sm:items-end gap-2 shrink-0">
                            <!-- Cancellable button -->
                            <div v-if="order.is_cancellable" class="flex flex-col sm:items-end gap-1.5">
                                <button 
                                    @click="confirmCancel(order.id)"
                                    class="px-5 py-2.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-400 hover:shadow-lg hover:shadow-rose-500/20 active:scale-95 text-xs font-bold tracking-wide transition-all"
                                >
                                    Cancel Order
                                </button>
                                <span class="text-[10px] font-bold text-rose-400/80 tracking-wide uppercase">
                                    Cancellable for next {{ order.cancellation_minutes_remaining }} mins
                                </span>
                            </div>

                            <!-- Non-cancellable reasons -->
                            <div v-else-if="order.status.toLowerCase() === 'pending'" class="text-right">
                                <span class="inline-block px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400/70 text-[10px] font-bold uppercase tracking-wider">
                                    Cancel Window Closed (30m limit)
                                </span>
                            </div>
                            <div v-else-if="order.status.toLowerCase() === 'processing'" class="text-right">
                                <span class="inline-block px-3 py-1.5 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-400/70 text-[10px] font-bold uppercase tracking-wider">
                                    Processing - Cannot Cancel
                                </span>
                            </div>
                            <div v-else-if="order.status.toLowerCase() === 'completed'" class="text-right">
                                <span class="inline-block px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400/70 text-[10px] font-bold uppercase tracking-wider">
                                    Delivered - Order Closed
                                </span>
                            </div>
                            <div v-else-if="order.status.toLowerCase() === 'cancelled'" class="text-right">
                                <span class="inline-block px-3 py-1.5 rounded-lg bg-white/[0.04] border border-white/10 text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                                    Cancelled & Stock Restored
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="orders.length === 0" class="glass-card py-20 text-center text-slate-500 flex flex-col items-center justify-center gap-4">
                <svg class="w-12 h-12 stroke-current text-slate-600" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <h3 class="font-bold text-white text-lg tracking-tight">No Orders Placed</h3>
                <p class="text-xs text-slate-400 max-w-xs leading-relaxed">You haven't checkout any garments yet. Explore our latest arrivals to select clothing.</p>
                <Link href="/" class="glass-button-primary px-6 py-2.5 text-xs rounded-full mt-2 inline-block">Explore Shop</Link>
            </div>
        </main>

        <!-- Custom Confirmation Modal -->
        <div 
            v-if="activeCancelOrderId !== null"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md"
        >
            <div class="glass-card w-full max-w-md border-white/10 shadow-2xl p-6 sm:p-8 rounded-[2rem] text-center space-y-6 transform scale-100 transition-all duration-300">
                <div class="w-14 h-14 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mx-auto text-xl">
                    ⚠️
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-white tracking-tight">Cancel this order?</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Are you sure you want to cancel order #VR-{{ activeCancelOrderId }}? This action will restore the stock inventory for the items and cannot be undone.
                    </p>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button 
                        @click="activeCancelOrderId = null"
                        class="flex-1 glass-button py-3 px-6 rounded-xl font-bold text-xs"
                    >
                        Keep Order
                    </button>
                    <button 
                        @click="cancelOrder"
                        class="flex-1 bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs py-3 px-6 rounded-xl hover:shadow-lg hover:shadow-rose-500/25 active:scale-95 transition-all duration-200"
                    >
                        Confirm Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
