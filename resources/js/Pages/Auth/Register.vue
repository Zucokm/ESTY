<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8 selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Floating Register Card -->
        <div class="glass-card glass-card-hover w-full max-w-md p-8 sm:p-10 rounded-3xl relative overflow-hidden">
            <!-- Subtle internal glow/reflection overlay -->
            <div class="absolute inset-0 bg-gradient-to-tr from-white/[0.02] to-transparent pointer-events-none"></div>

            <!-- Brand Header -->
            <div class="flex flex-col items-center mb-8 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/30 mb-4 border border-white/10">
                    <!-- Stylized Hanger Icon representing Garments/Clothing -->
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white mb-1.5">Create Account</h1>
                <p class="text-sm text-slate-400 font-medium">Join our premium garment shopping community</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5 relative z-10">
                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Full Name</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                        <input
                            id="name"
                            type="text"
                            class="glass-input pl-11"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="John Doe"
                            autocomplete="name"
                        />
                    </div>
                    <InputError class="mt-2 text-rose-400 text-xs" :message="form.errors.name" />
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </span>
                        <input
                            id="email"
                            type="email"
                            class="glass-input pl-11"
                            v-model="form.email"
                            required
                            placeholder="name@company.com"
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-2 text-rose-400 text-xs" :message="form.errors.email" />
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <input
                            id="password"
                            type="password"
                            class="glass-input pl-11"
                            v-model="form.password"
                            required
                            placeholder="••••••••"
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError class="mt-2 text-rose-400 text-xs" :message="form.errors.password" />
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Confirm Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <input
                            id="password_confirmation"
                            type="password"
                            class="glass-input pl-11"
                            v-model="form.password_confirmation"
                            required
                            placeholder="••••••••"
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError class="mt-2 text-rose-400 text-xs" :message="form.errors.password_confirmation" />
                </div>

                <!-- Footer Links & Actions -->
                <div class="pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <Link
                        :href="route('login')"
                        class="text-sm font-medium text-slate-400 hover:text-white transition-colors duration-200 text-center sm:text-left hover:underline"
                    >
                        Already registered?
                    </Link>

                    <button
                        type="submit"
                        class="glass-button-primary px-8 py-3.5 flex justify-center items-center text-base font-semibold transition-all duration-300 ease-in-out-cubic hover:scale-[1.02] active:scale-[0.98]"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="inline-block animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
