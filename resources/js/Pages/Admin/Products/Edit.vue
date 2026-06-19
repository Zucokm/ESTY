<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';

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
const imagePreviews = ref({});
const openDropdownIndex = ref(null);

import { computed } from 'vue';

const presetColors = [
    { name: 'Oatmeal', hex: '#e5dcd3' },
    { name: 'Charcoal', hex: '#2f3542' },
    { name: 'Navy', hex: '#1e272e' },
    { name: 'Black', hex: '#111111' },
    { name: 'Sage', hex: '#a3b19b' },
    { name: 'Beige', hex: '#d2b48c' },
    { name: 'Khaki', hex: '#c3b091' },
    { name: 'Gray', hex: '#718093' },
    { name: 'White', hex: '#f5f6fa' },
    { name: 'Olive', hex: '#57606f' },
    { name: 'Rose', hex: '#fda7df' },
    { name: 'Red', hex: '#ff7675' },
    { name: 'Blue', hex: '#74b9ff' },
    { name: 'Green', hex: '#55efc4' },
    { name: 'Pink', hex: '#ff80ab' },
    { name: 'Yellow', hex: '#feca57' },
    { name: 'Orange', hex: '#ff9f43' },
    { name: 'Purple', hex: '#9c27b0' },
    { name: 'Brown', hex: '#8d6e63' },
    { name: 'Cream', hex: '#fffdd0' },
    { name: 'Tan', hex: '#d2b48c' },
    { name: 'Maroon', hex: '#800000' },
    { name: 'Burgundy', hex: '#800020' },
    { name: 'Teal', hex: '#008080' },
    { name: 'Lavender', hex: '#e6e6fa' },
    { name: 'Mustard', hex: '#e1ad01' },
    { name: 'Camel', hex: '#c19a6b' },
    { name: 'Coral', hex: '#ff7f50' },
    { name: 'Sand', hex: '#c2b280' },
    { name: 'Mint', hex: '#98ff98' },
    { name: 'Indigo', hex: '#4b0082' },
    { name: 'Violet', hex: '#ee82ee' },
    { name: 'Plum', hex: '#dda0dd' },
    { name: 'Magenta', hex: '#ff00ff' },
    { name: 'Gold', hex: '#ffd700' },
    { name: 'Silver', hex: '#c0c0c0' },
    { name: 'Sky Blue', hex: '#87ceeb' },
    { name: 'Emerald', hex: '#50c878' }
];

const getColorStyle = (colorName) => {
    if (!colorName) return { backgroundColor: '#334155' };
    
    const getHex = (name) => {
        const norm = name.toLowerCase().trim();
        const found = presetColors.find(c => c.name.toLowerCase().trim() === norm);
        return found ? found.hex : norm;
    };

    const parts = colorName.split('/').map(p => p.trim()).filter(p => p !== '');
    if (parts.length === 0) return { backgroundColor: '#334155' };
    if (parts.length === 1) return { backgroundColor: getHex(parts[0]) };
    
    const hexes = parts.map(getHex);
    if (hexes.length === 2) {
        return { background: `linear-gradient(135deg, ${hexes[0]} 50%, ${hexes[1]} 50%)` };
    }
    if (hexes.length === 3) {
        return { background: `conic-gradient(${hexes[0]} 120deg, ${hexes[1]} 120deg 240deg, ${hexes[2]} 240deg)` };
    }
    if (hexes.length === 4) {
        return { background: `conic-gradient(${hexes[0]} 90deg, ${hexes[1]} 90deg 180deg, ${hexes[2]} 180deg 270deg, ${hexes[3]} 270deg)` };
    }
    const step = 360 / hexes.length;
    const conicParts = hexes.map((hex, i) => `${hex} ${i * step}deg ${(i + 1) * step}deg`);
    return { background: `conic-gradient(${conicParts.join(', ')})` };
};

const toggleColorInVariant = (variant, colorName) => {
    if (!variant.selected_colors) {
        variant.selected_colors = [];
    }
    const idx = variant.selected_colors.indexOf(colorName);
    if (idx === -1) {
        if (variant.selected_colors.length < 4) {
            variant.selected_colors.push(colorName);
        }
    } else {
        variant.selected_colors.splice(idx, 1);
    }
    variant.color = variant.selected_colors.join(' / ');
};

// Form Setup: uses PUT spoofing for file upload support on edit
const form = useForm({
    _method: 'PUT',
    category_id: props.product.category_id || '',
    name: props.product.name || '',
    slug: props.product.slug || '',
    description: props.product.description || '',
    base_price: props.product.base_price || '',
    color_images: {},
    existing_image_colors: props.product.images
        ? props.product.images.reduce((acc, img) => {
            acc[img.id] = img.color || 'general_unspecified';
            return acc;
          }, {})
        : {},
    deleted_image_ids: [],
    variants: props.product.variants && props.product.variants.length > 0 
        ? props.product.variants.map(v => ({
            id: v.id,
            size: v.size,
            color: v.color,
            selected_colors: v.color ? v.color.split(' / ').map(c => c.trim()) : [],
            stock_quantity: v.stock_quantity,
            sku: v.sku,
            additional_price: v.additional_price
          }))
        : [{ size: 'M', color: '', selected_colors: [], stock_quantity: 10, sku: '', additional_price: 0.00 }]
});

// Extract unique colors defined in variants list
const uniqueColors = computed(() => {
    const colorsSet = new Set();
    form.variants.forEach(v => {
        if (v.color && v.color.trim() !== '') {
            colorsSet.add(v.color.trim());
        }
    });
    return Array.from(colorsSet);
});

// Auto-generate slug from name
watch(() => form.name, (newName) => {
    form.slug = newName
        .toLowerCase()
        .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
        .replace(/\s+/g, '-') // collapse whitespace and replace by -
        .replace(/-+/g, '-'); // collapse dashes
});

// Handle Multiple Image Upload Previews grouped by color
const handleImageUpload = (event, colorKey) => {
    const files = event.target.files;
    form.color_images[colorKey] = Array.from(files);
    
    // Clear old previews for this color key
    imagePreviews.value[colorKey] = [];
    
    // Generate new previews
    for (let i = 0; i < files.length; i++) {
        const reader = new FileReader();
        reader.onload = (e) => {
            if (!imagePreviews.value[colorKey]) {
                imagePreviews.value[colorKey] = [];
            }
            imagePreviews.value[colorKey].push(e.target.result);
        };
        reader.readAsDataURL(files[i]);
    }
};

const deleteExistingImage = (imageId) => {
    if (!form.deleted_image_ids.includes(imageId)) {
        form.deleted_image_ids.push(imageId);
    }
};

// Dynamic Variants Logic
const addVariant = () => {
    form.variants.push({
        size: 'M',
        color: '',
        selected_colors: [],
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

import axios from 'axios';

const localCategories = ref([...props.categories]);
const showAddCategoryModal = ref(false);
const newCategoryName = ref('');
const newCategoryDescription = ref('');
const isCreatingCategory = ref(false);
const categoryError = ref('');

const submitNewCategory = async () => {
    if (!newCategoryName.value.trim()) return;
    
    isCreatingCategory.value = true;
    categoryError.value = '';
    
    try {
        const response = await axios.post(route('admin.categories.store'), {
            name: newCategoryName.value,
            description: newCategoryDescription.value
        });
        
        const newCat = response.data;
        localCategories.value.push(newCat);
        form.category_id = newCat.id;
        
        newCategoryName.value = '';
        newCategoryDescription.value = '';
        showAddCategoryModal.value = false;
    } catch (error) {
        console.error('Error creating category:', error);
        categoryError.value = error.response?.data?.message || 'Failed to create category. Please check if the category name already exists.';
    } finally {
        isCreatingCategory.value = false;
    }
};

const submit = () => {
    // We send a POST request with _method=PUT to support multipart file uploads in PHP
    form.post(route('admin.products.update', props.product.id), {
        forceFormData: true,
        onSuccess: () => {
            imagePreviews.value = {};
        }
    });
};
</script>

<template>
    <Head title="Edit Product - ESTY" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200">
        
        <!-- Left Sidebar -->
        <AdminSidebar active="products" />

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
                        :href="route('admin.products.index')" 
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
                                 <div class="flex gap-2">
                                     <div class="relative flex-1">
                                         <select 
                                             v-model="form.category_id" 
                                             class="glass-input appearance-none text-slate-200 pr-10"
                                             required
                                         >
                                             <option value="" disabled class="bg-[#121620]">Select Category</option>
                                             <option 
                                                 v-for="cat in localCategories" 
                                                 :key="cat.id" 
                                                 :value="cat.id"
                                                 class="bg-[#121620]"
                                             >
                                                 {{ cat.name }}
                                             </option>
                                         </select>
                                         <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                             </svg>
                                         </span>
                                     </div>
                                     <button 
                                         type="button"
                                         @click="showAddCategoryModal = true"
                                         class="px-4 bg-indigo-600/80 hover:bg-indigo-600 border border-white/10 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1 active:scale-[0.97]"
                                         title="Create New Category"
                                     >
                                         <span>+ New</span>
                                     </button>
                                 </div>
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
                        <h2 class="text-lg font-bold text-white tracking-tight border-b border-white/[0.06] pb-3">Section 1.5: Product Images (Color Grouped)</h2>
                        
                        <!-- Existing database images list -->
                        <div v-if="product.images && product.images.length > 0" class="space-y-4">
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider ml-1">Current Active Images</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <div 
                                    v-for="img in product.images" 
                                    :key="img.id" 
                                    v-show="!form.deleted_image_ids.includes(img.id)"
                                    class="p-3 rounded-2xl bg-white/[0.02] border border-white/[0.05] flex gap-4 items-center"
                                >
                                    <div class="relative w-16 h-16 rounded-xl overflow-hidden border border-white/10 shrink-0 bg-slate-950">
                                        <img :src="img.image_path" class="w-full h-full object-cover" />
                                    </div>
                                    <div class="flex-1 space-y-2">
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Image Color Association</label>
                                        <select 
                                            v-model="form.existing_image_colors[img.id]"
                                            class="glass-input text-xs py-1.5 px-2.5"
                                        >
                                            <option value="general_unspecified">General / Cover Image</option>
                                            <option v-for="color in uniqueColors" :key="color" :value="color">{{ color }}</option>
                                        </select>
                                    </div>
                                    <button 
                                        type="button"
                                        @click="deleteExistingImage(img.id)"
                                        class="h-8 w-8 flex items-center justify-center rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500 hover:text-white transition-all shrink-0"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- New image uploads -->
                        <div class="space-y-4 border-t border-white/[0.06] pt-6">
                            <h3 class="text-sm font-semibold text-white ml-1">Upload New Images</h3>
                            
                            <!-- General images -->
                            <div class="space-y-4 p-4 rounded-2xl bg-white/[0.02] border border-white/[0.04]">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2 ml-1">New General / Cover Images (No Specific Color)</label>
                                    <input 
                                        type="file" 
                                        multiple 
                                        @change="handleImageUpload($event, 'general_unspecified')" 
                                        class="glass-input file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-200 hover:file:bg-indigo-600/30"
                                        accept="image/*"
                                    />
                                </div>
                                <div v-if="imagePreviews['general_unspecified'] && imagePreviews['general_unspecified'].length > 0" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                                    <div 
                                        v-for="(preview, idx) in imagePreviews['general_unspecified']" 
                                        :key="idx" 
                                        class="relative aspect-square rounded-2xl overflow-hidden border border-white/10 group bg-slate-950"
                                    >
                                        <img :src="preview" class="w-full h-full object-cover" />
                                        <span class="absolute top-2 left-2 bg-indigo-500 text-white font-bold text-[8px] uppercase tracking-wider px-2 py-0.5 rounded-full shadow-md">
                                            New Cover
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Color specific images -->
                            <div v-if="uniqueColors.length > 0" class="space-y-4 mt-6">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider ml-1">New Images by Variant Color</h4>
                                <div v-for="color in uniqueColors" :key="color" class="space-y-4 p-4 rounded-2xl bg-white/[0.02] border border-white/[0.04]">
                                    <div>
                                        <label class="block text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 ml-1">New Images for Color: {{ color }}</label>
                                        <input 
                                            type="file" 
                                            multiple 
                                            @change="handleImageUpload($event, color)" 
                                            class="glass-input file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-200 hover:file:bg-indigo-600/30"
                                            accept="image/*"
                                        />
                                    </div>
                                    <div v-if="imagePreviews[color] && imagePreviews[color].length > 0" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                                        <div 
                                            v-for="(preview, idx) in imagePreviews[color]" 
                                            :key="idx" 
                                            class="relative aspect-square rounded-2xl overflow-hidden border border-white/10 group bg-slate-950"
                                        >
                                            <img :src="preview" class="w-full h-full object-cover" />
                                            <span class="absolute top-2 left-2 bg-purple-500 text-white font-bold text-[8px] uppercase tracking-wider px-2 py-0.5 rounded-full shadow-md">
                                                New {{ color }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
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

                                <!-- Color Selection Dropdown -->
                                <div class="relative">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Color (Select up to 4)</label>
                                    <button 
                                        type="button"
                                        @click="openDropdownIndex = (openDropdownIndex === index ? null : index)"
                                        class="glass-input text-sm py-2 px-3 flex items-center justify-between gap-2 text-left min-h-[42px] w-full"
                                    >
                                        <div class="flex items-center gap-2 truncate">
                                            <span 
                                                v-if="variant.color" 
                                                class="w-5 h-5 rounded-full inline-flex border border-white/20 shrink-0 shadow-inner overflow-hidden"
                                            >
                                                <span :style="getColorStyle(variant.color)" class="w-full h-full block"></span>
                                            </span>
                                            <span class="truncate text-slate-200">{{ variant.color || 'Select Colors' }}</span>
                                        </div>
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Dropdown list -->
                                    <div 
                                        v-if="openDropdownIndex === index" 
                                        class="absolute left-0 right-0 bottom-full mb-2 max-h-60 overflow-y-auto glass-card border-white/10 shadow-2xl p-1.5 rounded-2xl z-50 flex flex-col gap-0.5 bg-[#121620]"
                                    >
                                        <div 
                                            v-for="preset in presetColors" 
                                            :key="preset.name"
                                            @click="toggleColorInVariant(variant, preset.name)"
                                            class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/[0.05] cursor-pointer transition-colors"
                                        >
                                            <div class="flex items-center gap-2">
                                                <span :style="{ backgroundColor: preset.hex }" class="w-4.5 h-4.5 rounded-full border border-white/10"></span>
                                                <span>{{ preset.name }}</span>
                                            </div>
                                            <span class="text-indigo-400 font-bold" v-if="variant.selected_colors && variant.selected_colors.includes(preset.name)">✓</span>
                                        </div>
                                    </div>
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
                            :href="route('admin.products.index')" 
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

        <!-- Create Category Modal -->
        <div 
            v-if="showAddCategoryModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div @click="showAddCategoryModal = false" class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>
            
            <div class="glass-card w-full max-w-md p-6 relative z-10 border border-white/10 shadow-[0_24px_50px_-12px_rgba(0,0,0,0.7)]">
                <div class="flex items-center justify-between border-b border-white/[0.06] pb-3 mb-4">
                    <h3 class="text-lg font-bold text-white">Create New Category</h3>
                    <button @click="showAddCategoryModal = false" class="text-slate-400 hover:text-white transition-colors">
                        ✕
                    </button>
                </div>
                
                <form @submit.prevent="submitNewCategory" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Category Name</label>
                        <input 
                            v-model="newCategoryName" 
                            type="text" 
                            required
                            placeholder="e.g. Hats, Accessories"
                            class="glass-input text-sm"
                        />
                        <span v-if="categoryError" class="text-xs text-rose-400 mt-1 block ml-1">{{ categoryError }}</span>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Description (Optional)</label>
                        <textarea 
                            v-model="newCategoryDescription" 
                            class="glass-input text-sm h-20 resize-none"
                            placeholder="Brief description of category items..."
                        ></textarea>
                    </div>
                    
                    <div class="pt-4 border-t border-white/[0.06] flex justify-end gap-3">
                        <button 
                            type="button" 
                            @click="showAddCategoryModal = false" 
                            class="glass-button text-xs py-2 px-4 rounded-xl border border-white/5 text-slate-300 hover:text-white"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="isCreatingCategory || !newCategoryName.trim()"
                            class="glass-button-primary text-xs py-2 px-5 rounded-xl border border-white/10 font-bold disabled:opacity-50"
                        >
                            {{ isCreatingCategory ? 'Creating...' : 'Create Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
