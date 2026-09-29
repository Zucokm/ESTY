<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    coupons: Array
});

const isModalOpen = ref(false);
const activeTab = ref('active');

const editingCoupon = ref(null);
const expandedCouponId = ref(null);

const filteredCoupons = computed(() => {
    if (activeTab.value === 'active') {
        return props.coupons.filter(c => !c.deleted_at);
    } else {
        return props.coupons.filter(c => c.deleted_at);
    }
});

const toggleExpandCoupon = (id) => {
    expandedCouponId.value = expandedCouponId.value === id ? null : id;
};


const form = useForm({
    code: '',
    type: 'percent',
    value: '',
    minimum_spend: '',
    usage_limit: '',
    valid_until: '',
    is_active: true
});

const openModal = (coupon = null) => {
    if (coupon) {
        editingCoupon.value = coupon;
        form.code = coupon.code;
        form.type = coupon.type;
        form.value = coupon.value;
        form.minimum_spend = coupon.minimum_spend;
        form.usage_limit = coupon.usage_limit;
        form.valid_until = coupon.valid_until ? coupon.valid_until.split('T')[0] : '';
        form.is_active = !!coupon.is_active;
    } else {
        editingCoupon.value = null;
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (editingCoupon.value) {
        form.put(route('admin.coupons.update', editingCoupon.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.coupons.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCoupon = (id) => {
    if (confirm('Are you sure you want to delete this coupon?')) {
        router.delete(route('admin.coupons.destroy', id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Promotions & Coupons - Admin" />

    <div class="min-h-screen bg-[#080b11] text-slate-100 flex relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Ambient Shifts -->
        <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>
        
        <!-- Left Sidebar -->
        <AdminSidebar active="coupons" />

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
                    <h2 class="text-sm font-bold text-white tracking-wide uppercase">Promotions</h2>
                </div>
            </header>

            <!-- Main Body -->
            <main class="flex-1 min-w-0 p-4 sm:p-8 space-y-6">
                <!-- Page Title -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight">Coupons Management</h1>
                        <p class="text-slate-400 font-medium mt-1">Create and manage discount codes for your store.</p>
                    </div>
                    <button 
                        @click="openModal()" 
                        class="px-5 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl shadow-[0_0_20px_rgba(99,102,241,0.3)] transition-all flex items-center justify-center gap-2 whitespace-nowrap"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Coupon
                    </button>
                </div>

                <div class="flex gap-4 border-b border-white/10 mb-6">
                    <button 
                        @click="activeTab = 'active'" 
                        :class="[activeTab === 'active' ? 'text-indigo-400 border-indigo-500' : 'text-slate-400 border-transparent hover:text-white hover:border-white/20']"
                        class="px-4 py-3 font-bold text-sm tracking-wider uppercase border-b-2 transition-all"
                    >Active Promotions</button>
                    <button 
                        @click="activeTab = 'archived'" 
                        :class="[activeTab === 'archived' ? 'text-indigo-400 border-indigo-500' : 'text-slate-400 border-transparent hover:text-white hover:border-white/20']"
                        class="px-4 py-3 font-bold text-sm tracking-wider uppercase border-b-2 transition-all"
                    >Past/Deleted</button>
                </div>

                <!-- Coupons Table -->
                <div class="glass-card overflow-hidden border border-white/5 shadow-[0_24px_60px_-15px_rgba(0,0,0,0.6)]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="border-b border-white/[0.06] bg-white/[0.01] text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    <th class="whitespace-nowrap px-6 py-4.5">Code</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Discount</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Min. Spend</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Usage</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Expiry Date</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Status</th>
                                    <th class="whitespace-nowrap px-6 py-4.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.06]">
                                <template v-for="coupon in filteredCoupons" :key="coupon.id">
                                    <tr 
                                        @click="toggleExpandCoupon(coupon.id)"
                                        :class="[expandedCouponId === coupon.id ? 'bg-indigo-500/[0.04] border-l border-indigo-500' : 'hover:bg-white/[0.03] hover:translate-x-0.5']"
                                        class="text-sm text-slate-200 transition-all duration-200 cursor-pointer"
                                    >
                                        <td class="whitespace-nowrap px-6 py-5 font-mono font-bold text-indigo-400">
                                            {{ coupon.code }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-5 font-bold text-white">
                                            {{ coupon.type === 'percent' ? coupon.value + '%' : coupon.value + ' Ks' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-5 text-slate-400">
                                            {{ coupon.minimum_spend ? coupon.minimum_spend + ' Ks' : 'No Min' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-5 text-slate-400">
                                            {{ coupon.times_used || coupon.used_count || 0 }} / {{ coupon.usage_limit || '∞' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-5 text-slate-400">
                                            {{ formatDate(coupon.valid_until) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-5">
                                            <span v-if="coupon.deleted_at" class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider rounded-full bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                                Deleted
                                            </span>
                                            <span v-else
                                                class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider rounded-full"
                                                :class="coupon.is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'"
                                            >
                                                {{ coupon.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-5 text-right">
                                            <div v-if="!coupon.deleted_at" class="flex items-center justify-end gap-2" @click.stop>
                                                <button 
                                                    @click="openModal(coupon)"
                                                    class="p-2 text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-colors"
                                                    title="Edit"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                </button>
                                                <button 
                                                    @click="deleteCoupon(coupon.id)"
                                                    class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors"
                                                    title="Delete"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Expanded Details (History) -->
                                    <tr v-if="expandedCouponId === coupon.id" class="bg-slate-900/40">
                                        <td colspan="7" class="p-6">
                                            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-4">Usage History ({{ coupon.orders?.length || 0 }} orders)</h4>
                                            
                                            <div v-if="coupon.orders && coupon.orders.length > 0" class="space-y-3">
                                                <div v-for="order in coupon.orders" :key="order.id" class="bg-slate-900/50 border border-white/5 p-4 rounded-xl flex items-center justify-between">
                                                    <div>
                                                        <div class="text-sm font-bold text-white mb-1">
                                                            Order <span class="text-indigo-400">#VR-{{ order.id }}</span>
                                                        </div>
                                                        <div class="text-xs text-slate-400">
                                                            Customer: <span class="text-slate-300">{{ order.user?.name || 'Guest' }}</span> &bull; 
                                                            Date: {{ new Date(order.created_at).toLocaleString() }}
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-sm font-bold text-emerald-400">-{{ order.discount_amount }} Ks</div>
                                                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Discount Applied</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div v-else class="text-sm text-slate-500 italic bg-white/[0.02] p-4 rounded-lg border border-white/5 text-center">
                                                This coupon has not been used yet.
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="!filteredCoupons.length">
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500 font-medium">
                                        No coupons found. Create one to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>

        <!-- Coupon Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="glass-card w-full max-w-lg p-6 sm:p-8 rounded-3xl relative overflow-hidden shadow-2xl border border-white/10">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white tracking-tight">{{ editingCoupon ? 'Edit Coupon' : 'Create New Coupon' }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Coupon Code</label>
                        <input v-model="form.code" type="text" class="w-full bg-slate-900/50 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 uppercase" placeholder="e.g. SUMMER20" required>
                        <span v-if="form.errors.code" class="text-rose-400 text-xs mt-1">{{ form.errors.code }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Discount Type</label>
                            <select v-model="form.type" class="w-full bg-slate-900/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (Ks)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">{{ form.type === 'percent' ? 'Percentage (%)' : 'Amount (Ks)' }}</label>
                            <input v-model="form.value" type="number" step="0.01" class="w-full bg-slate-900/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500" :placeholder="form.type === 'percent' ? 'e.g. 20' : 'e.g. 5000'" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Usage Limit</label>
                            <input v-model="form.usage_limit" type="number" class="w-full bg-slate-900/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500" placeholder="e.g. 50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Expiry Date</label>
                            <input v-model="form.valid_until" type="date" class="w-full bg-slate-900/50 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500" style="color-scheme: dark;">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500"></div>
                            <span class="ml-3 text-sm font-bold text-slate-300">Active</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="closeModal" class="px-5 py-2.5 rounded-xl font-bold text-slate-400 hover:text-white hover:bg-white/5 transition-colors">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-indigo-500 hover:bg-indigo-600 disabled:opacity-50 text-white font-bold rounded-xl shadow-[0_0_15px_rgba(99,102,241,0.3)] transition-all">
                            {{ editingCoupon ? 'Update Coupon' : 'Create Coupon' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
