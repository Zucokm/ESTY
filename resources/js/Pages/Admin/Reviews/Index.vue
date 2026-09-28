<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const props = defineProps({
    reviews: Array
});

const toggleApproval = (review) => {
    const action = review.is_approved ? 'HIDE' : 'APPROVE';
    if (confirm(`Are you sure you want to ${action} this review?`)) {
        router.put(route('admin.reviews.toggle-approval', review.id));
    }
};

const deleteReview = (id) => {
    if (confirm('Are you sure you want to permanently DELETE this review?')) {
        router.delete(route('admin.reviews.destroy', id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Reviews Moderation - Admin" />

    <div class="min-h-screen bg-[#080b11] text-slate-100 flex relative overflow-hidden selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Ambient Shifts -->
        <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>
        
        <!-- Left Sidebar -->
        <AdminSidebar active="reviews" />

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
                    <h2 class="text-sm font-bold text-white tracking-wide uppercase">Reviews</h2>
                </div>
            </header>

            <!-- Main Body -->
            <main class="flex-1 min-w-0 p-4 sm:p-8 space-y-6">
                <!-- Page Title -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-white tracking-tight">Reviews Moderation</h1>
                        <p class="text-slate-400 font-medium mt-1">Manage customer feedback, approve, hide or delete reviews.</p>
                    </div>
                </div>

                <!-- Reviews Table -->
                <div class="glass-card overflow-hidden border border-white/5 shadow-[0_24px_60px_-15px_rgba(0,0,0,0.6)]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="border-b border-white/[0.06] bg-white/[0.01] text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                    <th class="whitespace-nowrap px-6 py-4.5">Customer</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Product</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Rating & Review</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Date</th>
                                    <th class="whitespace-nowrap px-6 py-4.5">Status</th>
                                    <th class="whitespace-nowrap px-6 py-4.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/[0.06]">
                                <tr 
                                    v-for="review in reviews" 
                                    :key="review.id"
                                    class="hover:bg-white/[0.03] text-sm text-slate-200 transition-colors duration-200"
                                >
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-white">{{ review.user?.name || 'Unknown User' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 text-slate-300 font-medium">
                                        {{ review.product?.name || 'Unknown Product' }}
                                    </td>
                                    <td class="px-6 py-5 min-w-[300px]">
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex items-center gap-1 text-amber-400">
                                                <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= review.rating ? 'fill-current' : 'text-slate-700'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                            </div>
                                            <p class="text-xs text-slate-400 leading-relaxed">{{ review.comment }}</p>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 text-slate-400">
                                        {{ formatDate(review.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <span 
                                            class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider rounded-full"
                                            :class="review.is_approved ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'"
                                        >
                                            {{ review.is_approved ? 'Approved' : 'Hidden' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                @click="toggleApproval(review)"
                                                class="px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200"
                                                :class="review.is_approved ? 'bg-amber-500/10 border-amber-500/20 text-amber-400 hover:bg-amber-500/20' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20'"
                                            >
                                                {{ review.is_approved ? 'Hide' : 'Approve' }}
                                            </button>
                                            <button 
                                                @click="deleteReview(review.id)"
                                                class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors"
                                                title="Delete"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!reviews.length">
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 font-medium">
                                        No reviews found.
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
