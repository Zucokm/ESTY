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
