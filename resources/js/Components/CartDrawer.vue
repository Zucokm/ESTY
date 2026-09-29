<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { useCartStore } from '@/Stores/cartStore';
import LoadingOverlay from '@/Components/LoadingOverlay.vue';

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    }
});

const emit = defineEmits(['close', 'order-success']);

const { cart, removeFromCart, updateQuantity, clearCart, cartCount, cartTotal } = useCartStore();
const page = usePage();

const checkoutStep = ref('cart'); // 'cart' or 'checkout'
const shippingAddress = ref(page.props.auth.user?.shipping_address || '');
const township = ref(page.props.auth.user?.township || '');

const phone = ref(page.props.auth.user?.phone || '');
const paymentMethod = ref('cod');

const checkoutForm = useForm({
    shipping_address: '',
    township: '',

    phone: '',
    payment_method: 'cod',
    coupon_code: '',
    items: []
});

const couponCodeInput = ref('');
const appliedCoupon = ref(null);
const couponError = ref('');
const couponSuccess = ref('');

const applyCoupon = async () => {
    couponError.value = '';
    couponSuccess.value = '';
    if (!couponCodeInput.value) return;
    
    try {
        const response = await window.axios.post(route('coupon.apply'), { code: couponCodeInput.value });
        appliedCoupon.value = response.data;
        checkoutForm.coupon_code = response.data.code;
        couponSuccess.value = 'Coupon applied successfully!';
    } catch (error) {
        couponError.value = error.response?.data?.message || 'Invalid coupon code.';
        appliedCoupon.value = null;
        checkoutForm.coupon_code = '';
    }
};

const finalTotal = computed(() => {
    if (!appliedCoupon.value) return cartTotal.value;
    if (appliedCoupon.value.type === 'percent') {
        return cartTotal.value - (cartTotal.value * appliedCoupon.value.value / 100);
    }
    return Math.max(0, cartTotal.value - appliedCoupon.value.value);
});

const handleCheckoutProceed = () => {
    checkoutStep.value = 'checkout';
};

const submitCheckout = () => {
    checkoutForm.items = cart.value.map(item => ({
        product_id: item.product_id,
        variant_id: item.variant_id,
        quantity: item.quantity,
        price: item.price
    }));
    checkoutForm.shipping_address = shippingAddress.value;
    checkoutForm.township = township.value;

    checkoutForm.phone = phone.value;
    checkoutForm.payment_method = paymentMethod.value;

    checkoutForm.post(route('checkout.store'), {
        onSuccess: () => {
            clearCart();
            shippingAddress.value = '';
            phone.value = '';
            checkoutStep.value = 'cart';
            emit('close');
            emit('order-success');
        }
    });
};

const getColorStyle = (colorName) => {
    if (!colorName) return { backgroundColor: '#718093' };
    
    // We map colors to hexes
    const colorMap = {
        oatmeal: '#e5dcd3',
        charcoal: '#2f3542',
        navy: '#1e272e',
        black: '#111111',
        sage: '#a3b19b',
        beige: '#d2b48c',
        khaki: '#c3b091',
        gray: '#718093',
        grey: '#718093',
        white: '#f5f6fa',
        olive: '#57606f',
        rose: '#fda7df',
        red: '#ff7675',
        blue: '#74b9ff',
        green: '#55efc4',
    };
    const norm = colorName.toLowerCase().trim();
    return { backgroundColor: colorMap[norm] || norm };
};
</script>

<template>
    <Transition name="fade-slide">
        <!-- Cart Drawer Backdrop -->
        <div 
            v-if="show" 
            class="fixed inset-0 z-50 overflow-hidden"
        >
        <!-- Blur overlay -->
        <div 
            @click="emit('close')" 
            class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
        ></div>

        <!-- Sidebar container -->
        <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md glass-card h-full flex flex-col shadow-2xl border-l border-white/10 rounded-l-3xl overflow-hidden relative z-10">
                <LoadingOverlay :active="checkoutForm.processing" message="Placing order..." />
                <!-- Header -->
                <div class="px-6 py-5 border-b border-white/[0.06] flex items-center justify-between">
                    <h2 class="text-lg font-black text-white tracking-tight flex items-center gap-2">
                        <span>{{ checkoutStep === 'cart' ? 'Shopping Bag' : 'Shipping Details' }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 font-extrabold">{{ cartCount }}</span>
                    </h2>
                    <button @click="emit('close')" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Step 1: Cart items list -->
                <div v-if="checkoutStep === 'cart'" class="flex-1 overflow-y-auto p-6 space-y-4">
                    <div 
                        v-for="item in cart" 
                        :key="item.variant_id"
                        class="p-3.5 rounded-2xl bg-white/[0.02] border border-white/[0.04] flex gap-4 relative group hover:border-white/[0.08] transition-all"
                    >
                        <!-- Image -->
                        <div class="w-16 h-20 rounded-xl overflow-hidden bg-slate-900 shrink-0">
                            <img v-if="item.image" :src="item.image" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center bg-indigo-500/10 text-indigo-300 text-xs font-bold uppercase">
                                {{ item.name ? item.name.charAt(0) : '' }}
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-white text-sm tracking-tight line-clamp-1">{{ item.name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-block text-[10px] font-bold text-slate-400 bg-white/[0.05] border border-white/[0.08] px-2 py-0.5 rounded-md">
                                        {{ item.size }}
                                    </span>
                                    <span class="inline-block text-[10px] font-bold text-slate-400 bg-white/[0.05] border border-white/[0.08] px-2 py-0.5 rounded-md capitalize">
                                        {{ item.color }}
                                    </span>
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 bg-white/[0.04] border border-white/[0.06] rounded-lg px-2 py-1">
                                    <button @click="updateQuantity(item.variant_id, item.quantity - 1)" class="text-slate-400 hover:text-white font-bold text-xs">-</button>
                                    <span class="text-xs text-white font-bold px-1.5">{{ item.quantity }}</span>
                                    <button @click="updateQuantity(item.variant_id, item.quantity + 1)" class="text-slate-400 hover:text-white font-bold text-xs">+</button>
                                </div>
                                <span class="font-extrabold text-white text-sm whitespace-nowrap shrink-0">{{ (item.price * item.quantity).toLocaleString() }} Ks</span>
                            </div>
                        </div>

                        <!-- Delete button -->
                        <button 
                            @click="removeFromCart(item.variant_id)" 
                            class="absolute top-2 right-2 text-slate-500 hover:text-rose-400 opacity-0 group-hover:opacity-100 transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div v-if="cart.length === 0" class="h-64 flex flex-col items-center justify-center text-slate-500 text-center gap-3">
                        <svg class="w-10 h-10 stroke-current text-slate-600" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span class="text-sm font-semibold tracking-wide">Your Shopping bag is empty.</span>
                    </div>
                </div>

                <!-- Step 2: Checkout details form -->
                <div v-else class="flex-1 overflow-y-auto p-6 space-y-6">
                    <div class="space-y-4">
                        <!-- Shipping Address -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Shipping Address</label>
                            <textarea 
                                v-model="shippingAddress" 
                                rows="3" 
                                class="glass-input text-sm resize-none" 
                                placeholder="Enter your complete home address for delivery..."
                                required
                            ></textarea>
                            <span v-if="checkoutForm.errors.shipping_address" class="text-xs text-rose-400 mt-1 block ml-1">{{ checkoutForm.errors.shipping_address }}</span>
                        </div>

                        <!-- Township -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Township</label>
                            <input 
                                type="text" 
                                v-model="township" 
                                list="townships-list"
                                class="glass-input text-sm" 
                                placeholder="E.g. Bahan, Kamayut..."
                                required
                            />
                            <datalist id="townships-list">
                                <option value="Bahan"></option>
                                <option value="Dagon"></option>
                                <option value="Kamayut"></option>
                                <option value="Hlaing"></option>
                                <option value="Sanchaung"></option>
                                <option value="Yankin"></option>
                                <option value="Tamwe"></option>
                                <option value="South Okkalapa"></option>
                                <option value="North Okkalapa"></option>
                                <option value="Insein"></option>
                                <option value="Thingangyun"></option>
                            </datalist>
                            <span v-if="checkoutForm.errors.township" class="text-xs text-rose-400 mt-1 block ml-1">{{ checkoutForm.errors.township }}</span>
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Phone Number</label>
                            <input 
                                type="text" 
                                v-model="phone" 
                                class="glass-input text-sm" 
                                placeholder="+95 9..."
                                required
                            />
                            <span v-if="checkoutForm.errors.phone" class="text-xs text-rose-400 mt-1 block ml-1">{{ checkoutForm.errors.phone }}</span>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Payment Method</label>
                            <div class="grid grid-cols-1 gap-3">
                                <label 
                                    class="flex flex-col items-center justify-center p-3.5 rounded-2xl border cursor-pointer transition-all text-center gap-1.5"
                                    :class="paymentMethod === 'cod' ? 'bg-indigo-500/10 border-indigo-500/40 text-indigo-300' : 'bg-white/[0.02] border-white/5 text-slate-400 hover:text-white'"
                                >
                                    <input type="radio" value="cod" v-model="paymentMethod" class="sr-only" />
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="text-[11px] font-bold">Cash on Delivery</span>
                                </label>
                            </div>
                            <span v-if="checkoutForm.errors.payment_method" class="text-xs text-rose-400 mt-1 block ml-1">{{ checkoutForm.errors.payment_method }}</span>
                        </div>

                        <!-- Stock Error Flash -->
                        <div v-if="checkoutForm.errors.items" class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs font-semibold text-rose-400 leading-relaxed">
                            {{ checkoutForm.errors.items }}
                        </div>

                        <!-- Coupon Code -->
                        <div class="pt-2">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Promo Code (Optional)</label>
                            
                            <!-- Available Coupons (Click to Apply) -->
                            <div v-if="$page.props.active_coupons?.length" class="mb-3 flex flex-wrap gap-2">
                                <button 
                                    v-for="coupon in $page.props.active_coupons" 
                                    :key="coupon.id"
                                    @click="couponCodeInput = coupon.code; applyCoupon()"
                                    class="px-2.5 py-1 text-[10px] font-bold tracking-wider rounded border border-indigo-500/30 bg-indigo-500/10 text-indigo-300 hover:bg-indigo-500/20 transition-colors uppercase"
                                >
                                    {{ coupon.code }} (-{{ coupon.discount_type === 'percentage' ? coupon.discount_amount + '%' : Number(coupon.discount_amount).toLocaleString() + ' Ks' }})
                                </button>
                            </div>

                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    v-model="couponCodeInput" 
                                    class="glass-input text-sm uppercase flex-1" 
                                    placeholder="Enter code..."
                                />
                                <button type="button" @click="applyCoupon" class="glass-button text-xs font-bold px-4 rounded-xl text-indigo-300 hover:text-white">Apply</button>
                            </div>
                            <span v-if="couponError" class="text-xs text-rose-400 mt-1 block ml-1">{{ couponError }}</span>
                            <span v-if="couponSuccess" class="text-xs text-emerald-400 mt-1 block ml-1">{{ couponSuccess }}</span>
                            <span v-if="checkoutForm.errors.coupon_code" class="text-xs text-rose-400 mt-1 block ml-1">{{ checkoutForm.errors.coupon_code }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Summary & Actions -->
                <div class="p-6 border-t border-white/[0.08] space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-400">Total Price</span>
                        <div class="text-right">
                            <span v-if="appliedCoupon" class="text-sm text-slate-500 line-through mr-2">{{ cartTotal.toLocaleString() }} Ks</span>
                            <span class="text-2xl font-black text-white whitespace-nowrap shrink-0">{{ finalTotal.toLocaleString() }} Ks</span>
                            <div v-if="appliedCoupon" class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">
                                (-{{ appliedCoupon.type === 'percent' ? appliedCoupon.value + '%' : Number(appliedCoupon.value).toLocaleString() + ' Ks' }})
                            </div>
                        </div>
                    </div>

                    <!-- Step 1 Actions -->
                    <div v-if="checkoutStep === 'cart'" class="space-y-2">
                        <template v-if="$page.props.auth.user">
                            <button 
                                @click="handleCheckoutProceed"
                                :disabled="cart.length === 0"
                                class="glass-button-primary w-full py-4 font-bold text-base rounded-2xl flex justify-center items-center shadow-lg shadow-indigo-500/25 disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer"
                            >
                                Proceed to Checkout
                            </button>
                        </template>
                        <template v-else>
                            <Link 
                                :href="route('login')"
                                class="glass-button-primary w-full py-4 font-bold text-base rounded-2xl flex justify-center items-center shadow-lg shadow-indigo-500/25 text-center"
                            >
                                Sign In to Checkout
                            </Link>
                        </template>
                    </div>

                    <!-- Step 2 Actions -->
                    <div v-else class="flex gap-3">
                        <button 
                            @click="checkoutStep = 'cart'" 
                            class="glass-button py-4 px-6 rounded-2xl text-slate-300 font-semibold cursor-pointer"
                        >
                            Back
                        </button>
                        <button 
                            @click="submitCheckout"
                            :disabled="checkoutForm.processing || !shippingAddress || !phone"
                            class="glass-button-primary flex-1 py-4 font-bold text-base rounded-2xl flex justify-center items-center shadow-lg shadow-indigo-500/25 disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer"
                        >
                            <span v-if="checkoutForm.processing" class="inline-block animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                            Place Order
                        </button>
                    </div>
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
