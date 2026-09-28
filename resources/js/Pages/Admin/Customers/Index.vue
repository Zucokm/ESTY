<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const props = defineProps({
    customers: Array
});

const toggleBan = (customer) => {
    const action = customer.is_banned ? 'UNBAN' : 'BAN';
    if (confirm(`Are you sure you want to ${action} ${customer.name}?`)) {
        router.put(route('admin.customers.toggle-ban', customer.id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Customers - Admin" />

    <div class="min-h-screen bg-[#080b11] text-slate-100 flex relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Ambient Shifts -->
        <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>
        
        <!-- Left Sidebar -->
        <AdminSidebar active="customers" />

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0 md:ml-64 flex flex-col min-h-screen relative z-10">
            
            <!-- Top Navigation -->
            <header class="h-16 bg-white/[0.02] backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-4 sm:px-8 sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <button @click="() => window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'))" class="md:hidden p-2 -ml-2 text-slate-400 hover:text-white transition-colors focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-sm font-bold text-white tracking-wide uppercase">Customers</h2>
                </div>
            </header>

            <!-- Main Body -->
            <main class="flex-1 min-w-0 p-4 sm:p-8 space-y-6">
                <!-- Page Title -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight">Customer Management</h1>
                        <p class="text-slate-400 font-medium mt-1">View registered customers, their spending, and manage access.</p>
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="glass-card overflow-hidden border border-white/5 shadow-[0_24px_60px_-15px_rgba(0,0,0,0.6)]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="border-b border-white/[0.06] bg-white/[0.01] text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    <th class="whitespace-nowrap px-6 py-4.5">Customer Info</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Total Orders</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Total Spent</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Joined Date</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Status</th>
                                    <th class="whitespace-nowrap px-6 py-4.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.06]">
                                <tr 
                                    v-for="customer in customers" 
                                    :key="customer.id"
                                    class="hover:bg-white/[0.03] hover:translate-x-0.5 text-sm text-slate-200 transition-all duration-200"
                                >
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center font-bold text-white shadow-md">
                                                {{ customer.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-white">{{ customer.name }}</span>
                                                <span class="text-[10px] font-mono text-slate-400">{{ customer.email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 font-bold text-slate-300">
                                        {{ customer.orders_count }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 font-bold text-emerald-400">
                                        ${{ parseFloat(customer.orders_sum_total_amount || 0).toFixed(2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 text-slate-400">
                                        {{ formatDate(customer.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <span 
                                            class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider rounded-full"
                                            :class="customer.is_banned ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'"
                                        >
                                            {{ customer.is_banned ? 'Banned' : 'Active' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 text-right">
                                        <button 
                                            @click="toggleBan(customer)"
                                            class="px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200"
                                            :class="customer.is_banned ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20' : 'bg-rose-500/10 border-rose-500/20 text-rose-400 hover:bg-rose-500/20'"
                                        >
                                            {{ customer.is_banned ? 'Unban User' : 'Ban User' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!customers.length">
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 font-medium">
                                        No customers found.
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
