<script setup>
import { ref, computed, watch } from 'vue';
import { useCart } from '@/Composables/useCart';

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    product: {
        type: Object,
        default: null,
    }
});

const emit = defineEmits(['close', 'added-success']);

const { addToCart } = useCart();

const selectedSize = ref('');
const selectedColor = ref('');

// Watch product to reset selections when it changes
watch(() => props.product, (newVal) => {
    if (newVal) {
        const sizes = getUniqueSizes(newVal.variants);
        const colors = getUniqueColors(newVal.variants);
        selectedSize.value = sizes[0] || '';
        selectedColor.value = colors[0] || '';
    }
}, { immediate: true });

const getUniqueSizes = (variants) => {
    if (!variants) return [];
    return [...new Set(variants.map(v => v.size))];
};

const getUniqueColors = (variants) => {
    if (!variants) return [];
    return [...new Set(variants.map(v => v.color))];
};

// Computes the active variant based on size & color choices
const selectedVariant = computed(() => {
    if (!props.product || !selectedSize.value || !selectedColor.value) return null;
    return props.product.variants.find(
        v => v.size === selectedSize.value && v.color === selectedColor.value
    );
});

// Computes the active image for the selection modal based on selected color
const modalProductImage = computed(() => {
    if (!props.product) return null;
    const color = selectedColor.value;
    if (color && props.product.images) {
        const normalizedColor = color.toLowerCase().trim();
        
        // 1. Try to find image with explicit color match first (case-insensitive)
        let matchingImage = props.product.images.find(img => 
            img.color && img.color.toLowerCase().trim() === normalizedColor
        );
        
        // 2. Fallback to substring matching on path
        if (!matchingImage) {
            matchingImage = props.product.images.find(img => 
                img.image_path && img.image_path.toLowerCase().includes(normalizedColor)
            );
        }
        
        if (matchingImage) return matchingImage.image_path;
        
        // 3. Fallback to index-based matching
        const colorsList = getUniqueColors(props.product.variants);
        const colorIndex = colorsList.indexOf(color);
        if (colorIndex !== -1 && props.product.images[colorIndex]) {
            return props.product.images[colorIndex].image_path;
        }
    }
    return props.product.images && props.product.images.length > 0 ? props.product.images[0].image_path : null;
});

// Calculate variant-specific price (base + additional)
const variantPrice = computed(() => {
    if (!props.product) return 0;
    const base = parseFloat(props.product.base_price);
    const add = selectedVariant.value ? parseFloat(selectedVariant.value.additional_price) : 0;
    return base + add;
});

const handleAddToCart = () => {
    if (props.product && selectedVariant.value) {
        addToCart(props.product, selectedVariant.value, 1, modalProductImage.value);
        emit('close');
        emit('added-success');
    }
};

const getColorStyle = (colorName) => {
    if (!colorName) return { backgroundColor: '#718093' };
    
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
    <!-- Product Variant Selection Modal -->
    <div 
        v-if="show && product" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <!-- Backdrop Overlay -->
        <div @click="emit('close')" class="absolute inset-0 bg-black/50 backdrop-blur-md"></div>
        
        <!-- Modal Body -->
        <div class="glass-card w-full max-w-2xl overflow-hidden rounded-[2.5rem] relative z-10 border border-white/10 shadow-[0_24px_50px_-12px_rgba(0,0,0,0.7)] flex flex-col md:flex-row h-[500px] md:h-auto max-h-[90vh]">
            
            <!-- Left: Image Area -->
            <div class="md:w-1/2 relative bg-slate-900/60 aspect-[4/5] md:aspect-auto">
                <img 
                    v-if="modalProductImage" 
                    :src="modalProductImage" 
                    class="w-full h-full object-cover object-top"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-slate-600 bg-slate-950">
                    No Image
                </div>
                <!-- Close button for Mobile inside image panel -->
                <button 
                    @click="emit('close')" 
                    class="md:hidden absolute top-4 right-4 w-9 h-9 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center text-white border border-white/10 cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Right: Details & Selection Form -->
            <div class="p-6 md:p-8 md:w-1/2 flex flex-col justify-between overflow-y-auto">
                <!-- Heading Area -->
                <div>
                    <div class="flex justify-between items-start gap-4 mb-2">
                        <span class="text-[10px] font-bold tracking-widest text-indigo-400 uppercase bg-indigo-500/10 px-2.5 py-1 rounded-full border border-indigo-500/20">
                            {{ product.category?.name || 'Garments' }}
                        </span>
                        <!-- Close Button Desktop -->
                        <button @click="emit('close')" class="hidden md:block text-slate-400 hover:text-white transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-white tracking-tight mb-2">{{ product.name }}</h2>
                    <p class="text-slate-400 text-sm font-medium leading-relaxed mb-5">{{ product.description || 'No description available for this luxury product.' }}</p>
                </div>

                <!-- Options Area -->
                <div class="space-y-5 my-4">
                    <!-- Select Size -->
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Select Size</span>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                v-for="size in getUniqueSizes(product.variants)" 
                                :key="size"
                                @click="selectedSize = size"
                                :class="[selectedSize === size ? 'bg-indigo-600/90 border-indigo-400 text-white shadow-lg shadow-indigo-500/20' : 'bg-white/[0.03] border-white/10 text-slate-300 hover:bg-white/[0.08]']"
                                class="px-4 py-2 text-xs font-bold rounded-xl border transition-all duration-200 cursor-pointer"
                            >
                                {{ size }}
                            </button>
                        </div>
                    </div>

                    <!-- Select Color -->
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Select Color</span>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                v-for="color in getUniqueColors(product.variants)" 
                                :key="color"
                                @click="selectedColor = color"
                                :class="[selectedColor === color ? 'bg-white/[0.08] border-white/30 text-white' : 'bg-white/[0.03] border-white/10 text-slate-400 hover:bg-white/[0.08]']"
                                class="px-4 py-2 text-xs font-bold rounded-xl border flex items-center gap-2 transition-all duration-200 cursor-pointer"
                            >
                                <span class="w-3.5 h-3.5 rounded-full ring-1 ring-white/20 border border-white/10 overflow-hidden inline-flex">
                                    <span :style="getColorStyle(color)" class="w-full h-full block"></span>
                                </span>
                                {{ color }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bottom Price / Action Area -->
                <div class="pt-5 border-t border-white/[0.08] flex items-center justify-between gap-4 mt-auto">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Total Price</span>
                        <span class="text-2xl font-black text-white">${{ variantPrice.toFixed(2) }}</span>
                    </div>

                    <button 
                        @click="handleAddToCart"
                        :disabled="!selectedVariant"
                        class="glass-button-primary flex-1 py-3.5 font-bold text-sm rounded-2xl flex justify-center items-center shadow-lg shadow-indigo-500/20 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer"
                    >
                        {{ selectedVariant ? 'Add to Shopping Bag' : 'Select Options First' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
