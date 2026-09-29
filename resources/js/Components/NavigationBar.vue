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
const isMobileMenuOpen = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    if (isMobileMenuOpen.value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
    document.body.style.overflow = '';
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Initial check
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="fixed top-6 left-0 right-0 z-40 flex justify-center px-4">
        <nav 
            class="glass-card flex items-center justify-between rounded-full border transition-all duration-500 ease-in-out w-full max-w-5xl"
            :class="[
                isScrolled 
                    ? 'py-2.5 px-6 bg-slate-950/85 shadow-[0_24px_50px_rgba(0,0,0,0.6)] border-white/15 backdrop-blur-3xl scale-[0.98]' 
                    : 'py-3.5 px-7 bg-slate-950/30 shadow-[0_12px_40px_rgba(0,0,0,0.3)] border-white/10 backdrop-blur-xl'
            ]"
        >
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2 group focus:outline-none">
                <span class="text-white font-bold tracking-wider text-lg hover:scale-105 transition-transform duration-200">ESTY</span>
            </Link>

            <!-- Main Nav Links (Desktop) -->
            <div class="hidden md:flex items-center gap-8">
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

                <Link 
                    :href="route('contact')" 
                    class="relative group py-1 text-sm font-semibold transition-colors duration-300"
                    :class="[active === 'contact' ? 'text-white' : 'text-slate-400 hover:text-white']"
                >
                    Contact
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-indigo-400 transition-all duration-300" 
                        :class="[active === 'contact' ? 'w-full' : 'w-0 group-hover:w-full']"
                    ></span>
                </Link>

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

                <Link 
                    v-if="$page.props.auth.user" 
                    :href="route('orders.index')" 
                    class="relative group py-1 text-sm font-semibold transition-colors duration-300"
                    :class="[active === 'orders' ? 'text-white' : 'text-slate-400 hover:text-white']"
                >
                    My Orders
                    <span v-if="$page.props.customer_unread_returns > 0" class="absolute -top-2 -right-3 w-4 h-4 flex items-center justify-center bg-rose-500 text-white text-[9px] font-bold rounded-full">{{ $page.props.customer_unread_returns }}</span>
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-indigo-400 transition-all duration-300" 
                        :class="[active === 'orders' ? 'w-full' : 'w-0 group-hover:w-full']"
                    ></span>
                </Link>
            </div>

            <!-- Auth Navigation (Desktop) & Hamburger (Mobile) -->
            <div class="flex items-center gap-2 sm:gap-3">
                <template v-if="canLogin">
                    <template v-if="$page.props.auth.user">
                        <Link
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('dashboard')"
                            class="hidden sm:inline-flex glass-button text-xs py-2 px-4.5 rounded-full border-white/10 hover:border-white/20 hover:bg-white/[0.05] transition-all duration-300"
                        >
                            Admin Dashboard
                        </Link>
                        <Link
                            :href="route('profile.edit')"
                            class="hidden sm:inline-flex glass-button text-xs py-2 px-4.5 rounded-full border-white/10 hover:border-white/20 hover:bg-white/[0.05] transition-all duration-300"
                            :class="{ 'border-indigo-500/30 text-indigo-300 bg-indigo-500/5': active === 'profile' }"
                        >
                            Profile
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="hidden sm:inline-flex text-xs font-semibold text-rose-400 hover:text-rose-300 px-3.5 py-2 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
                        >
                            Log Out
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="hidden sm:inline-flex text-xs font-semibold text-slate-300 hover:text-white px-3 py-2 transition-colors duration-200"
                        >
                            Sign In
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="hidden sm:inline-flex glass-button-primary text-xs py-2.5 px-4.5 rounded-full border-white/15 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
                        >
                            Register
                        </Link>
                    </template>
                </template>

                <!-- Mobile Menu Button -->
                <button 
                    @click="toggleMobileMenu" 
                    class="md:hidden text-slate-300 hover:text-white focus:outline-none p-2 relative z-50 transition-transform active:scale-95"
                >
                    <svg v-if="!isMobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </nav>
    </div>

    <!-- Mobile Full Screen Menu -->
    <Transition
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="opacity-0 translate-y-[-20px]"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-[-20px]"
    >
        <div v-if="isMobileMenuOpen" class="fixed inset-0 z-30 bg-slate-950/95 backdrop-blur-3xl flex flex-col justify-center items-center md:hidden">
            <div class="flex flex-col items-center gap-8 w-full px-6">
                <Link @click="closeMobileMenu" href="/" class="text-2xl font-black tracking-widest text-white hover:text-indigo-400 transition-colors">HOME</Link>
                <Link @click="closeMobileMenu" :href="route('products.index')" class="text-2xl font-black tracking-widest text-white hover:text-indigo-400 transition-colors">PRODUCTS</Link>
                <Link @click="closeMobileMenu" :href="route('contact')" class="text-2xl font-black tracking-widest text-white hover:text-indigo-400 transition-colors">CONTACT</Link>
                <button 
                    @click="() => { emit('open-wishlist'); closeMobileMenu(); }" 
                    class="text-2xl font-black tracking-widest text-white hover:text-rose-400 transition-colors flex items-center gap-3"
                >
                    WISHLIST 
                    <span v-if="wishlistCount > 0" class="px-3 py-1 rounded-full text-sm bg-rose-500/20 text-rose-400">{{ wishlistCount }}</span>
                </button>
                <Link v-if="$page.props.auth.user" @click="closeMobileMenu" :href="route('orders.index')" class="text-2xl font-black tracking-widest text-white hover:text-indigo-400 transition-colors">MY ORDERS</Link>

                <div class="w-full h-px bg-white/10 my-4 max-w-xs mx-auto"></div>

                <template v-if="$page.props.auth.user">
                    <Link v-if="$page.props.auth.user.role === 'admin'" @click="closeMobileMenu" :href="route('dashboard')" class="text-lg font-bold text-slate-300 hover:text-white">Admin Dashboard</Link>
                    <Link @click="closeMobileMenu" :href="route('profile.edit')" class="text-lg font-bold text-slate-300 hover:text-white">Profile Settings</Link>
                    <Link @click="closeMobileMenu" :href="route('logout')" method="post" as="button" class="text-lg font-bold text-rose-400 hover:text-rose-300 mt-2">Log Out</Link>
                </template>
                <template v-else>
                    <Link @click="closeMobileMenu" :href="route('login')" class="glass-button-primary w-full max-w-xs py-4 rounded-2xl text-center font-bold text-lg">Sign In</Link>
                    <Link @click="closeMobileMenu" :href="route('register')" class="text-lg font-bold text-slate-300 hover:text-white">Create an Account</Link>
                </template>
            </div>
        </div>
    </Transition>
</template>
