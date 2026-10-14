<template>
    <Head :title="`Return Ticket #R-${returnRequest.id} - ESTY`" />

    <div class="min-h-screen relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200 bg-[#0a0a0c] text-slate-100 flex flex-col">
        <!-- Ambient Glowing Orbs -->
        <div class="fixed top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-900/10 blur-[150px] pointer-events-none"></div>
        <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] rounded-full bg-purple-900/10 blur-[150px] pointer-events-none"></div>

        <NavigationBar active="orders" />

        <main class="relative z-10 flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-16 space-y-8">
            
            <!-- Top Header & Navigation -->
            <div class="glass-card p-6 sm:p-8 border border-white/[0.08] shadow-2xl rounded-3xl flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <Link 
                        :href="route('orders.index')" 
                        class="text-xs font-bold text-indigo-400 hover:text-indigo-300 uppercase tracking-widest inline-flex items-center gap-2 mb-3 group"
                    >
                        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        <span>Back to My Orders</span>
                    </Link>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Return Ticket #R-{{ returnRequest.id }}</h1>
                        <span class="px-3.5 py-1 rounded-xl text-xs font-extrabold uppercase tracking-wider border shadow-sm" :class="getStatusClass(returnRequest.status)">
                            {{ returnRequest.status.replace('_', ' ') }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1.5">
                        Connected to Order <strong class="text-slate-200 font-mono">#VR-{{ returnRequest.order.id }}</strong> · Submitted on {{ formatDate(returnRequest.created_at) }}
                    </p>
                </div>

                <!-- Status Progress Summary -->
                <div class="flex items-center gap-3 bg-white/[0.03] p-3.5 rounded-2xl border border-white/[0.06] shrink-0">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="getStatusIconBg(returnRequest.status)">
                        <svg v-if="returnRequest.status === 'completed'" class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        <svg v-else-if="returnRequest.status === 'rejected'" class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        <svg v-else class="w-5 h-5 text-indigo-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">{{ getStatusTitle(returnRequest.status) }}</p>
                        <p class="text-[11px] text-slate-400 max-w-[220px]">{{ getStatusDescription(returnRequest.status) }}</p>
                    </div>
                </div>
            </div>

            <!-- Visual Progress Stepper (Lifecycle Tracking) -->
            <div class="glass-card p-6 sm:p-8 border border-white/[0.08] shadow-xl rounded-3xl overflow-x-auto">
                <div class="min-w-[600px] flex items-center justify-between relative">
                    <!-- Progress Line -->
                    <div class="absolute left-6 right-6 top-1/2 -translate-y-1/2 h-1 bg-white/10 -z-0 rounded-full">
                        <div 
                            class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 transition-all duration-500 rounded-full"
                            :style="{ width: getProgressPercent(returnRequest.status) + '%' }"
                        ></div>
                    </div>

                    <!-- Steps -->
                    <div 
                        v-for="(step, idx) in steps" 
                        :key="step.key"
                        class="relative z-10 flex flex-col items-center gap-2 group"
                    >
                        <div 
                            class="w-11 h-11 rounded-2xl flex items-center justify-center font-bold text-xs border transition-all duration-300 shadow-md"
                            :class="[
                                isStepActive(returnRequest.status, idx) 
                                    ? 'bg-indigo-600 border-indigo-400 text-white shadow-indigo-600/30 scale-105 ring-4 ring-indigo-500/20' 
                                    : isStepCompleted(returnRequest.status, idx) 
                                        ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300' 
                                        : 'bg-slate-900 border-white/10 text-slate-500'
                            ]"
                        >
                            <svg v-if="isStepCompleted(returnRequest.status, idx)" class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span v-else>{{ idx + 1 }}</span>
                        </div>
                        <span 
                            class="text-xs font-bold whitespace-nowrap"
                            :class="isStepActive(returnRequest.status, idx) ? 'text-white' : 'text-slate-400'"
                        >
                            {{ step.title }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Main 2-Column Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- LEFT COLUMN: Request Info, Order Items, Evidence (5 cols) -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Return Reason & Note Card -->
                    <div class="glass-card p-6 border border-white/[0.08] shadow-xl rounded-3xl space-y-4">
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            Return Details
                        </h2>
                        <div>
                            <span class="text-[11px] font-semibold text-slate-400 block mb-1">Reason Selected</span>
                            <div class="text-sm font-bold text-white bg-white/[0.03] p-3 rounded-xl border border-white/[0.04]">
                                {{ returnRequest.reason }}
                            </div>
                        </div>
                        <div>
                            <span class="text-[11px] font-semibold text-slate-400 block mb-1">Your Detailed Note</span>
                            <p class="text-xs sm:text-sm text-slate-300 bg-white/[0.02] p-4 rounded-xl border border-white/[0.04] leading-relaxed break-words whitespace-pre-wrap">
                                {{ returnRequest.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Evidence Photos Card -->
                    <div v-if="returnRequest.images && returnRequest.images.length > 0" class="glass-card p-6 border border-white/[0.08] shadow-xl rounded-3xl">
                        <div class="flex items-center justify-between mb-3.5">
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Uploaded Evidence
                            </h2>
                            <span class="text-[11px] text-slate-500 lowercase">Tap to view</span>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div 
                                v-for="(img, idx) in returnRequest.images" 
                                :key="idx" 
                                @click="previewImage = img"
                                class="relative group aspect-square rounded-2xl overflow-hidden bg-black/40 border border-white/10 cursor-pointer hover:border-indigo-500 transition-all shadow-md"
                            >
                                <img :src="img" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items Card -->
                    <div v-if="returnRequest.order?.items" class="glass-card p-6 border border-white/[0.08] shadow-xl rounded-3xl">
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            Order Items (#VR-{{ returnRequest.order.id }})
                        </h2>
                        <div class="space-y-3">
                            <div 
                                v-for="item in returnRequest.order.items" 
                                :key="item.id" 
                                class="flex gap-3.5 items-center bg-white/[0.03] p-3 rounded-2xl border border-white/[0.04]"
                            >
                                <img 
                                    :src="getItemImage(item)" 
                                    class="w-12 h-12 rounded-xl object-cover bg-black/40 border border-white/10 shrink-0" 
                                    :alt="item.product?.name"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs sm:text-sm font-bold text-white truncate">{{ item.product?.name }}</p>
                                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                        <span>Qty: {{ item.quantity }}</span>
                                        <span v-if="item.variant" class="text-indigo-300">
                                            {{ item.variant.color }} <span v-if="item.variant.size">/ {{ item.variant.size }}</span>
                                        </span>
                                        <span class="text-slate-200 font-semibold ml-auto">{{ Number(item.price).toLocaleString() }} Ks</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Return Guidelines Advice -->
                    <div class="p-5 rounded-3xl bg-indigo-500/5 border border-indigo-500/15 space-y-2">
                        <div class="flex items-center gap-2 text-indigo-300 text-xs font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Return & Refund Instructions</span>
                        </div>
                        <ul class="text-[11px] text-slate-400 space-y-1.5 pl-5 list-disc leading-relaxed">
                            <li>Keep tags and original packaging intact for inspection.</li>
                            <li>You can message our support admin directly in the live chat on the right.</li>
                            <li>Once the admin confirms the return, you can send the parcel or coordinate courier pickup.</li>
                        </ul>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Interactive Live Support Chat (7 cols) -->
                <div class="lg:col-span-7 flex flex-col">
                    <div class="glass-card flex flex-col h-[650px] border border-white/[0.08] shadow-2xl rounded-3xl overflow-hidden relative">
                        <!-- Chat Header -->
                        <div class="px-6 py-4.5 border-b border-white/[0.06] bg-black/40 backdrop-blur-md flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-black text-white text-sm shadow-md shadow-indigo-500/20">
                                        E
                                    </div>
                                    <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-500 border-2 border-[#0e0e12]"></span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-bold text-white">ESTY Support Team</h3>
                                        <span class="text-[10px] bg-indigo-500/10 text-indigo-300 px-2 py-0.5 rounded-full font-mono border border-indigo-500/20">Official Support</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400">Direct RMA Discussion Channel</p>
                                </div>
                            </div>

                            <span class="text-[11px] font-mono text-slate-400 bg-white/5 px-2.5 py-1 rounded-lg">
                                Ticket #R-{{ returnRequest.id }}
                            </span>
                        </div>

                        <!-- Chat Messages Scroll Area -->
                        <div ref="messagesContainer" class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6 space-y-4 scroll-smooth">
                            <!-- Empty State -->
                            <div v-if="!returnRequest.messages || returnRequest.messages.length === 0" class="h-full flex flex-col items-center justify-center text-center p-8 text-slate-500">
                                <div class="w-16 h-16 rounded-3xl bg-white/[0.03] border border-white/[0.06] flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-300">Ticket Opened</p>
                                <p class="text-xs text-slate-500 max-w-xs mt-1">Our support team has received your return request. You can send any questions or additional details below.</p>
                            </div>

                            <!-- Messages Loop -->
                            <div 
                                v-for="msg in returnRequest.messages" 
                                :key="msg.id" 
                                class="flex flex-col"
                                :class="!msg.is_admin ? 'items-end' : 'items-start'"
                            >
                                <div class="flex items-end gap-2 max-w-[85%] sm:max-w-[78%]">
                                    <div 
                                        class="rounded-2xl p-3.5 sm:p-4 text-sm shadow-md transition-all break-words whitespace-pre-wrap"
                                        :class="!msg.is_admin 
                                            ? 'bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-br-sm shadow-indigo-600/15' 
                                            : 'bg-slate-800/90 border border-white/[0.08] text-slate-100 rounded-bl-sm shadow-black/20'"
                                    >
                                        <div class="flex items-center justify-between gap-3 text-[11px] mb-1 font-semibold" :class="!msg.is_admin ? 'text-indigo-200' : 'text-slate-400'">
                                            <span>{{ msg.is_admin ? 'ESTY Support' : 'You' }}</span>
                                            <span class="font-normal opacity-75 font-mono text-[10px]">{{ formatTime(msg.created_at) }}</span>
                                        </div>
                                        <p class="text-sm leading-relaxed">{{ msg.message }}</p>
                                        
                                        <!-- Seen Status for Customer's messages -->
                                        <div v-if="!msg.is_admin" class="flex items-center justify-end gap-1 mt-1.5 text-[10px]">
                                            <template v-if="msg.is_read">
                                                <span class="text-indigo-200 font-medium">Seen by Support</span>
                                                <svg class="w-3.5 h-3.5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7m-1-4l4 4L22 7" /></svg>
                                            </template>
                                            <template v-else>
                                                <span class="text-indigo-300/70">Delivered</span>
                                                <svg class="w-3 h-3 text-indigo-300/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Input Footer -->
                        <div class="p-3 sm:p-4 border-t border-white/[0.06] bg-slate-950/70 backdrop-blur-md shrink-0">
                            <form @submit.prevent="sendMessage" class="flex items-center gap-2 sm:gap-3">
                                <input 
                                    v-model="form.message" 
                                    type="text" 
                                    class="flex-1 glass-input py-2.5 sm:py-3 px-4 text-base sm:text-sm rounded-xl focus:border-indigo-500 transition-all placeholder:text-slate-500" 
                                    placeholder="Type a message to support..." 
                                    required 
                                    :disabled="form.processing"
                                />
                                <button 
                                    type="submit" 
                                    :disabled="form.processing || !form.message.trim()" 
                                    class="px-5 sm:px-6 py-2.5 sm:py-3 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition-all shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 shrink-0"
                                >
                                    <span class="hidden sm:inline">Send</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </main>
        
        <Footer />

        <!-- Evidence Image Lightbox Zoom Modal -->
        <div 
            v-if="previewImage" 
            @click="previewImage = null" 
            class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 sm:p-8"
        >
            <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center" @click.stop>
                <img :src="previewImage" class="max-w-full max-h-[80vh] object-contain rounded-3xl border border-white/10 shadow-2xl" />
                <button 
                    @click="previewImage = null" 
                    class="mt-4 px-6 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm transition-colors flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, nextTick, watch } from 'vue';
import NavigationBar from '@/Components/NavigationBar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    returnRequest: Object
});

const form = useForm({
    message: ''
});

const previewImage = ref(null);
const messagesContainer = ref(null);

const steps = [
    { key: 'pending', title: 'Submitted' },
    { key: 'reviewing', title: 'Reviewing' },
    { key: 'approved', title: 'Approved' },
    { key: 'completed', title: 'Refunded' }
];

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
});

watch(() => props.returnRequest.messages, () => {
    scrollToBottom();
}, { deep: true });

const getProgressPercent = (status) => {
    switch (status) {
        case 'pending': return 15;
        case 'reviewing': return 45;
        case 'approved': return 75;
        case 'completed': return 100;
        case 'rejected': return 100;
        default: return 0;
    }
};

const isStepActive = (status, idx) => {
    const statusIdxMap = { pending: 0, reviewing: 1, approved: 2, completed: 3 };
    return statusIdxMap[status] === idx;
};

const isStepCompleted = (status, idx) => {
    const statusIdxMap = { pending: 0, reviewing: 1, approved: 2, completed: 3 };
    const current = statusIdxMap[status] ?? -1;
    return current > idx;
};

const getStatusClass = (status) => {
    switch(status) {
        case 'pending': return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
        case 'reviewing': return 'bg-blue-500/10 text-blue-400 border-blue-500/30';
        case 'approved': return 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30';
        case 'rejected': return 'bg-rose-500/10 text-rose-400 border-rose-500/30';
        case 'completed': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        default: return 'bg-slate-500/10 text-slate-400 border-slate-500/30';
    }
};

const getStatusIconBg = (status) => {
    switch(status) {
        case 'completed': return 'bg-emerald-500/10 border border-emerald-500/30';
        case 'rejected': return 'bg-rose-500/10 border border-rose-500/30';
        default: return 'bg-indigo-500/10 border border-indigo-500/30';
    }
};

const getStatusTitle = (status) => {
    switch(status) {
        case 'pending': return 'Request Under Review';
        case 'reviewing': return 'Support Reviewing';
        case 'approved': return 'Return Approved';
        case 'completed': return 'Refund Completed';
        case 'rejected': return 'Request Declined';
        default: return 'Processing';
    }
};

const getStatusDescription = (status) => {
    switch(status) {
        case 'pending': return 'Our support team is reviewing your evidence and reason.';
        case 'reviewing': return 'Admin has opened your ticket. Check chat messages.';
        case 'approved': return 'Return accepted. Please ship the package back.';
        case 'completed': return 'Return accepted and stock/funds restored.';
        case 'rejected': return 'This return request has been rejected. Chat with support for details.';
        default: return 'Status is being updated.';
    }
};

const formatTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
};

const getItemImage = (item) => {
    if (!item.product || !item.product.images || item.product.images.length === 0) return '';
    if (item.variant && item.variant.color) {
        const color = item.variant.color.toLowerCase().trim();
        let matched = item.product.images.find(img => img.color && img.color.toLowerCase().trim() === color);
        if (!matched) {
            matched = item.product.images.find(img => img.image_path && img.image_path.toLowerCase().includes(color));
        }
        if (matched) return matched.image_path;
    }
    const primary = item.product.images.find(img => img.is_primary);
    return primary ? primary.image_path : item.product.images[0].image_path;
};

const sendMessage = () => {
    if (!form.message.trim()) return;
    form.post(route('returns.message', props.returnRequest.id), {
        onSuccess: () => {
            form.reset();
            scrollToBottom();
        },
        preserveScroll: true
    });
};
</script>
