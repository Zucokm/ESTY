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
const isUserMenuOpen = ref(false);
const userMenuRef = ref(null);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

const toggleUserMenu = () => {
    isUserMenuOpen.value = !isUserMenuOpen.value;
};

const closeUserMenu = () => {
    isUserMenuOpen.value = false;
};

const handleClickOutside = (e) => {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        isUserMenuOpen.value = false;
    }
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
    document.addEventListener('click', handleClickOutside);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', handleClickOutside);
    document.body.style.overflow = '';
});
</script>

<template>
    <div 
        class="fixed left-0 right-0 z-40 flex justify-center px-3 sm:px-6 pointer-events-none transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
        :class="[isScrolled ? 'top-3 sm:top-4' : 'top-4 sm:top-6']"
    >
        <!-- Apple Liquid Glass Responsive Morphing Nav -->
        <nav 
            class="pointer-events-auto relative flex items-center justify-between rounded-full border transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] transform-gpu"
            :class="[
                isScrolled 
                    ? 'w-full max-w-2xl lg:max-w-3xl py-2.5 px-4 sm:px-6 bg-black/80 backdrop-blur-3xl border-white/[0.18] shadow-[0_25px_60px_rgba(0,0,0,0.8),inset_0_1px_1px_rgba(255,255,255,0.18)]' 
                    : 'w-full max-w-5xl py-3.5 sm:py-4 px-6 sm:px-8 bg-slate-950/45 backdrop-blur-2xl border-white/[0.14] shadow-[0_20px_50px_rgba(0,0,0,0.4),inset_0_1px_1.5px_rgba(255,255,255,0.22),inset_0_-1px_1px_rgba(255,255,255,0.05)] hover:border-white/25 hover:bg-slate-950/55'
            ]"
        >
            <!-- 1. Left: Brand Logo -->
            <Link href="/" class="flex items-center gap-2.5 group focus:outline-none pl-1 shrink-0">
                <span 
                    class="rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.85)] animate-pulse transition-all duration-500"
                    :class="[isScrolled ? 'w-2 h-2' : 'w-2.5 h-2.5']"
                ></span>
                <span 
                    class="text-white font-black tracking-widest group-hover:text-indigo-300 transition-all duration-500"
                    :class="[isScrolled ? 'text-base sm:text-lg' : 'text-lg sm:text-xl']"
                >
                    ESTY
                </span>
            </Link>

            <!-- 2. Center: Navigation Links (Clean, frameless Apple typography with silky smooth spacing) -->
            <div 
                class="hidden md:flex items-center transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
                :class="[isScrolled ? 'gap-5 lg:gap-6' : 'gap-7 lg:gap-9']"
            >
                <Link 
                    href="/" 
                    class="relative py-1 text-sm font-semibold tracking-wide transition-colors duration-200 group"
                    :class="[active === 'home' ? 'text-white font-bold' : 'text-slate-300 hover:text-white']"
                >
                    Home
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-full transition-all duration-300" 
                        :class="[active === 'home' ? 'w-full shadow-[0_0_8px_rgba(129,140,248,0.8)]' : 'w-0 group-hover:w-full']"
                    ></span>
                </Link>

                <a 
                    href="/#shop" 
                    class="relative py-1 text-sm font-semibold tracking-wide transition-colors duration-200 group"
                    :class="[active === 'shop' ? 'text-white font-bold' : 'text-slate-300 hover:text-white']"
                >
                    Shop
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-full transition-all duration-300" 
                        :class="[active === 'shop' ? 'w-full shadow-[0_0_8px_rgba(129,140,248,0.8)]' : 'w-0 group-hover:w-full']"
                    ></span>
                </a>

                <Link 
                    :href="route('products.index')" 
                    class="relative py-1 text-sm font-semibold tracking-wide transition-colors duration-200 group"
                    :class="[active === 'products' ? 'text-white font-bold' : 'text-slate-300 hover:text-white']"
                >
                    Products
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-full transition-all duration-300" 
                        :class="[active === 'products' ? 'w-full shadow-[0_0_8px_rgba(129,140,248,0.8)]' : 'w-0 group-hover:w-full']"
                    ></span>
                </Link>

                <Link 
                    :href="route('contact')" 
                    class="relative py-1 text-sm font-semibold tracking-wide transition-colors duration-200 group"
                    :class="[active === 'contact' ? 'text-white font-bold' : 'text-slate-300 hover:text-white']"
                >
                    Contact
                    <span 
                        class="absolute bottom-0 left-0 h-0.5 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-full transition-all duration-300" 
                        :class="[active === 'contact' ? 'w-full shadow-[0_0_8px_rgba(129,140,248,0.8)]' : 'w-0 group-hover:w-full']"
                    ></span>
                </Link>
            </div>

            <!-- 3. Right: Utility Icons (Clean, frameless Apple buttons) -->
            <div 
                class="flex items-center transition-all duration-500"
                :class="[isScrolled ? 'gap-1.5 sm:gap-2' : 'gap-2 sm:gap-3']"
            >
                <!-- Wishlist Icon Button -->
                <button 
                    type="button"
                    @click="emit('open-wishlist')" 
                    class="relative p-2 rounded-full text-slate-300 hover:text-white hover:bg-white/10 transition-colors duration-200 focus:outline-none"
                    title="Wishlist"
                >
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span 
                        v-if="wishlistCount > 0" 
                        class="absolute -top-1 -right-1 w-4 h-4 flex items-center justify-center bg-rose-500 text-white text-[9px] font-black rounded-full shadow-md shadow-rose-500/50 animate-pulse"
                    >
                        {{ wishlistCount }}
                    </span>
                </button>

                <!-- My Orders Link (with Unread Badge) -->
                <Link 
                    v-if="$page.props.auth.user" 
                    :href="route('orders.index')" 
                    class="relative p-2 rounded-full text-slate-300 hover:text-white hover:bg-white/10 transition-colors duration-200 focus:outline-none"
                    :class="[active === 'orders' ? 'text-white bg-white/10' : '']"
                    title="My Orders"
                >
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span 
                        v-if="$page.props.customer_unread_returns > 0" 
                        class="absolute -top-1 -right-1 w-4 h-4 flex items-center justify-center bg-rose-500 text-white text-[9px] font-black rounded-full shadow-md shadow-rose-500/50 animate-pulse"
                    >
                        {{ $page.props.customer_unread_returns }}
                    </span>
                </Link>

                <!-- User Profile / Account Dynamic Capsule -->
                <div v-if="canLogin" class="relative" ref="userMenuRef">
                    <template v-if="$page.props.auth.user">
                        <!-- User Capsule Trigger -->
                        <button 
                            type="button"
                            @click="toggleUserMenu"
                            class="flex items-center gap-1.5 pl-1.5 pr-2.5 py-1 rounded-full bg-white/[0.06] hover:bg-white/[0.12] border border-white/10 transition-all duration-200 focus:outline-none"
                        >
                            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-black shadow-sm shrink-0">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <span class="text-xs font-bold text-slate-200 max-w-[80px] sm:max-w-[100px] truncate hidden sm:inline">
                                {{ $page.props.auth.user.name }}
                            </span>
                            <svg class="w-3 h-3 text-slate-400 transition-transform duration-200 shrink-0" :class="{ 'rotate-180': isUserMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dynamic Island Expansion Dropdown -->
                        <Transition
                            enter-active-class="transition-all duration-250 ease-out"
                            enter-from-class="opacity-0 translate-y-2 scale-95"
                            enter-to-class="opacity-100 translate-y-0 scale-100"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-from-class="opacity-100 translate-y-0 scale-100"
                            leave-to-class="opacity-0 translate-y-2 scale-95"
                        >
                            <div 
                                v-if="isUserMenuOpen" 
                                class="absolute right-0 mt-3 w-56 rounded-2xl bg-black/90 backdrop-blur-2xl border border-white/[0.12] shadow-[0_20px_50px_rgba(0,0,0,0.8),0_0_0_1px_rgba(255,255,255,0.06)] p-2 space-y-1 z-50 overflow-hidden"
                            >
                                <div class="px-3 py-2 border-b border-white/[0.06] mb-1">
                                    <p class="text-xs font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ $page.props.auth.user.email }}</p>
                                </div>

                                <Link 
                                    v-if="$page.props.auth.user.role === 'admin'"
                                    :href="route('dashboard')"
                                    @click="closeUserMenu"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-indigo-300 hover:text-white hover:bg-indigo-600/20 transition-colors"
                                >
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" /></svg>
                                    <span>Admin Dashboard</span>
                                </Link>

                                <Link 
                                    :href="route('orders.index')"
                                    @click="closeUserMenu"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/[0.06] transition-colors"
                                >
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                    <span>My Orders</span>
                                </Link>

                                <Link 
                                    :href="route('profile.edit')"
                                    @click="closeUserMenu"
                                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/[0.06] transition-colors"
                                >
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <span>Profile Settings</span>
                                </Link>

                                <div class="h-px bg-white/[0.06] my-1"></div>

                                <Link 
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    @click="closeUserMenu"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors text-left"
                                >
                                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    <span>Log Out</span>
                                </Link>
                            </div>
                        </Transition>
                    </template>

                    <!-- Guest Mode: Sign In / Register -->
                    <template v-else>
                        <div class="flex items-center gap-1 sm:gap-2">
                            <Link
                                :href="route('login')"
                                class="text-xs font-bold text-slate-300 hover:text-white px-2.5 sm:px-3 py-1.5 transition-colors"
                            >
                                Sign In
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-white text-slate-950 hover:bg-slate-200 shadow-md shadow-white/10 transition-all active:scale-95"
                            >
                                Register
                            </Link>
                        </div>
                    </template>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <button 
                    @click="toggleMobileMenu" 
                    class="md:hidden p-1.5 rounded-full text-slate-300 hover:text-white hover:bg-white/10 focus:outline-none transition-transform active:scale-90"
                    title="Menu"
                >
                    <svg v-if="!isMobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="flex flex-col items-center gap-7 w-full px-6">
                <Link @click="closeMobileMenu" href="/" class="text-2xl font-black tracking-widest text-white hover:text-indigo-400 transition-colors">HOME</Link>
                <a @click="closeMobileMenu" href="/#shop" class="text-2xl font-black tracking-widest text-white hover:text-indigo-400 transition-colors">SHOP</a>
                <Link @click="closeMobileMenu" :href="route('products.index')" class="text-2xl font-black tracking-widest text-white hover:text-indigo-400 transition-colors">PRODUCTS</Link>
                <Link @click="closeMobileMenu" :href="route('contact')" class="text-2xl font-black tracking-widest text-white hover:text-indigo-400 transition-colors">CONTACT</Link>
                
                <button 
                    @click="() => { emit('open-wishlist'); closeMobileMenu(); }" 
                    class="text-2xl font-black tracking-widest text-white hover:text-rose-400 transition-colors flex items-center gap-3"
                >
                    WISHLIST 
                    <span v-if="wishlistCount > 0" class="px-3 py-1 rounded-full text-sm bg-rose-500/20 text-rose-400">{{ wishlistCount }}</span>
                </button>
                <Link v-if="$page.props.auth.user" @click="closeMobileMenu" :href="route('orders.index')" class="text-2xl font-black tracking-widest text-white hover:text-indigo-400 transition-colors flex items-center gap-3">
                    MY ORDERS
                    <span v-if="$page.props.customer_unread_returns > 0" class="px-2.5 py-0.5 rounded-full text-xs bg-rose-500 text-white font-bold">{{ $page.props.customer_unread_returns }} new</span>
                </Link>

                <div class="w-full h-px bg-white/10 my-3 max-w-xs mx-auto"></div>

                <template v-if="$page.props.auth.user">
                    <Link v-if="$page.props.auth.user.role === 'admin'" @click="closeMobileMenu" :href="route('dashboard')" class="text-lg font-bold text-indigo-400 hover:text-indigo-300">Admin Dashboard</Link>
                    <Link @click="closeMobileMenu" :href="route('profile.edit')" class="text-lg font-bold text-slate-300 hover:text-white">Profile Settings</Link>
                    <Link @click="closeMobileMenu" :href="route('logout')" method="post" as="button" class="text-lg font-bold text-rose-400 hover:text-rose-300 mt-2">Log Out</Link>
                </template>
                <template v-else>
                    <Link @click="closeMobileMenu" :href="route('login')" class="glass-button-primary w-full max-w-xs py-3.5 rounded-2xl text-center font-bold text-base">Sign In</Link>
                    <Link @click="closeMobileMenu" :href="route('register')" class="text-base font-bold text-slate-300 hover:text-white">Create an Account</Link>
                </template>
            </div>
        </div>
    </Transition>
</template>
