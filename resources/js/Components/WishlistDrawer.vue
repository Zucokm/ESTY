<script setup>
import { useWishlistStore } from '@/Stores/wishlistStore';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    }
});

const emit = defineEmits(['close', 'open-product-modal']);

const { wishlist, removeFromWishlist, wishlistCount } = useWishlistStore();
</script>

<template>
    <Transition name="fade-slide">
        <!-- Slide-out Wishlist Drawer -->
        <div 
            v-if="show"
            class="fixed inset-0 z-50 overflow-hidden"
        >
            <!-- Backdrop -->
            <div 
                @click="emit('close')" 
                class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity duration-300"
            ></div>

            <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
                <!-- Drawer Content -->
                <div class="w-screen max-w-md bg-slate-950/40 border-l border-white/10 backdrop-blur-3xl shadow-[0_0_50px_0_rgba(0,0,0,0.6)] flex flex-col justify-between h-full relative z-10 glass-card">
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-white/[0.08] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <h2 class="text-lg font-bold text-white tracking-tight">
                                My Wishlist ({{ wishlistCount }})
                            </h2>
                        </div>
                        <button @click="emit('close')" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Wishlist Items List -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div 
                            v-for="item in wishlist" 
                            :key="item.id"
                            class="p-3.5 rounded-2xl bg-white/[0.02] border border-white/[0.04] flex gap-4 relative group hover:border-white/[0.08] transition-all"
                        >
                            <!-- Thumbnail -->
                            <Link :href="route('products.show', item.slug)" @click="emit('close')" class="w-16 h-20 rounded-xl overflow-hidden bg-slate-900 shrink-0 block">
                                <img v-if="item.images && item.images.length > 0" :src="item.images[0].image_path" class="w-full h-full object-cover object-top" />
                                <div v-else class="w-full h-full flex items-center justify-center bg-indigo-500/10 text-indigo-300 text-xs font-bold uppercase">
                                    {{ item.name.charAt(0) }}
                                </div>
                            </Link>

                            <!-- Details -->
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <Link :href="route('products.show', item.slug)" @click="emit('close')" class="font-bold text-white text-sm tracking-tight line-clamp-1 hover:text-indigo-300 transition-colors">{{ item.name }}</Link>
                                    <span class="block mt-1 text-xs font-extrabold text-slate-300">${{ parseFloat(item.base_price).toFixed(2) }}</span>
                                </div>

                                <!-- Quick actions -->
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="emit('open-product-modal', item)" 
                                        class="text-[10px] font-bold text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500 hover:text-white border border-indigo-500/20 px-2.5 py-1 rounded-md transition-all cursor-pointer"
                                    >
                                        Add to Bag
                                    </button>
                                    <button 
                                        @click="removeFromWishlist(item.id)" 
                                        class="text-[10px] font-bold text-rose-400 bg-rose-500/10 hover:bg-rose-500 hover:text-white border border-rose-500/20 px-2.5 py-1 rounded-md transition-all cursor-pointer"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="wishlist.length === 0" class="h-64 flex flex-col items-center justify-center text-slate-500 text-center gap-3">
                            <svg class="w-10 h-10 stroke-current text-slate-600" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span class="text-sm font-semibold tracking-wide">Your Wishlist is empty.</span>
                        </div>
                    </div>

                    <!-- Footer actions -->
                    <div class="p-6 border-t border-white/[0.08]">
                        <button 
                            @click="emit('close')" 
                            class="glass-button w-full py-3.5 font-bold text-sm rounded-xl text-center cursor-pointer"
                        >
                            Continue Shopping
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-slide-enter-active .glass-card,
.fade-slide-leave-active .glass-card {
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
}

.fade-slide-enter-from .glass-card,
.fade-slide-leave-to .glass-card {
    transform: translateX(100%);
}
</style>
