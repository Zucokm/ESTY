<template>
    <Head title="Return Requests - Admin" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200 bg-[#0a0a0c] text-slate-100">
        <!-- Ambient Glowing Orbs -->
        <div class="fixed top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-900/10 blur-[150px] pointer-events-none"></div>
        <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] rounded-full bg-orange-900/10 blur-[150px] pointer-events-none"></div>

        <AdminSidebar active="returns" />

        <div class="flex-1 min-w-0 md:ml-64 flex flex-col min-h-screen">
            <header class="h-16 bg-black/10 backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-4 sm:px-8 sticky top-0 z-10">
                <h1 class="text-lg font-bold text-white tracking-tight">Return & Refund Requests</h1>
            </header>

            <main class="flex-1 p-4 sm:p-8 relative z-0 overflow-y-auto">
                <div class="glass-card border border-white/[0.06] overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/[0.04] bg-white/[0.02]">
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.04]">
                                <tr v-for="req in returnRequests" :key="req.id" class="hover:bg-white/[0.01] transition-colors group">
                                    <td class="px-6 py-4 text-sm font-bold text-white">#R-{{ req.id }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-indigo-400">#VR-{{ req.order_id }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-300">{{ req.user.name }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-400">{{ req.reason }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border" :class="getStatusClass(req.status)">
                                            {{ req.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Link :href="route('admin.returns.show', req.id)" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 text-xs font-bold transition-colors">
                                            Review
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="returnRequests.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">
                                        No return requests found.
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

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

defineProps({
    returnRequests: Array
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
</script>
