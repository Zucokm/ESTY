<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import { homepageApi } from '@/Services/api';

const props = defineProps({
    slides: {
        type: Array,
        required: true
    },
    lookbooks: {
        type: Array,
        required: true
    },
    settings: {
        type: Object,
        required: true
    }
});

const activeTab = ref('slides');
const showProfileDropdown = ref(false);

// Modals State
const isSlideModalOpen = ref(false);
const editingSlide = ref(null);

const isLookbookModalOpen = ref(false);
const editingLookbook = ref(null);

// Forms Setup
const slideForm = useForm({
    badge: '',
    title: '',
    highlight: '',
    description: '',
    cta_text: '',
    cta_link: '',
    image: null,
    sort_order: 1,
    is_active: true
});

const lookbookForm = useForm({
    badge: '',
    title: '',
    description: '',
    cta_text: '',
    cta_link: '',
    image: null,
    sort_order: 1,
    is_active: true
});

const settingsForm = useForm({
    brand_story_title: props.settings.brand_story_title || '',
    brand_story_text_1: props.settings.brand_story_text_1 || '',
    brand_story_text_2: props.settings.brand_story_text_2 || '',
    brand_story_stat_1_val: props.settings.brand_story_stat_1_val || '',
    brand_story_stat_1_lbl: props.settings.brand_story_stat_1_lbl || '',
    brand_story_stat_2_val: props.settings.brand_story_stat_2_val || '',
    brand_story_stat_2_lbl: props.settings.brand_story_stat_2_lbl || '',
    brand_story_image_file: null
    , feature_1_title: props.settings.feature_1_title || '',
    feature_1_subtitle: props.settings.feature_1_subtitle || '',
    feature_1_icon: props.settings.feature_1_icon || 'truck',
    feature_2_title: props.settings.feature_2_title || '',
    feature_2_subtitle: props.settings.feature_2_subtitle || '',
    feature_2_icon: props.settings.feature_2_icon || 'arrow-path',
    feature_3_title: props.settings.feature_3_title || '',
    feature_3_subtitle: props.settings.feature_3_subtitle || '',
    feature_3_icon: props.settings.feature_3_icon || 'check-badge',
    feature_4_title: props.settings.feature_4_title || '',
    feature_4_subtitle: props.settings.feature_4_subtitle || '',
    feature_4_icon: props.settings.feature_4_icon || 'adjustments'
});

// Image preview references
const slideImagePreview = ref(null);
const lookbookImagePreview = ref(null);
const settingsImagePreview = ref(null);

// Handle Slide actions
const openAddSlideModal = () => {
    editingSlide.value = null;
    slideForm.reset();
    slideImagePreview.value = null;
    isSlideModalOpen.value = true;
};

const openEditSlideModal = (slide) => {
    editingSlide.value = slide;
    slideForm.badge = slide.badge || '';
    slideForm.title = slide.title || '';
    slideForm.highlight = slide.highlight || '';
    slideForm.description = slide.description || '';
    slideForm.cta_text = slide.cta_text || '';
    slideForm.cta_link = slide.cta_link || '';
    slideForm.sort_order = slide.sort_order;
    slideForm.is_active = !!slide.is_active;
    slideForm.image = null;
    slideImagePreview.value = slide.image_path;
    isSlideModalOpen.value = true;
};

const handleSlideFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        slideForm.image = file;
        slideImagePreview.value = URL.createObjectURL(file);
    }
};

const submitSlideForm = () => {
    if (editingSlide.value) {
        // Update
        slideForm.post(route('admin.homepage.updateSlide', editingSlide.value.id), {
            onSuccess: () => {
                isSlideModalOpen.value = false;
                slideForm.reset();
            }
        });
    } else {
        // Create
        slideForm.post(route('admin.homepage.storeSlide'), {
            onSuccess: () => {
                isSlideModalOpen.value = false;
                slideForm.reset();
            }
        });
    }
};

const deleteSlide = (id) => {
    if (confirm('Are you sure you want to delete this hero slide?')) {
        homepageApi.destroySlide(id);
    }
};

// Handle Lookbook actions
const openAddLookbookModal = () => {
    editingLookbook.value = null;
    lookbookForm.reset();
    lookbookImagePreview.value = null;
    isLookbookModalOpen.value = true;
};

const openEditLookbookModal = (lookbook) => {
    editingLookbook.value = lookbook;
    lookbookForm.badge = lookbook.badge || '';
    lookbookForm.title = lookbook.title || '';
    lookbookForm.description = lookbook.description || '';
    lookbookForm.cta_text = lookbook.cta_text || '';
    lookbookForm.cta_link = lookbook.cta_link || '';
    lookbookForm.sort_order = lookbook.sort_order;
    lookbookForm.is_active = !!lookbook.is_active;
    lookbookForm.image = null;
    lookbookImagePreview.value = lookbook.image_path;
    isLookbookModalOpen.value = true;
};

const handleLookbookFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        lookbookForm.image = file;
        lookbookImagePreview.value = URL.createObjectURL(file);
    }
};

const submitLookbookForm = () => {
    if (editingLookbook.value) {
        lookbookForm.post(route('admin.homepage.updateLookbook', editingLookbook.value.id), {
            onSuccess: () => {
                isLookbookModalOpen.value = false;
                lookbookForm.reset();
            }
        });
    } else {
        lookbookForm.post(route('admin.homepage.storeLookbook'), {
            onSuccess: () => {
                isLookbookModalOpen.value = false;
                lookbookForm.reset();
            }
        });
    }
};

const deleteLookbook = (id) => {
    if (confirm('Are you sure you want to delete this lookbook promo card?')) {
        homepageApi.destroyLookbook(id);
    }
};

// Handle Settings action
const handleSettingsFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        settingsForm.brand_story_image_file = file;
        settingsImagePreview.value = URL.createObjectURL(file);
    }
};

const submitSettingsForm = () => {
    settingsForm.post(route('admin.homepage.updateSettings'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Brand philosophy settings saved successfully.');
        }
    });
};

const toggleSidebar = () => {
    window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'));
};
</script>

<template>
    <Head title="Homepage Editor - ESTY" />

    <div class="min-h-screen flex selection:bg-indigo-500/30 selection:text-indigo-200 bg-[#0a0a0c] text-slate-100">
        
        <!-- Ambient Glowing Orbs -->
        <div class="fixed top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-900/10 blur-[150px] pointer-events-none"></div>
        <div class="fixed bottom-[-20%] right-[-10%] w-[600px] h-[600px] rounded-full bg-purple-900/10 blur-[150px] pointer-events-none"></div>

        <!-- Shared Left Sidebar -->
        <AdminSidebar active="homepage" />

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0 md:ml-64 flex flex-col min-h-screen">
            
            <!-- Top Navigation Bar -->
            <header class="h-16 bg-black/10 backdrop-blur-md border-b border-white/[0.06] flex items-center justify-between px-4 sm:px-8 sticky top-0 z-10">
                <div class="flex items-center gap-2">
                    <!-- Hamburger Menu Button -->
                    <button 
                        @click="toggleSidebar" 
                        class="md:hidden p-2 text-slate-400 hover:text-white transition-colors focus:outline-none"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-white font-bold text-lg tracking-tight">Homepage Editorial</h2>
                </div>

                <!-- Admin Action items -->
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button 
                            @click="showProfileDropdown = !showProfileDropdown"
                            class="flex items-center gap-2 py-1 px-3 rounded-full hover:bg-white/[0.04] transition-colors focus:outline-none"
                        >
                            <span class="text-sm font-semibold text-white">{{ $page.props.auth.user?.name || 'Admin' }}</span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': showProfileDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown Content -->
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

            <!-- Main Scrollable Body Area -->
            <main class="flex-1 min-w-0 p-4 sm:p-8 space-y-6 sm:space-y-8 max-w-6xl w-full mx-auto">
                
                <!-- Page Title -->
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Homepage Settings</h1>
                    <p class="text-slate-400 text-sm font-medium">Customize sliders, collection cards, and brand statistics blocks.</p>
                </div>

                <!-- Tab Buttons -->
                <div class="flex gap-2 border-b border-white/[0.06] pb-px overflow-x-auto whitespace-nowrap scrollbar-thin">
                    <button 
                        @click="activeTab = 'slides'" 
                        :class="[activeTab === 'slides' ? 'border-indigo-500 text-white font-semibold' : 'border-transparent text-slate-400 hover:text-slate-200']"
                        class="px-4 py-2.5 text-sm border-b-2 font-medium transition-all duration-200"
                    >
                        Hero Slideshow
                    </button>
                    <button 
                        @click="activeTab = 'lookbooks'" 
                        :class="[activeTab === 'lookbooks' ? 'border-indigo-500 text-white font-semibold' : 'border-transparent text-slate-400 hover:text-slate-200']"
                        class="px-4 py-2.5 text-sm border-b-2 font-medium transition-all duration-200"
                    >
                        Lookbooks Promo
                    </button>
                    <button 
                        @click="activeTab = 'settings'" 
                        :class="[activeTab === 'settings' ? 'border-indigo-500 text-white font-semibold' : 'border-transparent text-slate-400 hover:text-slate-200']"
                        class="px-4 py-2.5 text-sm border-b-2 font-medium transition-all duration-200"
                    >
                        Brand Philosophy
                    </button>
                </div>

                <!-- TAB 1: HERO SLIDESHOW -->
                <div v-if="activeTab === 'slides'" class="space-y-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-white font-bold text-base tracking-tight">Hero Slides</h3>
                            <p class="text-slate-400 text-xs font-medium">Banners that rotate on the landing page carousel.</p>
                        </div>
                        <button 
                            @click="openAddSlideModal"
                            class="glass-btn px-4 py-2 text-xs rounded-xl flex items-center gap-2 font-semibold bg-indigo-500/20 text-indigo-200 border-indigo-500/30 hover:bg-indigo-500/30 transition-all duration-200"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New Slide
                        </button>
                    </div>

                    <!-- Slide list grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div 
                            v-for="slide in slides" 
                            :key="slide.id"
                            class="glass-card flex flex-col justify-between overflow-hidden border border-white/[0.06] hover:border-white/10 transition-all duration-300"
                        >
                            <div class="relative h-44 bg-slate-950 overflow-hidden flex items-center justify-center">
                                <img :src="slide.image_path" class="w-full h-full object-cover opacity-60 hover:scale-105 transition-transform duration-500" alt="slide preview" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 to-transparent"></div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <span v-if="slide.badge" class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase bg-white/10 text-white border border-white/20 backdrop-blur-md">
                                        {{ slide.badge }}
                                    </span>
                                    <h4 class="text-white font-bold text-lg mt-1 truncate">{{ slide.title }} <span class="text-indigo-400 font-medium">{{ slide.highlight }}</span></h4>
                                </div>
                                <div class="absolute top-4 right-4">
                                    <span :class="[slide.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20']" class="px-2 py-0.5 rounded-full text-[10px] font-bold border">
                                        {{ slide.is_active ? 'Active' : 'Draft' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                                <p class="text-slate-400 text-xs font-medium line-clamp-2">{{ slide.description || 'No description provided.' }}</p>
                                
                                <div class="flex items-center justify-between text-xs text-slate-500 font-semibold border-t border-white/[0.04] pt-3">
                                    <span>Sort Order: {{ slide.sort_order }}</span>
                                    <div class="flex gap-2">
                                        <button @click="openEditSlideModal(slide)" class="text-indigo-400 hover:text-indigo-300 transition-colors">Edit</button>
                                        <button @click="deleteSlide(slide.id)" class="text-rose-400 hover:text-rose-300 transition-colors">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="slides.length === 0" class="col-span-full py-16 text-center glass-card border-dashed">
                            <span class="text-slate-400 text-sm font-semibold">No slides configured. Add some slides to power the slideshow.</span>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: LOOKBOOK PROMOTION -->
                <div v-if="activeTab === 'lookbooks'" class="space-y-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-white font-bold text-base tracking-tight">Lookbook Cards</h3>
                            <p class="text-slate-400 text-xs font-medium">Bespoke 2-column promotion items featured on the store front page.</p>
                        </div>
                        <button 
                            @click="openAddLookbookModal"
                            class="glass-btn px-4 py-2 text-xs rounded-xl flex items-center gap-2 font-semibold bg-indigo-500/20 text-indigo-200 border-indigo-500/30 hover:bg-indigo-500/30 transition-all duration-200"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Lookbook Card
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div 
                            v-for="item in lookbooks" 
                            :key="item.id"
                            class="glass-card flex flex-col justify-between overflow-hidden border border-white/[0.06] hover:border-white/10 transition-all duration-300"
                        >
                            <div class="relative h-44 bg-slate-950 overflow-hidden flex items-center justify-center">
                                <img :src="item.image_path" class="w-full h-full object-cover opacity-60" alt="lookbook preview" />
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 to-transparent"></div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <span v-if="item.badge" class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase bg-white/10 text-white border border-white/20 backdrop-blur-md">
                                        {{ item.badge }}
                                    </span>
                                    <h4 class="text-white font-bold text-lg mt-1 truncate">{{ item.title }}</h4>
                                </div>
                                <div class="absolute top-4 right-4">
                                    <span :class="[item.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20']" class="px-2 py-0.5 rounded-full text-[10px] font-bold border">
                                        {{ item.is_active ? 'Active' : 'Draft' }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                                <p class="text-slate-400 text-xs font-medium line-clamp-2">{{ item.description || 'No description provided.' }}</p>
                                
                                <div class="flex items-center justify-between text-xs text-slate-500 font-semibold border-t border-white/[0.04] pt-3">
                                    <span>Sort Order: {{ item.sort_order }}</span>
                                    <div class="flex gap-2">
                                        <button @click="openEditLookbookModal(item)" class="text-indigo-400 hover:text-indigo-300 transition-colors">Edit</button>
                                        <button @click="deleteLookbook(item.id)" class="text-rose-400 hover:text-rose-300 transition-colors">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="lookbooks.length === 0" class="col-span-full py-16 text-center glass-card border-dashed">
                            <span class="text-slate-400 text-sm font-semibold">No lookbook cards configured. Add cards to display promotional segments.</span>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: BRAND PHILOSOPHY -->
                <div v-if="activeTab === 'settings'" class="glass-card p-8 border border-white/[0.06] space-y-8">
                    <div>
                        <h3 class="text-white font-bold text-base tracking-tight">Brand Philosophy & Stats</h3>
                        <p class="text-slate-400 text-xs font-medium">Update editorial story details, brand descriptions, local sourcing statistics, and backdrop detail image.</p>
                    </div>

                    <form @submit.prevent="submitSettingsForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Left: Story Fields -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Editorial Section Title</label>
                                    <input 
                                        type="text" 
                                        v-model="settingsForm.brand_story_title"
                                        class="glass-input w-full py-2.5 px-4 text-sm"
                                        placeholder="Crafting a Dialogue..."
                                        required
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Story Paragraph 1</label>
                                    <textarea 
                                        v-model="settingsForm.brand_story_text_1"
                                        rows="4"
                                        class="glass-input w-full py-2.5 px-4 text-sm resize-none"
                                        placeholder="First description paragraph..."
                                        required
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Story Paragraph 2</label>
                                    <textarea 
                                        v-model="settingsForm.brand_story_text_2"
                                        rows="4"
                                        class="glass-input w-full py-2.5 px-4 text-sm resize-none"
                                        placeholder="Second craftsmanship details paragraph..."
                                        required
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Right: Stats & Image -->
                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Stat 1 Value</label>
                                        <input 
                                            type="text" 
                                            v-model="settingsForm.brand_story_stat_1_val"
                                            class="glass-input w-full py-2.5 px-4 text-sm"
                                            placeholder="100%"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Stat 1 Label</label>
                                        <input 
                                            type="text" 
                                            v-model="settingsForm.brand_story_stat_1_lbl"
                                            class="glass-input w-full py-2.5 px-4 text-sm"
                                            placeholder="Local Sourcing"
                                            required
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Stat 2 Value</label>
                                        <input 
                                            type="text" 
                                            v-model="settingsForm.brand_story_stat_2_val"
                                            class="glass-input w-full py-2.5 px-4 text-sm"
                                            placeholder="Limited"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Stat 2 Label</label>
                                        <input 
                                            type="text" 
                                            v-model="settingsForm.brand_story_stat_2_lbl"
                                            class="glass-input w-full py-2.5 px-4 text-sm"
                                            placeholder="Studio Run"
                                            required
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Story Backdrop Image</label>
                                    <div class="flex items-center gap-4">
                                        <div class="w-20 h-20 rounded-xl bg-slate-900 border border-white/10 overflow-hidden flex items-center justify-center">
                                            <img 
                                                v-if="settingsImagePreview || settings.brand_story_image" 
                                                :src="settingsImagePreview || settings.brand_story_image" 
                                                class="w-full h-full object-cover" 
                                                alt="brand image" 
                                            />
                                            <svg v-else class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <input 
                                            type="file" 
                                            @change="handleSettingsFileChange" 
                                            accept="image/*"
                                            class="text-xs text-slate-400"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-white/[0.04] mt-8">
                            <div class="mb-6">
                                <h3 class="text-xl font-bold text-white tracking-tight">Features Banner</h3>
                                <p class="text-slate-400 text-sm mt-1">Configure the 4 key features displayed below the hero section.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div v-for="i in 4" :key="i" class="space-y-4 p-4 rounded-xl border border-white/[0.05] bg-white/[0.01]">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400 font-bold text-xs">{{ i }}</div>
                                        <h4 class="text-white font-semibold text-sm">Feature {{ i }}</h4>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Title</label>
                                        <input type="text" v-model="settingsForm['feature_'+i+'_title']" class="glass-input w-full py-2 px-3 text-sm" required />
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Subtitle</label>
                                        <input type="text" v-model="settingsForm['feature_'+i+'_subtitle']" class="glass-input w-full py-2 px-3 text-sm" required />
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Icon Type</label>
                                        <select v-model="settingsForm['feature_'+i+'_icon']" class="glass-input w-full py-2 px-3 text-sm appearance-none bg-slate-900">
                                            <option value="truck">Truck (Shipping)</option>
                                            <option value="arrow-path">Circular Arrow (Returns)</option>
                                            <option value="check-badge">Check Badge (Quality/Organic)</option>
                                            <option value="adjustments">Sliders (Bespoke)</option>
                                            <option value="shield-check">Shield (Secure)</option>
                                            <option value="star">Star (Premium)</option>
                                            <option value="heart">Heart</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-white/[0.04]">
                            <button 
                                type="submit" 
                                :disabled="settingsForm.processing"
                                class="glass-btn px-6 py-2.5 text-xs rounded-xl font-bold bg-indigo-500 text-white border-indigo-600 hover:bg-indigo-400 disabled:opacity-50 transition-all duration-200"
                            >
                                {{ settingsForm.processing ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>

        <!-- SLIDE MODAL (CREATE / EDIT) -->
        <div v-if="isSlideModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="glass-card w-full max-w-xl border-white/10 shadow-2xl p-6 rounded-2xl space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-white font-bold text-base">{{ editingSlide ? 'Edit Hero Slide' : 'Add New Hero Slide' }}</h3>
                    <button @click="isSlideModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitSlideForm" class="space-y-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2">Badge Text</label>
                            <input 
                                type="text" 
                                v-model="slideForm.badge" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="Autumn / Winter '26"
                            />
                        </div>
                        <div>
                            <label class="block mb-2">Main Title</label>
                            <input 
                                type="text" 
                                v-model="slideForm.title" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="Designed for Comfort."
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2">Highlight Accent Text</label>
                            <input 
                                type="text" 
                                v-model="slideForm.highlight" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="Crafted for Excellence."
                            />
                        </div>
                        <div>
                            <label class="block mb-2">Description</label>
                            <input 
                                type="text" 
                                v-model="slideForm.description" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="Minimalist sustainable collection..."
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2">CTA Button Text</label>
                            <input 
                                type="text" 
                                v-model="slideForm.cta_text" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="Explore Collection"
                            />
                        </div>
                        <div>
                            <label class="block mb-2">CTA Destination Link</label>
                            <input 
                                type="text" 
                                v-model="slideForm.cta_link" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="#shop"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2">Sort Order</label>
                            <input 
                                type="number" 
                                v-model="slideForm.sort_order" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                required
                            />
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input 
                                type="checkbox" 
                                id="is_active_slide" 
                                v-model="slideForm.is_active" 
                                class="w-4 h-4 rounded border-white/10 bg-slate-900 text-indigo-500 focus:ring-indigo-500"
                            />
                            <label for="is_active_slide" class="text-slate-300 select-none normal-case">Publish immediately</label>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2">Slide Background Image</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-12 rounded bg-slate-950 border border-white/10 overflow-hidden flex items-center justify-center">
                                <img v-if="slideImagePreview" :src="slideImagePreview" class="w-full h-full object-cover" alt="slide preview" />
                                <svg v-else class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input 
                                type="file" 
                                @change="handleSlideFileChange" 
                                accept="image/*"
                                class="text-[11px] text-slate-400"
                                :required="!editingSlide"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/[0.04]">
                        <button 
                            type="button" 
                            @click="isSlideModalOpen = false" 
                            class="px-4 py-2 bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="slideForm.processing"
                            class="px-4 py-2 bg-indigo-500 hover:bg-indigo-400 disabled:opacity-50 text-white rounded-xl text-xs font-bold transition-all duration-200"
                        >
                            {{ slideForm.processing ? 'Saving...' : (editingSlide ? 'Save Changes' : 'Create Slide') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LOOKBOOK MODAL (CREATE / EDIT) -->
        <div v-if="isLookbookModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="glass-card w-full max-w-xl border-white/10 shadow-2xl p-6 rounded-2xl space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-white font-bold text-base">{{ editingLookbook ? 'Edit Lookbook Card' : 'Add Lookbook Card' }}</h3>
                    <button @click="isLookbookModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitLookbookForm" class="space-y-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2">Badge Text</label>
                            <input 
                                type="text" 
                                v-model="lookbookForm.badge" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="Active Streetwear"
                            />
                        </div>
                        <div>
                            <label class="block mb-2">Promo Title</label>
                            <input 
                                type="text" 
                                v-model="lookbookForm.title" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="The Transit Collection"
                                required
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2">Description</label>
                        <textarea 
                            v-model="lookbookForm.description" 
                            rows="2"
                            class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case resize-none"
                            placeholder="Relaxed silhouettes, heavyweight fleece..."
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2">CTA Button Text</label>
                            <input 
                                type="text" 
                                v-model="lookbookForm.cta_text" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="Explore Lookbook"
                            />
                        </div>
                        <div>
                            <label class="block mb-2">CTA Destination Link</label>
                            <input 
                                type="text" 
                                v-model="lookbookForm.cta_link" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                placeholder="#shop"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2">Sort Order</label>
                            <input 
                                type="number" 
                                v-model="lookbookForm.sort_order" 
                                class="glass-input w-full py-2.5 px-4 text-sm text-slate-100 font-normal normal-case"
                                required
                            />
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input 
                                type="checkbox" 
                                id="is_active_lookbook" 
                                v-model="lookbookForm.is_active" 
                                class="w-4 h-4 rounded border-white/10 bg-slate-900 text-indigo-500 focus:ring-indigo-500"
                            />
                            <label for="is_active_lookbook" class="text-slate-300 select-none normal-case">Publish immediately</label>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2">Card Image Banner</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-12 rounded bg-slate-950 border border-white/10 overflow-hidden flex items-center justify-center">
                                <img v-if="lookbookImagePreview" :src="lookbookImagePreview" class="w-full h-full object-cover" alt="lookbook preview" />
                                <svg v-else class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input 
                                type="file" 
                                @change="handleLookbookFileChange" 
                                accept="image/*"
                                class="text-[11px] text-slate-400"
                                :required="!editingLookbook"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/[0.04]">
                        <button 
                            type="button" 
                            @click="isLookbookModalOpen = false" 
                            class="px-4 py-2 bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="lookbookForm.processing"
                            class="px-4 py-2 bg-indigo-500 hover:bg-indigo-400 disabled:opacity-50 text-white rounded-xl text-xs font-bold transition-all duration-200"
                        >
                            {{ lookbookForm.processing ? 'Saving...' : (editingLookbook ? 'Save Changes' : 'Create Card') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>
