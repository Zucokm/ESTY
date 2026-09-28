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
                
                <div class="mt-8 pt-6 border-t border-white/[0.08]">
                    <p class="text-xs text-center text-slate-500 font-semibold mb-4 uppercase tracking-wider">Or continue with</p>
                    <div class="flex gap-4">
                        <a :href="route('socialite.redirect', 'google')" class="flex-1 glass-button py-2.5 flex justify-center items-center gap-2 rounded-xl text-sm font-bold text-slate-300 hover:text-white transition-all cursor-pointer hover:bg-white/[0.08]">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            Google
                        </a>
                        <a :href="route('socialite.redirect', 'facebook')" class="flex-1 glass-button py-2.5 flex justify-center items-center gap-2 rounded-xl text-sm font-bold text-slate-300 hover:text-white transition-all cursor-pointer hover:bg-white/[0.08]">
                            <svg class="w-5 h-5 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
