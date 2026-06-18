<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const email = ref('');
const subscribed = ref(false);

const handleSubscribe = (e) => {
    e.preventDefault();
    if (email.value.trim()) {
        subscribed.value = true;
        email.value = '';
        setTimeout(() => {
            subscribed.value = false;
        }, 5000);
    }
};
</script>

<template>
    <footer class="relative mt-20 border-t border-white/[0.06] bg-black/40 backdrop-blur-md selection:bg-indigo-500/30 selection:text-indigo-200">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-indigo-500/[0.005] to-indigo-500/[0.02] pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-6 py-16 relative z-10">
            <!-- Main Grid layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 lg:gap-8 mb-12">
                
                <!-- Brand Info Block -->
                <div class="lg:col-span-2 space-y-5">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-md shadow-indigo-500/20">
                            <span class="text-white font-bold text-sm">E</span>
                        </div>
                        <span class="text-white font-extrabold tracking-widest text-lg">ESTY</span>
                    </div>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed max-w-xs">
                        Crafting premium garments for the modern curator. Experience tailoring excellence, sustainable materials, and contemporary design.
                    </p>
                    
                    <!-- Social icons -->
                    <div class="flex items-center gap-3 pt-2">
                        <a href="#" class="w-8 h-8 rounded-full bg-white/[0.03] hover:bg-white/[0.08] border border-white/5 flex items-center justify-center text-slate-400 hover:text-white transition-all duration-200">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                            </svg>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-white/[0.03] hover:bg-white/[0.08] border border-white/5 flex items-center justify-center text-slate-400 hover:text-white transition-all duration-200">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                            </svg>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-white/[0.03] hover:bg-white/[0.08] border border-white/5 flex items-center justify-center text-slate-400 hover:text-white transition-all duration-200">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Navigation Columns -->
                <div class="space-y-4">
                    <h3 class="text-white font-bold text-xs uppercase tracking-wider">Catalog</h3>
                    <ul class="space-y-2.5 text-xs sm:text-sm">
                        <li><Link href="/" class="text-slate-400 hover:text-indigo-400 transition-colors duration-200">New Arrivals</Link></li>
                        <li><Link href="/" class="text-slate-400 hover:text-indigo-400 transition-colors duration-200">Best Sellers</Link></li>
                        <li><Link href="/" class="text-slate-400 hover:text-indigo-400 transition-colors duration-200">Garments</Link></li>
                        <li><Link href="/" class="text-slate-400 hover:text-indigo-400 transition-colors duration-200">Collections</Link></li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h3 class="text-white font-bold text-xs uppercase tracking-wider">Service</h3>
                    <ul class="space-y-2.5 text-xs sm:text-sm">
                        <li><Link :href="route('orders.index')" class="text-slate-400 hover:text-indigo-400 transition-colors duration-200">Track Order</Link></li>
                        <li><a href="#" class="text-slate-400 hover:text-indigo-400 transition-colors duration-200">Returns & Exchanges</a></li>
                        <li><a href="#" class="text-slate-400 hover:text-indigo-400 transition-colors duration-200">Shipping Policy</a></li>
                        <li><a href="#" class="text-slate-400 hover:text-indigo-400 transition-colors duration-200">Size Guide</a></li>
                    </ul>
                </div>

                <!-- Newsletter signup -->
                <div class="space-y-4">
                    <h3 class="text-white font-bold text-xs uppercase tracking-wider">Newsletter</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Subscribe to receive early catalog access updates, releases, and seasonal events notifications.
                    </p>
                    <form @submit="handleSubscribe" class="space-y-2.5">
                        <div class="relative">
                            <input 
                                v-model="email"
                                type="email" 
                                placeholder="Your email address" 
                                required
                                class="w-full px-4 py-2.5 bg-white/[0.03] border border-white/10 rounded-xl text-slate-200 placeholder-slate-500 text-xs focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all"
                            />
                        </div>
                        <button 
                            type="submit" 
                            class="w-full py-2.5 bg-white text-black hover:bg-slate-200 active:scale-[0.98] rounded-xl text-xs font-bold tracking-wide transition-all shadow-md shadow-white/5 cursor-pointer"
                        >
                            Subscribe
                        </button>
                    </form>
                    <div 
                        v-if="subscribed" 
                        class="text-[10px] text-emerald-400 font-semibold animate-pulse"
                    >
                        ✓ Thank you! You've successfully subscribed.
                    </div>
                </div>

            </div>

            <!-- Bottom Section -->
            <div class="pt-8 border-t border-white/[0.06] flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Copy -->
                <div class="text-[11px] text-slate-500 font-medium">
                    &copy; {{ new Date().getFullYear() }} ESTY Garments Studio. All rights reserved.
                </div>
                
                <!-- Payment Badges -->
                <div class="flex items-center gap-3 opacity-40 hover:opacity-60 transition-opacity duration-300">
                    <!-- Visa -->
                    <svg class="w-8 h-5" viewBox="0 0 24 15" fill="currentColor">
                        <rect width="24" height="15" rx="2" fill="white" fill-opacity="0.05" />
                        <path d="M9.73 10.3l1.1-2.83h.03l.64 2.83H9.73zm4.56-4.14c-.2-.08-.73-.17-1.28-.17-1.4 0-2.39.73-2.4 1.78-.01.78.7 1.21 1.24 1.47.55.26.73.43.73.66 0 .35-.43.51-.83.51-.55 0-.96-.08-1.47-.3l-.2-.1-.22 1.34c.37.17 1.05.31 1.76.32 1.48 0 2.45-.72 2.47-1.84.02-.61-.37-1.08-1.17-1.46-.48-.24-.77-.4-.77-.65 0-.22.25-.45.79-.45.44-.01.76.09.99.19l.12.06.21-1.31zm3.87 4.14L19.4 6.27h-1.03c-.32 0-.59.18-.71.48l-2.5 5.86h1.66l.33-.91h2.02l.19.91h1.49zm-11.83 0L7.9 6.27H6.28L4.66 9.87l-.17-.86A1.66 1.66 0 002.9 7.82L1.33 7.3l.02.1 2.37 5.21h1.66l2.48-5.86h-1.49v.03z" />
                    </svg>
                    <!-- Mastercard -->
                    <svg class="w-8 h-5" viewBox="0 0 24 15" fill="currentColor">
                        <rect width="24" height="15" rx="2" fill="white" fill-opacity="0.05" />
                        <circle cx="10" cy="7.5" r="4.5" fill="#EB001B" />
                        <circle cx="14" cy="7.5" r="4.5" fill="#F79E1B" fill-opacity="0.8" />
                    </svg>
                    <!-- Apple Pay -->
                    <svg class="w-8 h-5" viewBox="0 0 24 15" fill="currentColor">
                        <rect width="24" height="15" rx="2" fill="white" fill-opacity="0.05" />
                        <path d="M6.1 9.4c-.6.9-1.2 1.4-1.9 1.4-.7 0-1-.4-1.8-.4s-1.1.4-1.8.4c-.7 0-1.3-.5-1.9-1.4C-1.9 7-1.4 4.5.2 4.5c.7 0 1.2.4 1.7.4.4 0 .9-.4 1.7-.4.6 0 1.2.4 1.6 1.1C4 6.2 3.6 8 4.7 8.9c.7.7 1.3.4 1.4.5M4 3c0-.8.6-1.5 1.4-1.5.1 0 .2 0 .2.1 0 .8-.6 1.5-1.4 1.5-.1 0-.2 0-.2-.1" transform="translate(9, 2)" />
                    </svg>
                </div>
            </div>
        </div>
    </footer>
</template>
