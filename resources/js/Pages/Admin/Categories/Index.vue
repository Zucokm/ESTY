<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';

const { props: pageProps } = usePage();
const userName = ref(pageProps.auth.user?.name || 'Admin');
const userEmail = ref(pageProps.auth.user?.email || 'admin@verone.com');

const props = defineProps({
    categories: {
        type: Array,
        required: true
    }
});

const showProfileDropdown = ref(false);
const showAddModal = ref(false);
const showEditModal = ref(false);
const editingCategory = ref(null);

// Form for Adding Category
const addForm = useForm({
    name: '',
    description: '',
    image: null
});

// Image preview for Add Form
const addImagePreview = ref(null);

const handleAddImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        addForm.image = file;
        addImagePreview.value = URL.createObjectURL(file);
    }
};

const submitAdd = () => {
    addForm.post(route('categories.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
            addImagePreview.value = null;
        }
    });
};

// Form for Editing Category
const editForm = useForm({
    _method: 'PUT',
    name: '',
    description: '',
    image: null
});

// Image preview for Edit Form
const editImagePreview = ref(null);

const handleEditImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        editForm.image = file;
        editImagePreview.value = URL.createObjectURL(file);
    }
};

const openEditModal = (cat) => {
    editingCategory.value = cat;
    editForm.name = cat.name;
    editForm.description = cat.description || '';
    editForm.image = null;
    editImagePreview.value = cat.image_path || null;
    showEditModal.value = true;
};

const submitUpdate = () => {
    // We send a POST request with _method=PUT to support files in Laravel update route
    editForm.post(route('categories.update', editingCategory.value.id), {
        forceFormData: true,
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            editImagePreview.value = null;
            editingCategory.value = null;
        }
    });
};

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('categories.destroy', id));
    }
};

const toggleSidebar = () => {
    window.dispatchEvent(new CustomEvent('toggle-admin-sidebar'));
};
</script>

<template>
    <Head title="Admin - Category Management" />

    <div class="min-h-screen bg-[#080b11] text-slate-100 flex relative overflow-hidden">
        <!-- Ambient Shifts -->
        <div class="fixed -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-indigo-500/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed -bottom-[30%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-500/10 blur-[120px] pointer-events-none z-0"></div>

        <!-- Sidebar Navigation -->
        <AdminSidebar active="categories" />

        <!-- Main Content Area -->
        <div class="flex-1 min-h-screen lg:ml-64 flex flex-col relative z-10">
            <!-- Header bar -->
            <header class="h-16 border-b border-white/[0.06] flex items-center justify-between px-4 sm:px-8 bg-black/[0.05] backdrop-blur-md">
                <div class="flex items-center gap-2">
                    <!-- Hamburger Menu Button -->
                    <button 
                        @click="toggleSidebar" 
                        class="lg:hidden p-2 text-slate-400 hover:text-white transition-colors focus:outline-none"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-bold text-white tracking-tight">Category Settings</h1>
                </div>
                
                <div class="relative">
                    <button 
                        @click="showProfileDropdown = !showProfileDropdown"
                        class="flex items-center gap-2.5 focus:outline-none"
                    >
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center font-bold text-white shadow-md">
                            {{ userName.charAt(0) }}
                        </div>
                        <span class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">{{ userName }}</span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div 
                        v-if="showProfileDropdown"
                        class="absolute right-0 mt-3 w-48 glass-card border border-white/10 rounded-2xl shadow-xl py-2 z-30"
                    >
                        <Link 
                            :href="route('profile.edit')" 
                            class="block px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/[0.04] transition-colors"
                        >
                            Profile Settings
                        </Link>
                        <Link 
                            :href="route('logout')" 
                            method="post" 
                            as="button" 
                            class="block w-full text-left px-4 py-2 text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-white/[0.04] transition-colors"
                        >
                            Log Out
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Main Panel -->
            <main class="flex-1 p-4 sm:p-8 overflow-y-auto">
                
                <!-- Welcome alerts/actions bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <div>
                        <h2 class="text-2xl font-extrabold text-white tracking-tight">Category Management</h2>
                        <p class="text-slate-400 text-xs mt-1">Configure lookbook categories with custom collection banner graphics.</p>
                    </div>
                    
                    <button 
                        @click="showAddModal = true"
                        class="glass-button-primary text-xs py-2.5 px-5 rounded-xl border border-white/10 font-bold flex items-center gap-1.5 hover:scale-[1.02] active:scale-[0.98] transition-all"
                    >
                        <span>+ Add Category</span>
                    </button>
                </div>

                <!-- Flash messages -->
                <div v-if="$page.props.flash?.success" class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">
                    {{ $page.props.flash?.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold">
                    {{ $page.props.flash?.error }}
                </div>

                <!-- Categories Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="cat in categories" 
                        :key="cat.id"
                        class="glass-card p-4 border-white/5 flex flex-col justify-between group hover:border-white/10 transition-all duration-300"
                    >
                        <div>
                            <!-- Category Image Graphic -->
                            <div class="relative aspect-[16/9] rounded-2xl overflow-hidden mb-4 bg-slate-900/60 border border-white/5">
                                <img 
                                    v-if="cat.image_path" 
                                    :src="cat.image_path" 
                                    :alt="cat.name" 
                                    class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                                />
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-600 gap-1.5">
                                    <svg class="w-8 h-8 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[10px] font-bold tracking-widest uppercase text-slate-600">No Image Banner</span>
                                </div>
                            </div>

                            <h3 class="font-extrabold text-white text-base tracking-tight mb-1">{{ cat.name }}</h3>
                            <span class="text-[10px] font-mono text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20 uppercase tracking-wider inline-block mb-3">
                                {{ cat.slug }}
                            </span>
                            <p class="text-slate-400 text-xs leading-relaxed line-clamp-2 mb-4">
                                {{ cat.description || 'No description provided for this collection.' }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-white/[0.04] flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                {{ cat.products_count }} Products
                            </span>
                            
                            <div class="flex gap-2">
                                <button 
                                    @click="openEditModal(cat)"
                                    class="glass-button text-[10px] font-bold py-1.5 px-3 rounded-lg border-white/5 text-indigo-400 hover:text-indigo-300"
                                >
                                    Edit
                                </button>
                                <button 
                                    @click="deleteCategory(cat.id)"
                                    :disabled="cat.products_count > 0"
                                    :class="[cat.products_count > 0 ? 'opacity-40 cursor-not-allowed text-slate-600' : 'text-rose-400 hover:text-rose-300']"
                                    class="glass-button text-[10px] font-bold py-1.5 px-3 rounded-lg border-white/5"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Add Category Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="showAddModal = false" class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>
            
            <div class="glass-card w-full max-w-md p-6 relative z-10 border border-white/10 shadow-[0_24px_50px_-12px_rgba(0,0,0,0.7)]">
                <div class="flex items-center justify-between border-b border-white/[0.06] pb-3 mb-4">
                    <h3 class="text-lg font-bold text-white">Add New Category</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-white transition-colors">✕</button>
                </div>
                
                <form @submit.prevent="submitAdd" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Category Name</label>
                        <input 
                            v-model="addForm.name" 
                            type="text" 
                            required
                            placeholder="e.g. Suits, Knitwear"
                            class="glass-input text-sm"
                        />
                        <span v-if="addForm.errors.name" class="text-xs text-rose-400 mt-1 block ml-1">{{ addForm.errors.name }}</span>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Description</label>
                        <textarea 
                            v-model="addForm.description" 
                            class="glass-input text-sm h-20 resize-none"
                            placeholder="Brief details about items in this collection..."
                        ></textarea>
                        <span v-if="addForm.errors.description" class="text-xs text-rose-400 mt-1 block ml-1">{{ addForm.errors.description }}</span>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Category Banner Image</label>
                        
                        <!-- Upload Slot -->
                        <div class="flex gap-4 items-center">
                            <div class="w-24 h-16 rounded-xl overflow-hidden bg-slate-900 border border-white/10 shrink-0 flex items-center justify-center relative">
                                <img v-if="addImagePreview" :src="addImagePreview" class="w-full h-full object-cover" />
                                <span v-else class="text-[10px] text-slate-600 font-bold uppercase">No Image</span>
                            </div>
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    @change="handleAddImageUpload"
                                    class="hidden" 
                                    id="add_cat_image"
                                />
                                <label 
                                    for="add_cat_image" 
                                    class="glass-button text-xs py-2.5 px-4 rounded-xl border border-white/10 text-center block cursor-pointer hover:bg-white/[0.04]"
                                >
                                    Select Image Banner
                                </label>
                            </div>
                        </div>
                        <span v-if="addForm.errors.image" class="text-xs text-rose-400 mt-1 block ml-1">{{ addForm.errors.image }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-white/[0.06] flex justify-end gap-3">
                        <button 
                            type="button" 
                            @click="showAddModal = false" 
                            class="glass-button text-xs py-2 px-4 rounded-xl border border-white/5 text-slate-300 hover:text-white"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="addForm.processing"
                            class="glass-button-primary text-xs py-2 px-5 rounded-xl border border-white/10 font-bold disabled:opacity-50"
                        >
                            {{ addForm.processing ? 'Saving...' : 'Save Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div @click="showEditModal = false" class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>
            
            <div class="glass-card w-full max-w-md p-6 relative z-10 border border-white/10 shadow-[0_24px_50px_-12px_rgba(0,0,0,0.7)]">
                <div class="flex items-center justify-between border-b border-white/[0.06] pb-3 mb-4">
                    <h3 class="text-lg font-bold text-white">Edit Category</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-white transition-colors">✕</button>
                </div>
                
                <form @submit.prevent="submitUpdate" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Category Name</label>
                        <input 
                            v-model="editForm.name" 
                            type="text" 
                            required
                            placeholder="Category name"
                            class="glass-input text-sm"
                        />
                        <span v-if="editForm.errors.name" class="text-xs text-rose-400 mt-1 block ml-1">{{ editForm.errors.name }}</span>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Description</label>
                        <textarea 
                            v-model="editForm.description" 
                            class="glass-input text-sm h-20 resize-none"
                            placeholder="Description"
                        ></textarea>
                        <span v-if="editForm.errors.description" class="text-xs text-rose-400 mt-1 block ml-1">{{ editForm.errors.description }}</span>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Category Banner Image</label>
                        
                        <div class="flex gap-4 items-center">
                            <div class="w-24 h-16 rounded-xl overflow-hidden bg-slate-900 border border-white/10 shrink-0 flex items-center justify-center relative">
                                <img v-if="editImagePreview" :src="editImagePreview" class="w-full h-full object-cover" />
                                <span v-else class="text-[10px] text-slate-600 font-bold uppercase">No Image</span>
                            </div>
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    @change="handleEditImageUpload"
                                    class="hidden" 
                                    id="edit_cat_image"
                                />
                                <label 
                                    for="edit_cat_image" 
                                    class="glass-button text-xs py-2.5 px-4 rounded-xl border border-white/10 text-center block cursor-pointer hover:bg-white/[0.04]"
                                >
                                    Change Image Banner
                                </label>
                            </div>
                        </div>
                        <span v-if="editForm.errors.image" class="text-xs text-rose-400 mt-1 block ml-1">{{ editForm.errors.image }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-white/[0.06] flex justify-end gap-3">
                        <button 
                            type="button" 
                            @click="showEditModal = false" 
                            class="glass-button text-xs py-2 px-4 rounded-xl border border-white/5 text-slate-300 hover:text-white"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            :disabled="editForm.processing"
                            class="glass-button-primary text-xs py-2 px-5 rounded-xl border border-white/10 font-bold disabled:opacity-50"
                        >
                            {{ editForm.processing ? 'Saving...' : 'Update Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
