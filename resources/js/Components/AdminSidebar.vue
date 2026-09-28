<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

defineProps({
    active: {
        type: String,
        required: true
    }
});

const page = usePage();
const userName = computed(() => page.props.auth.user?.name || 'Admin');
const userEmail = computed(() => page.props.auth.user?.email || 'admin@shop.com');

const isOpen = ref(false);

const toggleSidebar = () => {
    isOpen.value = !isOpen.value;
};

onMounted(() => {
    window.addEventListener('toggle-admin-sidebar', toggleSidebar);
});

onUnmounted(() => {
    window.removeEventListener('toggle-admin-sidebar', toggleSidebar);
});
</script>

<template>
    <!-- Mobile Drawer Backdrop -->
    <div 
        v-if="isOpen" 
        @click="isOpen = false"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 md:hidden"
    ></div>

    <aside 
        :class="[
            isOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full md:translate-x-0',
            'w-64 fixed inset-y-0 left-0 bg-slate-950/[0.95] md:bg-black/[0.15] backdrop-blur-3xl border-r border-white/[0.06] flex flex-col z-40 md:z-20 transform transition-transform duration-300 ease-in-out'
        ]"
    >
        <!-- Sidebar Brand Header -->
        <div class="h-16 flex items-center px-6 border-b border-white/[0.06]">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-md shadow-indigo-500/20">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4a3 3 0 00-3 3v1h6V7a3 3 0 00-3-3zM3 19a2 2 0 002 2h14a2 2 0 002-2M5 11h14l1 8H4l1-8z" />
                    </svg>
                </div>
                <span class="text-white font-bold tracking-tight text-base uppercase">ESTY <span class="text-xs text-indigo-400 font-medium tracking-normal lowercase ml-1">admin</span></span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-2">
            <Link 
                :href="route('dashboard')"
                :class="[active === 'dashboard' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                Dashboard
            </Link>

            <Link 
                :href="route('admin.products.index')"
                :class="[active === 'products' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Products
            </Link>

            <Link 
                :href="route('categories.index')"
                :class="[active === 'categories' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                </svg>
                Categories
            </Link>

            <Link 
                :href="route('admin.orders.index')"
                :class="[active === 'orders' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Orders
            </Link>

            <Link 
                :href="route('admin.contacts.index')"
                :class="[active === 'contacts' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Customer Messages
            </Link>

            <Link 
                :href="route('admin.customers.index')"
                :class="[active === 'customers' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Customers
            </Link>

            <Link 
                :href="route('admin.reviews.index')"
                :class="[active === 'reviews' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                Reviews
            </Link>

            <Link 
                :href="route('admin.inventory.index')"
                :class="[active === 'inventory' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                Inventory Alerts
            </Link>

            <Link 
                :href="route('admin.coupons.index')"
                :class="[active === 'coupons' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                Promotions
            </Link>

            <Link 
                :href="route('admin.homepage.index')"
                :class="[active === 'homepage' ? 'bg-white/[0.08] text-white shadow-inner border-white/10' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] border-transparent']"
                class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border transition-all duration-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Homepage Settings
            </Link>
        </nav>

        <!-- Bottom User Profile Section -->
        <div class="p-4 border-t border-white/[0.06] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center font-bold text-white shadow-md">
                    {{ userName.charAt(0) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-semibold text-white leading-tight">{{ userName }}</span>
                    <span class="text-[10px] text-slate-400 font-medium truncate max-w-[120px]">{{ userEmail }}</span>
                </div>
            </div>
            
            <Link 
                :href="route('logout')" 
                method="post" 
                as="button"
                class="p-2 text-slate-400 hover:text-rose-400 transition-colors bg-white/[0.02] hover:bg-rose-500/10 rounded-lg"
                title="Log Out"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </Link>
        </div>
    </aside>
</template>
