<script setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import NavigationBar from '@/Components/NavigationBar.vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import WishlistDrawer from '@/Components/WishlistDrawer.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showWishlistDrawer = ref(false);

const toggleSidebar = () => {
    window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'));
};
</script>

<template>
    <Head title="Profile - ESTY" />

    <div class="min-h-screen relative overflow-hidden pb-20 selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Navigation based on role -->
        <template v-if="$page.props.auth.user?.role === 'admin'">
            <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
            <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>
            <AdminSidebar active="profile" />
        </template>
        <template v-else>
            <NavigationBar 
                active="profile" 
                @open-wishlist="showWishlistDrawer = true" 
            />
        </template>

        <!-- Body Area -->
        <main :class="['pt-32 px-4 max-w-4xl mx-auto space-y-8 relative z-10', $page.props.auth.user?.role === 'admin' ? 'md:ml-64' : '']">
            <!-- Mobile Hamburger for Admin -->
            <div v-if="$page.props.auth.user?.role === 'admin'" class="md:hidden flex justify-start -mt-20 mb-6">
                <button @click="toggleSidebar" class="p-2 text-slate-400 hover:text-white glass-button rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Customer Profile</h1>
                <p class="text-slate-400 font-medium mt-1">Manage your default shipping addresses, phone, and account credentials.</p>
            </div>

            <!-- Profile forms -->
            <div class="space-y-6">
                <div class="glass-card p-6 sm:p-10 border-white/10 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-tr from-white/[0.01] to-transparent pointer-events-none"></div>
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-2xl relative z-10"
                    />
                </div>

                <div class="glass-card p-6 sm:p-10 border-white/10 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-tr from-white/[0.01] to-transparent pointer-events-none"></div>
                    <UpdatePasswordForm class="max-w-2xl relative z-10" />
                </div>

                <div class="glass-card p-6 sm:p-10 border-white/10 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-tr from-white/[0.01] to-transparent pointer-events-none"></div>
                    <DeleteUserForm class="max-w-2xl relative z-10" />
                </div>
            </div>
        </main>

        <WishlistDrawer :show="showWishlistDrawer" @close="showWishlistDrawer = false" />
    </div>
</template>
