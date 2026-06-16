<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8 selection:bg-indigo-500/30 selection:text-indigo-200">
        <!-- Floating Login Card -->
        <div class="glass-card glass-card-hover w-full max-w-md p-8 sm:p-10 rounded-3xl relative overflow-hidden">
            <!-- Subtle internal glow/reflection overlay -->
            <div class="absolute inset-0 bg-gradient-to-tr from-white/[0.02] to-transparent pointer-events-none"></div>

            <!-- Brand Header -->
            <div class="flex flex-col items-center mb-8 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/30 mb-4 border border-white/10">
                    <!-- Stylized Hanger Icon representing Garments/Clothing -->
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a3 3 0 00-3 3v1h6V7a3 3 0 00-3-3zM3 19a2 2 0 002 2h14a2 2 0 002-2M5 11h14l1 8H4l1-8z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white mb-1.5">Welcome Back</h1>
                <p class="text-sm text-slate-400 font-medium">Access your garment collections & orders</p>
            </div>

            <!-- Status Message -->
            <div v-if="status" class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-sm font-medium text-emerald-400">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-5 relative z-10">
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
                            autofocus
                            placeholder="name@company.com"
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-2 text-rose-400 text-xs" :message="form.errors.email" />
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex justify-between items-center mb-2 ml-1">
                        <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Password</label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs font-medium text-indigo-400 hover:text-indigo-300 hover:underline transition-colors duration-200"
                        >
                            Forgot?
                        </Link>
                    </div>
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
                            autocomplete="current-password"
                        />
                    </div>
                    <InputError class="mt-2 text-rose-400 text-xs" :message="form.errors.password" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center ml-1">
                    <label class="relative flex items-center cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            v-model="form.remember"
                            class="sr-only peer"
                        />
                        <div class="w-9 h-5 bg-white/5 border border-white/10 rounded-full peer peer-focus:ring-2 peer-focus:ring-indigo-500/50 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-slate-300 after:border-gray-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-indigo-600/80 peer-checked:after:bg-white peer-checked:after:border-white"></div>
                        <span class="ml-3 text-sm text-slate-300 font-medium">Keep me signed in</span>
                    </label>
                </div>

                <!-- Action Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="glass-button-primary w-full flex justify-center items-center py-3.5 text-base font-semibold transition-all duration-300 ease-in-out-cubic hover:scale-[1.02] active:scale-[0.98]"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="inline-block animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                        Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
