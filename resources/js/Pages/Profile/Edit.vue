<script setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile - ESTY" />

    <div class="min-h-screen relative overflow-hidden pb-20 selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Floating Header/Navigation (Dynamic Island Style) -->
        <div class="fixed top-6 left-0 right-0 z-40 flex justify-center px-4">
            <nav class="glass-card px-6 py-3.5 w-full max-w-4xl flex items-center justify-between shadow-[0_12px_40px_0_rgba(0,0,0,0.3)] rounded-full border-white/10">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2">
                    <span class="text-white font-bold tracking-tight text-lg">ESTY</span>
                </Link>

                <!-- Main Nav Links -->
                <div class="hidden md:flex items-center gap-7">
                    <Link href="/" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Home</Link>
                    <Link href="/#shop" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Shop</Link>
                    <Link :href="route('projects.index')" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">Products</Link>
                    <Link v-if="$page.props.auth.user" :href="route('orders.index')" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">My Orders</Link>
                </div>

                <!-- Auth Navigation -->
                <div class="flex items-center gap-3">
                    <template v-if="$page.props.auth.user">
                        <Link
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('dashboard')"
                            class="glass-button text-xs py-2 px-4 rounded-full border-white/10"
                        >
                            Admin Dashboard
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="text-xs font-semibold text-rose-400 hover:text-rose-300 px-3 py-2 transition-colors duration-200"
                        >
                            Log Out
                        </Link>
                    </template>
                </div>
            </nav>
        </div>

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
    </div>
</template>
