<template>
    <Head title="Return Request - ESTY" />

    <div class="min-h-screen relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        <NavigationBar active="orders" />

        <main class="relative z-10 max-w-4xl mx-auto px-6 py-24 sm:py-32 space-y-12">
            <!-- Header -->
            <div class="glass-card p-8 border border-white/[0.06] flex items-center justify-between">
                <div>
                    <Link :href="route('orders.index')" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 uppercase tracking-widest flex items-center gap-2 mb-2">
                        ← Back to Orders
                    </Link>
                    <h1 class="text-2xl font-black text-white tracking-tight">Return Request</h1>
                    <p class="text-slate-400 text-sm mt-1">Order #VR-{{ returnRequest.order.id }}</p>
                </div>
                <div class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide border" 
                     :class="getStatusClass(returnRequest.status)">
                    {{ returnRequest.status }}
                </div>
            </div>

            <!-- Details -->
            <div class="glass-card p-8 border border-white/[0.06] space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-2">Reason</h3>
                    <p class="text-white bg-slate-900/50 p-4 rounded-xl border border-white/5">{{ returnRequest.reason }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-2">Description</h3>
                    <p class="text-slate-400 bg-slate-900/50 p-4 rounded-xl border border-white/5">{{ returnRequest.description }}</p>
                </div>
                <div v-if="returnRequest.images && returnRequest.images.length > 0">
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-2">Evidence Photos</h3>
                    <div class="flex gap-4 overflow-x-auto pb-4">
                        <img v-for="img in returnRequest.images" :key="img" :src="img" class="h-32 w-32 object-cover rounded-xl border border-white/10" />
                    </div>
                </div>
            </div>

            <!-- Chat / Messages -->
            <div class="glass-card flex flex-col h-[500px] border border-white/[0.06]">
                <div class="p-6 border-b border-white/[0.06]">
                    <h2 class="text-lg font-bold text-white tracking-tight">Messages with Admin</h2>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <div v-for="msg in returnRequest.messages" :key="msg.id" class="flex" :class="msg.is_admin ? 'justify-start' : 'justify-end'">
                        <div class="max-w-[80%] rounded-2xl p-4 text-sm" 
                             :class="msg.is_admin ? 'bg-slate-800 text-slate-200 border border-white/5' : 'bg-indigo-600 text-white'">
                            <p class="font-bold text-xs mb-1 opacity-70">{{ msg.is_admin ? 'Admin' : 'You' }}</p>
                            <p>{{ msg.message }}</p>
                                    <div v-if="!msg.is_admin && msg.is_read" class="text-[10px] text-right text-indigo-200 mt-1">Seen</div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-white/[0.06] bg-black/20">
                    <form @submit.prevent="sendMessage" class="flex gap-3">
                        <input v-model="form.message" type="text" class="flex-1 glass-input py-3 px-4 text-sm" placeholder="Type a message..." required />
                        <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl transition-colors">
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </main>
        
        <Footer />
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import NavigationBar from '@/Components/NavigationBar.vue';
import Footer from '@/Components/Footer.vue';

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

const sendMessage = () => {
    form.post(route('returns.message', props.returnRequest.id), {
        onSuccess: () => form.reset(),
        preserveScroll: true
    });
};
</script>
