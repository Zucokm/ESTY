<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useWishlistStore } from '@/Stores/wishlistStore';

const props = defineProps({
    canLogin: {
        type: Boolean,
        default: true
    },
    canRegister: {
        type: Boolean,
        default: true
    },
    active: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['open-wishlist']);

const { wishlistCount } = useWishlistStore();
const isScrolled = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Initial check
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="fixed top-6 left-0 right-0 z-40 flex justify-center px-4">
        <nav 
            class="glass-card flex items-center justify-between rounded-full border transition-all duration-500 ease-in-out w-full"
            :class="[
                isScrolled 
                    ? 'py-2.5 px-6 max-w-3xl bg-slate-950/85 shadow-[0_24px_50px_rgba(0,0,0,0.6)] border-white/15 backdrop-blur-3xl scale-[0.98]' 
                    : 'py-3.5 px-7 max-w-4xl bg-slate-950/30 shadow-[0_12px_40px_rgba(0,0,0,0.3)] border-white/10 backdrop-blur-xl'
            ]"
        >
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2 group focus:outline-none">
                <span class="text-white font-bold tracking-wider text-lg hover:scale-105 transition-transform duration-200">ESTY</span>
            </Link>

            <!-- Main Nav Links -->
            <div class="hidden md:flex items-center gap-8">
                <!-- Home Link -->
                <Link 
                    href="/" 
                    class="relative group py-1 text-sm font-semibold transition-colors duration-300"
                    :class="[active === 'home' ? 'text-white' : 'text-slate-400 hover:text-white']"
                >
                    Home
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-indigo-400 transition-all duration-300" 
                        :class="[active === 'home' ? 'w-full' : 'w-0 group-hover:w-full']"
                    ></span>
                </Link>

                <!-- Shop Anchor/Link -->
                <a 
                    href="/#shop" 
                    class="relative group py-1 text-sm font-semibold transition-colors duration-300"
                    :class="[active === 'shop' ? 'text-white' : 'text-slate-400 hover:text-white']"
                >
                    Shop
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-indigo-400 transition-all duration-300" 
                        :class="[active === 'shop' ? 'w-full' : 'w-0 group-hover:w-full']"
                    ></span>
                </a>

                <!-- Products Catalog -->
                <Link 
                    :href="route('products.index')" 
                    class="relative group py-1 text-sm font-semibold transition-colors duration-300"
                    :class="[active === 'products' ? 'text-white' : 'text-slate-400 hover:text-white']"
                >
                    Products
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-indigo-400 transition-all duration-300" 
                        :class="[active === 'products' ? 'w-full' : 'w-0 group-hover:w-full']"
                    ></span>
                </Link>

                <!-- Wishlist Trigger -->
                <button 
                    @click="emit('open-wishlist')" 
                    class="relative group py-1 text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-300 flex items-center gap-1.5 focus:outline-none"
                >
                    Wishlist
                    <span 
                        v-if="wishlistCount > 0" 
                        class="px-2 py-0.5 rounded-full text-[10px] bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold hover:scale-105 transition-transform duration-200"
                    >
                        {{ wishlistCount }}
                    </span>
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-400 group-hover:w-full transition-all duration-300"></span>
                </button>

                <!-- Orders Link -->
                <Link 
                    v-if="$page.props.auth.user" 
                    :href="route('orders.index')" 
                    class="relative group py-1 text-sm font-semibold transition-colors duration-300"
                    :class="[active === 'orders' ? 'text-white' : 'text-slate-400 hover:text-white']"
                >
                    My Orders
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-indigo-400 transition-all duration-300" 
                        :class="[active === 'orders' ? 'w-full' : 'w-0 group-hover:w-full']"
                    ></span>
                </Link>
            </div>

            <!-- Auth Navigation -->
            <div class="flex items-center gap-3">
                <template v-if="canLogin">
                    <template v-if="$page.props.auth.user">
                        <Link
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('dashboard')"
                            class="glass-button text-xs py-2 px-4.5 rounded-full border-white/10 hover:border-white/20 hover:bg-white/[0.05] transition-all duration-300"
                        >
                            Admin Dashboard
                        </Link>
                        <Link
                            :href="route('profile.edit')"
                            class="glass-button text-xs py-2 px-4.5 rounded-full border-white/10 hover:border-white/20 hover:bg-white/[0.05] transition-all duration-300"
                            :class="{ 'border-indigo-500/30 text-indigo-300 bg-indigo-500/5': active === 'profile' }"
                        >
                            Profile
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="text-xs font-semibold text-rose-400 hover:text-rose-300 px-3.5 py-2 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
                        >
                            Log Out
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="text-xs font-semibold text-slate-300 hover:text-white px-3 py-2 transition-colors duration-200"
                        >
                            Sign In
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="glass-button-primary text-xs py-2.5 px-4.5 rounded-full border-white/15 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
                        >
                            Register
                        </Link>
                    </template>
                </template>
            </div>
        </nav>
    </div>
</template>
