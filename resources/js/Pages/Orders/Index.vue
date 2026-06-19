<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Footer from '@/Components/Footer.vue';

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

const getStatusStep = (status) => {
    switch (status.toLowerCase()) {
        case 'pending': return 1;
        case 'processing': return 2;
        case 'packing': return 3;
        case 'shipping': return 4;
        case 'delivered': return 5;
        case 'completed': return 6;
        default: return 0;
    }
};

const getTimelineWidth = (status) => {
    const step = getStatusStep(status);
    if (step <= 1) return '0%';
    if (step === 2) return '25%';
    if (step === 3) return '50%';
    if (step === 4) return '75%';
    return '100%'; // step >= 5
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

    <div class="min-h-screen relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        
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
                    <Link href="/#shop" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Shop</Link>
                    <Link :href="route('products.index')" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Products</Link>
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
                    <div class="relative py-8 px-4 relative z-10 border-b border-white/[0.04] mb-8 bg-white/[0.01] rounded-2xl border border-white/[0.03]">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-6">Order Track</span>
                        
                        <div class="flex items-center justify-between max-w-2xl mx-auto relative px-2">
                            <!-- Connecting Line Background -->
                            <div class="absolute left-5 right-5 top-[20px] -translate-y-1/2 h-1 bg-white/[0.06] rounded-full z-0"></div>
                            
                            <!-- Active Progress Line -->
                            <div 
                                class="absolute left-5 top-[20px] -translate-y-1/2 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 via-pink-500 via-blue-500 to-emerald-500 rounded-full transition-all duration-500 z-0"
                                :style="{
                                    width: getTimelineWidth(order.status),
                                    background: order.status.toLowerCase() === 'cancelled' 
                                                ? 'linear-gradient(to right, #818cf8, #f43f5e)' 
                                                : undefined
                                }"
                            ></div>

                            <!-- Steps -->
                            <template v-if="order.status.toLowerCase() === 'cancelled'">
                                <!-- Step 1: Placed -->
                                <div class="flex flex-col items-center gap-2.5 relative z-10">
                                    <div class="w-10 h-10 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold ring-4 ring-indigo-500/20 shadow-lg shadow-indigo-500/10">
                                        ✓
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Placed</span>
                                </div>
                                <!-- Step 2: Cancelled -->
                                <div class="flex flex-col items-center gap-2.5 relative z-10">
                                    <div class="w-10 h-10 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs font-bold ring-4 ring-rose-500/20 shadow-lg shadow-rose-500/10">
                                        ✕
                                    </div>
                                    <span class="text-[11px] font-bold text-rose-400 uppercase tracking-wide">Cancelled</span>
                                </div>
                            </template>

                            <template v-else>
                                <!-- Step 1: Placed -->
                                <div class="flex flex-col items-center gap-2.5 relative z-10">
                                    <div 
                                        class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-lg"
                                        :class="getStatusStep(order.status) > 1
                                            ? 'bg-indigo-500 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/10' 
                                            : 'bg-indigo-600 text-white ring-4 ring-indigo-500/30 animate-pulse shadow-indigo-600/20'"
                                    >
                                        <span v-if="getStatusStep(order.status) > 1" class="text-xs font-bold">✓</span>
                                        <svg v-else class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] sm:text-[11px] font-bold tracking-wide uppercase" :class="getStatusStep(order.status) >= 1 ? 'text-indigo-400' : 'text-slate-500'">Placed</span>
                                </div>

                                <!-- Step 2: Processing -->
                                <div class="flex flex-col items-center gap-2.5 relative z-10">
                                    <div 
                                        class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-lg"
                                        :class="getStatusStep(order.status) > 2
                                            ? 'bg-indigo-500 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/10'
                                            : getStatusStep(order.status) === 2
                                            ? 'bg-purple-600 text-white ring-4 ring-purple-600/30 animate-pulse shadow-purple-600/20'
                                            : 'bg-slate-900/90 text-slate-500 border border-white/10'"
                                    >
                                        <span v-if="getStatusStep(order.status) > 2" class="text-xs font-bold">✓</span>
                                        <svg v-else class="w-4.5 h-4.5" :class="{'animate-spin [animation-duration:8s]': getStatusStep(order.status) === 2}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <span 
                                        class="text-[10px] sm:text-[11px] font-bold tracking-wide uppercase"
                                        :class="getStatusStep(order.status) >= 2 ? 'text-purple-400' : 'text-slate-500'"
                                    >
                                        Processing
                                    </span>
                                </div>

                                <!-- Step 3: Packing -->
                                <div class="flex flex-col items-center gap-2.5 relative z-10">
                                    <div 
                                        class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-lg"
                                        :class="getStatusStep(order.status) > 3
                                            ? 'bg-indigo-500 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/10'
                                            : getStatusStep(order.status) === 3
                                            ? 'bg-pink-600 text-white ring-4 ring-pink-600/30 animate-pulse shadow-pink-600/20'
                                            : 'bg-slate-900/90 text-slate-500 border border-white/10'"
                                    >
                                        <span v-if="getStatusStep(order.status) > 3" class="text-xs font-bold">✓</span>
                                        <svg v-else class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <span 
                                        class="text-[10px] sm:text-[11px] font-bold tracking-wide uppercase"
                                        :class="getStatusStep(order.status) >= 3 ? 'text-pink-400' : 'text-slate-500'"
                                    >
                                        Packing
                                    </span>
                                </div>

                                <!-- Step 4: Delivering -->
                                <div class="flex flex-col items-center gap-2.5 relative z-10">
                                    <div 
                                        class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-lg"
                                        :class="getStatusStep(order.status) > 4
                                            ? 'bg-indigo-500 text-white ring-4 ring-indigo-500/20 shadow-indigo-500/10'
                                            : getStatusStep(order.status) === 4
                                            ? 'bg-blue-600 text-white ring-4 ring-blue-600/30 animate-pulse shadow-blue-600/20'
                                            : 'bg-slate-900/90 text-slate-500 border border-white/10'"
                                    >
                                        <span v-if="getStatusStep(order.status) > 4" class="text-xs font-bold">✓</span>
                                        <svg v-else class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V14a1 1 0 00-1-1H13" />
                                        </svg>
                                    </div>
                                    <span 
                                        class="text-[10px] sm:text-[11px] font-bold tracking-wide uppercase"
                                        :class="getStatusStep(order.status) >= 4 ? 'text-blue-400' : 'text-slate-500'"
                                    >
                                        Delivering
                                    </span>
                                </div>

                                <!-- Step 5: Completed / Delivered -->
                                <div class="flex flex-col items-center gap-2.5 relative z-10">
                                    <div 
                                        class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-lg"
                                        :class="getStatusStep(order.status) >= 5
                                            ? 'bg-emerald-500 text-white ring-4 ring-emerald-500/20 shadow-emerald-500/20 shadow-lg'
                                            : 'bg-slate-900/90 text-slate-500 border border-white/10'"
                                    >
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span 
                                        class="text-[10px] sm:text-[11px] font-bold tracking-wide uppercase"
                                        :class="getStatusStep(order.status) >= 5 ? 'text-emerald-400' : 'text-slate-500'"
                                    >
                                        {{ order.status.toLowerCase() === 'delivered' ? 'Delivered' : 'Completed' }}
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
                            <div v-else-if="order.status.toLowerCase() === 'packing'" class="text-right">
                                <span class="inline-block px-3 py-1.5 rounded-lg bg-pink-500/10 border border-pink-500/20 text-pink-400/70 text-[10px] font-bold uppercase tracking-wider">
                                    Packing - Cannot Cancel
                                </span>
                            </div>
                            <div v-else-if="order.status.toLowerCase() === 'shipping'" class="text-right">
                                <span class="inline-block px-3 py-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400/70 text-[10px] font-bold uppercase tracking-wider">
                                    Delivering - Cannot Cancel
                                </span>
                            </div>
                            <div v-else-if="order.status.toLowerCase() === 'delivered'" class="text-right">
                                <span class="inline-block px-3 py-1.5 rounded-lg bg-teal-500/10 border border-teal-500/20 text-teal-400/70 text-[10px] font-bold uppercase tracking-wider">
                                    Delivered - Cannot Cancel
                                </span>
                            </div>
                            <div v-else-if="order.status.toLowerCase() === 'completed'" class="text-right">
                                <span class="inline-block px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400/70 text-[10px] font-bold uppercase tracking-wider">
                                    Completed - Order Closed
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

        <Footer />

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
