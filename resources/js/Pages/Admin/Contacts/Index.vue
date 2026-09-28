<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    contacts: Object,
});

const updateStatus = (id, newStatus) => {
    router.put(route('admin.contacts.update', id), { status: newStatus }, {
        preserveScroll: true
    });
};

const toggleSidebar = () => {
    window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'));
};
</script>

<template>
    <Head title="Customer Messages - Admin" />

    <div class="min-h-screen relative overflow-hidden bg-slate-950">
        <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>
        
        <AdminSidebar active="contacts" />

        <main class="lg:ml-64 pt-6 px-4 sm:px-6 lg:px-8 pb-12 relative z-10 min-h-screen flex flex-col">
            <!-- Mobile Hamburger -->
            <div class="lg:hidden flex justify-start mb-6">
                <button @click="toggleSidebar" class="p-2 text-slate-400 hover:text-white glass-button rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">Customer Messages</h1>
                    <p class="text-slate-400 font-medium mt-1">Manage and respond to customer inquiries.</p>
                </div>
            </div>

            <div class="glass-card flex-1 p-0 overflow-hidden flex flex-col border border-white/10 rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/[0.08] bg-white/[0.02]">
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Message</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            <tr v-for="contact in contacts.data" :key="contact.id" class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                    {{ new Date(contact.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-white">{{ contact.name }}</div>
                                    <div class="text-xs text-slate-400">{{ contact.email }}</div>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="text-sm font-bold text-slate-200 truncate">{{ contact.subject || 'No Subject' }}</div>
                                    <div class="text-xs text-slate-400 line-clamp-2 mt-0.5">{{ contact.message }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider border"
                                        :class="{
                                            'bg-rose-500/10 text-rose-400 border-rose-500/20': contact.status === 'new',
                                            'bg-indigo-500/10 text-indigo-400 border-indigo-500/20': contact.status === 'read',
                                            'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': contact.status === 'replied'
                                        }"
                                    >
                                        {{ contact.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <select 
                                        @change="updateStatus(contact.id, $event.target.value)"
                                        :value="contact.status"
                                        class="glass-input py-1 px-2 text-xs w-28 appearance-none bg-slate-900"
                                    >
                                        <option value="new">New</option>
                                        <option value="read">Read</option>
                                        <option value="replied">Replied</option>
                                    </select>
                                </td>
                            </tr>
                            <tr v-if="contacts.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 font-medium">
                                    No messages found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-6 border-t border-white/[0.08]" v-if="contacts.data.length > 0">
                    <Pagination :links="contacts.links" />
                </div>
            </div>
        </main>
    </div>
</template>
