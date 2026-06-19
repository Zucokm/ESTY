<script setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import NavigationBar from '@/Components/NavigationBar.vue';
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
</script>

<template>
    <Head title="Profile - ESTY" />

    <div class="min-h-screen relative overflow-hidden pb-20 selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Floating Header/Navigation -->
        <NavigationBar 
            active="profile" 
            @open-wishlist="showWishlistDrawer = true" 
        />

        <!-- Body Area -->
        <main class="pt-32 px-4 max-w-4xl mx-auto space-y-8 relative z-10">
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
