<template>
    <Head title="Review Return Request - Admin" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200 bg-[#0a0a0c] text-slate-100">
        <AdminSidebar active="returns" />

        <div class="flex-1 min-w-0 md:ml-64 flex flex-col min-h-screen">
            <header class="h-16 bg-black/10 backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-4 sm:px-8 sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.returns.index')" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h1 class="text-lg font-bold text-white tracking-tight">Request #R-{{ returnRequest.id }}</h1>
                </div>
                <div class="flex gap-2">
                    <button @click="updateStatus('reviewing')" v-if="returnRequest.status === 'pending'" class="px-4 py-2 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold text-xs">Mark Reviewing</button>
                    <button @click="updateStatus('rejected')" v-if="['pending', 'reviewing'].includes(returnRequest.status)" class="px-4 py-2 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/50 hover:bg-rose-500 hover:text-white font-bold text-xs">Reject</button>
                    <button @click="updateStatus('approved')" v-if="['pending', 'reviewing'].includes(returnRequest.status)" class="px-4 py-2 rounded-xl bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-xs">Approve Return</button>
                    <button @click="updateStatus('completed')" v-if="returnRequest.status === 'approved'" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs">Confirm Refund (Restore Stock)</button>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-8 relative z-0 overflow-y-auto grid lg:grid-cols-2 gap-8">
                <!-- Details -->
                <div class="space-y-6">
                    <div class="glass-card p-6 border border-white/[0.06]">
                        <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4">Customer Info</h2>
                        <p class="text-white font-bold">{{ returnRequest.user.name }}</p>
                        <p class="text-slate-400 text-sm">{{ returnRequest.user.email }}</p>
                    </div>

                    <div class="glass-card p-6 border border-white/[0.06]">
                        <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4">Request Details</h2>
                        <div class="space-y-4">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Status</span>
                                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border" :class="getStatusClass(returnRequest.status)">{{ returnRequest.status }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Reason</span>
                                <p class="text-white bg-white/5 p-3 rounded-lg">{{ returnRequest.reason }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Description</span>
                                <p class="text-slate-400 bg-white/5 p-3 rounded-lg">{{ returnRequest.description }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="returnRequest.images && returnRequest.images.length > 0" class="glass-card p-6 border border-white/[0.06]">
                        <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4">Evidence</h2>
                        <div class="flex gap-4 overflow-x-auto pb-4">
                            <a :href="img" target="_blank" v-for="img in returnRequest.images" :key="img" class="shrink-0 block">
                                <img :src="img" class="h-40 w-40 object-cover rounded-xl border border-white/10 hover:border-indigo-500 transition-colors" />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Chat & Order Info -->
                <div class="space-y-6 flex flex-col h-full">
                    <div class="glass-card p-6 border border-white/[0.06]">
                        <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4">Order Items (VR-{{ returnRequest.order.id }})</h2>
                        <div class="space-y-3">
                            <div v-for="item in returnRequest.order.items" :key="item.id" class="flex gap-4 items-center bg-white/5 p-3 rounded-xl">
                                <img :src="getItemImage(item)" class="w-12 h-12 rounded-lg object-cover" />
                                <div>
                                    <p class="text-sm font-bold text-white">{{ item.product.name }}</p>
                                    <p class="text-xs text-slate-400">Qty: {{ item.quantity }} | {{ item.variant?.color }} - {{ item.variant?.size }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card flex flex-col flex-1 min-h-[400px] border border-white/[0.06]">
                        <div class="p-6 border-b border-white/[0.06]">
                            <h2 class="text-lg font-bold text-white tracking-tight">Communication Ticket</h2>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div v-for="msg in returnRequest.messages" :key="msg.id" class="flex" :class="msg.is_admin ? 'justify-end' : 'justify-start'">
                                <div class="max-w-[80%] rounded-2xl p-4 text-sm" 
                                     :class="msg.is_admin ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-200 border border-white/5'">
                                    <p class="font-bold text-xs mb-1 opacity-70">{{ msg.is_admin ? 'You' : 'Customer' }}</p>
                                    <p>{{ msg.message }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 border-t border-white/[0.06] bg-black/20">
                            <form @submit.prevent="sendMessage" class="flex gap-3">
                                <input v-model="form.message" type="text" class="flex-1 glass-input py-2.5 px-4 text-sm" placeholder="Reply to customer..." required />
                                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl text-sm">
                                    Send
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const props = defineProps({
    returnRequest: Object
});

const form = useForm({
    message: ''
});

const getStatusClass = (status) => {
    switch(status) {
        case 'pending': return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
        case 'reviewing': return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        case 'approved': return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
        case 'rejected': return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
        case 'completed': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    }
};

const updateStatus = (status) => {
    if (confirm(`Are you sure you want to change status to ${status}?`)) {
        router.put(route('admin.returns.status', props.returnRequest.id), { status }, { preserveScroll: true });
    }
};

const sendMessage = () => {
    form.post(route('admin.returns.message', props.returnRequest.id), {
        onSuccess: () => form.reset(),
        preserveScroll: true
    });
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
</script>
