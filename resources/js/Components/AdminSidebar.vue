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
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden"
    ></div>

    <aside 
        :class="[
            isOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0',
            'w-64 fixed inset-y-0 left-0 bg-slate-950/[0.95] lg:bg-black/[0.15] backdrop-blur-3xl border-r border-white/[0.06] flex flex-col z-40 lg:z-20 transform transition-transform duration-300 ease-in-out'
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
        </div>
    </aside>
</template>
