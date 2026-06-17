<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const { props: pageProps } = usePage();
const userName = ref(pageProps.auth.user?.name || 'Admin');
const userEmail = ref(pageProps.auth.user?.email || 'admin@verone.com');

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        required: true
    }
});

const showProfileDropdown = ref(false);
const imagePreviews = ref([]);

// Form Setup: uses PUT spoofing for file upload support on edit
const form = useForm({
    _method: 'PUT',
    category_id: props.product.category_id || '',
    name: props.product.name || '',
    slug: props.product.slug || '',
    description: props.product.description || '',
    base_price: props.product.base_price || '',
    images: [],
    variants: props.product.variants && props.product.variants.length > 0 
        ? props.product.variants.map(v => ({
            id: v.id,
            size: v.size,
            color: v.color,
            stock_quantity: v.stock_quantity,
            sku: v.sku,
            additional_price: v.additional_price
          }))
        : [{ size: 'M', color: '', stock_quantity: 10, sku: '', additional_price: 0.00 }]
});

// Auto-generate slug from name
watch(() => form.name, (newName) => {
    form.slug = newName
        .toLowerCase()
        .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
        .replace(/\s+/g, '-') // collapse whitespace and replace by -
        .replace(/-+/g, '-'); // collapse dashes
});

// Handle Multiple Image Upload Previews
const handleImageUpload = (event) => {
    const files = event.target.files;
    form.images = Array.from(files);
    
    // Clear old previews
    imagePreviews.value = [];
    
    // Generate new previews
    for (let i = 0; i < files.length; i++) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(files[i]);
    }
};

// Dynamic Variants Logic
const addVariant = () => {
    form.variants.push({
        size: 'M',
        color: '',
        stock_quantity: 10,
        sku: '',
        additional_price: 0.00
    });
};

const removeVariant = (index) => {
    if (form.variants.length > 1) {
        form.variants.splice(index, 1);
    }
};

const submit = () => {
    // We send a POST request with _method=PUT to support multipart file uploads in PHP
    form.post(route('products.update', props.product.id), {
        forceFormData: true,
        onSuccess: () => {
            imagePreviews.value = [];
        }
    });
};
</script>

<template>
    <Head title="Edit Product - ESTY" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Left Sidebar -->
        <aside class="w-64 fixed inset-y-0 left-0 bg-black/[0.15] backdrop-blur-3xl border-r border-white/[0.06] flex flex-col z-20">
            <div class="h-16 flex items-center px-6 border-b border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-md shadow-indigo-500/20">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4a3 3 0 00-3 3v1h6V7a3 3 0 00-3-3zM3 19a2 2 0 002 2h14a2 2 0 002-2M5 11h14l1 8H4l1-8z" />
                        </svg>
                    </div>
                    <span class="text-white font-bold tracking-tight text-base uppercase">ESTY <span class="text-xs text-indigo-400 font-medium tracking-normal lowercase ml-1">admin</span></span>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2">
                <Link 
                    :href="route('dashboard')"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border border-transparent text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    Dashboard
                </Link>

                <Link 
                    :href="route('products.index')"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border bg-white/[0.08] text-white shadow-inner border-white/10 transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Products
                </Link>

                <Link 
                    :href="route('admin.orders.index')"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border border-transparent text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Orders
                </Link>

                <a 
                    href="#" 
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl font-semibold text-sm border border-transparent text-slate-400 hover:text-slate-200 hover:bg-white/[0.03] transition-all duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
            </nav>

            <div class="p-4 border-t border-white/[0.06] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center font-bold text-white">
                        {{ userName.charAt(0) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-white leading-tight">{{ userName }}</span>
                        <span class="text-[10px] text-slate-400 font-medium truncate max-w-[120px]">{{ userEmail }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 pl-64 flex flex-col min-h-screen">
            
            <!-- Top Navigation -->
            <header class="h-16 bg-white/[0.02] backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-8 sticky top-0 z-10">
                <div></div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button 
                            @click="showProfileDropdown = !showProfileDropdown"
                            class="flex items-center gap-2 py-1 px-3 rounded-full hover:bg-white/[0.04] transition-colors focus:outline-none"
                        >
                            <span class="text-sm font-semibold text-white">{{ userName }}</span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': showProfileDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div 
                            v-if="showProfileDropdown" 
                            class="absolute right-0 mt-2 w-48 glass-card border-white/10 shadow-2xl p-2 rounded-2xl flex flex-col z-30"
                        >
                            <Link 
                                :href="route('profile.edit')" 
                                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/[0.05] transition-colors"
                            >
                                Profile Settings
                            </Link>
                            <Link 
                                :href="route('logout')" 
                                method="post" 
                                as="button" 
                                class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Body (Form) -->
            <main class="flex-1 p-8 space-y-6">
                <!-- Page Title -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Edit Garment: {{ product.name }}</h1>
                        <p class="text-slate-400 text-sm font-medium">Modify base details, variant configurations, and images.</p>
                    </div>
                    <Link 
                        :href="route('products.index')" 
                        class="glass-button text-xs py-2.5 px-5 rounded-full"
                    >
                        Back to Inventory
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-8 max-w-4xl">
                    
                    <!-- Section 1: Base Product Details -->
                    <div class="glass-card p-6 sm:p-8 space-y-6">
                        <h2 class="text-lg font-bold text-white tracking-tight border-b border-white/[0.06] pb-3">Section 1: Base Product</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Product Name</label>
                                <input 
                                    type="text" 
                                    v-model="form.name" 
                                    class="glass-input" 
                                    placeholder="e.g. Silk Blend Knit Shirt"
                                    required
                                />
                                <span v-if="form.errors.name" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.name }}</span>
                            </div>

                            <!-- Slug -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">URL Slug</label>
                                <input 
                                    type="text" 
                                    v-model="form.slug" 
                                    class="glass-input font-mono text-sm" 
                                    placeholder="e.g. silk-blend-knit-shirt"
                                    required
                                />
                                <span v-if="form.errors.slug" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.slug }}</span>
                            </div>

                            <!-- Category Dropdown -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Category</label>
                                <select 
                                    v-model="form.category_id" 
                                    class="glass-input appearance-none text-slate-200"
                                    required
                                >
                                    <option value="" disabled class="bg-[#121620]">Select Category</option>
                                    <option 
                                        v-for="cat in categories" 
                                        :key="cat.id" 
                                        :value="cat.id"
                                        class="bg-[#121620]"
                                    >
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <span v-if="form.errors.category_id" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.category_id }}</span>
                            </div>

                            <!-- Base Price -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Base Price ($)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    v-model="form.base_price" 
                                    class="glass-input" 
                                    placeholder="120.00"
                                    required
                                />
                                <span v-if="form.errors.base_price" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.base_price }}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Description</label>
                            <textarea 
                                v-model="form.description" 
                                rows="4" 
                                class="glass-input" 
                                placeholder="Write product descriptions, premium materials info, care instructions..."
                            ></textarea>
                            <span v-if="form.errors.description" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.description }}</span>
                        </div>
                    </div>

                    <!-- Section 1.5: Image Uploads -->
                    <div class="glass-card p-6 sm:p-8 space-y-6">
                        <h2 class="text-lg font-bold text-white tracking-tight border-b border-white/[0.06] pb-3">Section 1.5: Product Images</h2>
                        
                        <!-- Existing database images list -->
                        <div v-if="product.images && product.images.length > 0" class="space-y-2">
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider ml-1">Current Active Images</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                                <div 
                                    v-for="img in product.images" 
                                    :key="img.id" 
                                    class="relative aspect-square rounded-2xl overflow-hidden border border-white/5 bg-slate-950"
                                >
                                    <img :src="img.image_path" class="w-full h-full object-cover" />
                                    <span v-if="img.is_primary" class="absolute top-2 left-2 bg-emerald-500 text-white font-bold text-[8px] uppercase tracking-wider px-2 py-0.5 rounded-full shadow-md">
                                        Primary
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Upload New Images (Appends to list)</label>
                            <input 
                                type="file" 
                                multiple 
                                @change="handleImageUpload" 
                                class="glass-input file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-200 hover:file:bg-indigo-600/30"
                                accept="image/*"
                            />
                            <span v-if="form.errors.images" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.images }}</span>
                        </div>

                        <!-- Preview Area for new images -->
                        <div v-if="imagePreviews.length > 0" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                            <div 
                                v-for="(preview, idx) in imagePreviews" 
                                :key="idx" 
                                class="relative aspect-square rounded-2xl overflow-hidden border border-white/10 group bg-slate-950"
                            >
                                <img :src="preview" class="w-full h-full object-cover" />
                                <span class="absolute top-2 left-2 bg-indigo-500 text-white font-bold text-[8px] uppercase tracking-wider px-2 py-0.5 rounded-full shadow-md">
                                    New Image
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Dynamic Variants -->
                    <div class="glass-card p-6 sm:p-8 space-y-6">
                        <div class="flex items-center justify-between border-b border-white/[0.06] pb-3">
                            <h2 class="text-lg font-bold text-white tracking-tight">Section 2: Garment Variants</h2>
                            
                            <button 
                                type="button" 
                                @click="addVariant"
                                class="glass-button text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-full hover:bg-white/[0.08]"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Variant SKU
                            </button>
                        </div>

                        <!-- Variants Rows -->
                        <div class="space-y-4">
                            <div 
                                v-for="(variant, index) in form.variants" 
                                :key="index"
                                class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.04] grid grid-cols-1 sm:grid-cols-5 gap-4 items-end relative group"
                            >
                                <!-- Hidden variant ID tracking -->
                                <input type="hidden" v-model="variant.id" />

                                <!-- Size Input -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Size</label>
                                    <input 
                                        type="text" 
                                        v-model="variant.size" 
                                        class="glass-input text-sm py-2 px-3" 
                                        placeholder="M, L, XL"
                                        required
                                    />
                                </div>

                                <!-- Color Input -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Color</label>
                                    <input 
                                        type="text" 
                                        v-model="variant.color" 
                                        class="glass-input text-sm py-2 px-3" 
                                        placeholder="Charcoal"
                                        required
                                    />
                                </div>

                                <!-- Stock Qty Input -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Stock Qty</label>
                                    <input 
                                        type="number" 
                                        v-model="variant.stock_quantity" 
                                        class="glass-input text-sm py-2 px-3" 
                                        placeholder="10"
                                        required
                                    />
                                </div>

                                <!-- SKU Input -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">SKU (Optional)</label>
                                    <input 
                                        type="text" 
                                        v-model="variant.sku" 
                                        class="glass-input text-sm py-2 px-3 font-mono" 
                                        placeholder="Auto-generated if empty"
                                    />
                                </div>

                                <!-- Additional Price & Remove Button -->
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Add. Price ($)</label>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            v-model="variant.additional_price" 
                                            class="glass-input text-sm py-2 px-3" 
                                            placeholder="0.00"
                                            required
                                        />
                                    </div>
                                    
                                    <!-- Delete Row Button -->
                                    <button 
                                        v-if="form.variants.length > 1"
                                        type="button" 
                                        @click="removeVariant(index)"
                                        class="h-10 w-10 flex items-center justify-center rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500 hover:text-white transition-all"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit / Form Action -->
                    <div class="flex items-center justify-end gap-4">
                        <Link 
                            :href="route('products.index')" 
                            class="glass-button py-3 px-6 rounded-xl font-semibold text-sm"
                        >
                            Cancel
                        </Link>
                        
                        <button 
                            type="submit" 
                            class="glass-button-primary py-3 px-8 rounded-xl font-semibold text-sm hover:scale-[1.02] active:scale-[0.98] transition-all duration-300"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="inline-block animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                            Update Product & Variants
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</template>
