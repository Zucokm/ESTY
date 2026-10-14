<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md overflow-y-auto">
        <div class="glass-card w-full max-w-xl border border-white/10 shadow-2xl p-6 sm:p-8 rounded-[2rem] space-y-6 my-8 relative animate-fade-in">
            <!-- Header -->
            <div class="flex justify-between items-start border-b border-white/[0.06] pb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-orange-500/10 text-orange-400 border border-orange-500/20">
                            RMA System
                        </span>
                        <span class="text-xs text-slate-400">Order #VR-{{ order?.id }}</span>
                    </div>
                    <h3 class="text-xl font-black text-white tracking-tight">Request Return & Refund</h3>
                </div>
                <button 
                    type="button"
                    @click="$emit('close')" 
                    class="text-slate-400 hover:text-white transition-colors p-1.5 rounded-xl hover:bg-white/5"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Error Banner -->
            <div v-if="form.errors.message" class="p-4 bg-rose-500/10 text-rose-300 border border-rose-500/30 rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ form.errors.message }}</span>
            </div>

            <form @submit.prevent="submitReturn" class="space-y-5">
                <!-- 1. Visual Reason Selector -->
                <div>
                    <label class="block text-slate-300 text-xs font-bold uppercase tracking-wider mb-2.5">
                        Select Reason <span class="text-rose-400">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <button
                            v-for="item in reasons"
                            :key="item.value"
                            type="button"
                            @click="form.reason = item.value"
                            :class="[
                                form.reason === item.value 
                                    ? 'bg-indigo-600/20 border-indigo-500 text-white shadow-md shadow-indigo-500/15 ring-1 ring-indigo-500/50' 
                                    : 'bg-white/[0.02] border-white/5 text-slate-400 hover:text-slate-200 hover:bg-white/[0.04]'
                            ]"
                            class="p-3 rounded-xl border text-left flex flex-col justify-between gap-2 transition-all duration-200 cursor-pointer"
                        >
                            <span class="text-base">{{ item.icon }}</span>
                            <span class="text-xs font-bold leading-tight">{{ item.label }}</span>
                        </button>
                    </div>
                    <div v-if="form.errors.reason" class="text-rose-400 text-xs mt-1.5 ml-1">{{ form.errors.reason }}</div>
                </div>

                <!-- 2. Detailed Description -->
                <div>
                    <label class="block text-slate-300 text-xs font-bold uppercase tracking-wider mb-2">
                        Explain the Issue <span class="text-rose-400">*</span>
                    </label>
                    <textarea 
                        v-model="form.description" 
                        rows="3" 
                        class="glass-input w-full py-3 px-4 text-sm resize-none rounded-xl placeholder:text-slate-500 focus:border-indigo-500 transition-colors" 
                        placeholder="Please describe the defect, sizing issue, or why you'd like to return..." 
                        required
                    ></textarea>
                    <div v-if="form.errors.description" class="text-rose-400 text-xs mt-1 ml-1">{{ form.errors.description }}</div>
                </div>

                <!-- 3. Photo Evidence Upload with Live Previews -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-slate-300 text-xs font-bold uppercase tracking-wider">
                            Photo Evidence <span class="text-slate-500 text-[11px] font-normal lowercase">(optional but recommended)</span>
                        </label>
                        <span class="text-[11px] text-slate-500">Max 5MB each</span>
                    </div>

                    <!-- Upload Dropzone -->
                    <label class="flex flex-col items-center justify-center p-5 rounded-2xl border-2 border-dashed border-white/10 hover:border-indigo-500/50 bg-white/[0.02] hover:bg-white/[0.04] cursor-pointer transition-all duration-200 group">
                        <input 
                            type="file" 
                            @change="handleFileUpload" 
                            multiple 
                            accept="image/*" 
                            class="hidden" 
                        />
                        <div class="w-10 h-10 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-300">Click to upload photos</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">JPEG, PNG, JPG (Photos of package, defect, or tag)</p>
                    </label>

                    <!-- Live Image Previews -->
                    <div v-if="previewUrls.length > 0" class="mt-3 flex flex-wrap gap-2.5">
                        <div 
                            v-for="(url, idx) in previewUrls" 
                            :key="idx" 
                            class="relative w-16 h-16 rounded-xl overflow-hidden border border-white/10 shadow-md group"
                        >
                            <img :src="url" class="w-full h-full object-cover" />
                            <button 
                                type="button" 
                                @click="removeImage(idx)" 
                                class="absolute top-1 right-1 w-5 h-5 bg-black/80 hover:bg-rose-500 text-white rounded-full flex items-center justify-center text-xs transition-colors shadow"
                                title="Remove photo"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                    <div v-if="form.errors.images" class="text-rose-400 text-xs mt-1.5 ml-1">{{ form.errors.images }}</div>
                </div>

                <!-- Return Guarantee Notice -->
                <div class="p-3.5 rounded-xl bg-indigo-500/5 border border-indigo-500/15 flex items-start gap-3">
                    <svg class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        <strong class="text-slate-300">3-Day Return Policy:</strong> Once submitted, an Admin review ticket will be opened where you can chat directly with our support team to verify the return and finalize your refund.
                    </p>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-white/[0.06]">
                    <button 
                        type="button" 
                        @click="$emit('close')" 
                        class="glass-button py-2.5 px-5 rounded-xl font-bold text-xs hover:bg-white/10 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="form.processing || !form.reason || !form.description.trim()" 
                        class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs py-2.5 px-6 rounded-xl hover:shadow-lg hover:shadow-indigo-600/30 active:scale-95 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <span v-if="form.processing" class="w-3.5 h-3.5 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Submitting Request...' : 'Submit Request' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    order: Object
});

const emit = defineEmits(['close', 'success']);

const reasons = [
    { value: 'Damaged/Defective', label: 'Damaged / Defect', icon: '📦' },
    { value: 'Wrong Item', label: 'Wrong Item Sent', icon: '🔄' },
    { value: 'Size/Fit Issue', label: 'Size / Fit Issue', icon: '📏' },
    { value: 'Not as Expected', label: 'Quality / Fabric', icon: '✨' },
    { value: 'Other', label: 'Other Reason', icon: '💬' }
];

const form = useForm({
    reason: '',
    description: '',
    images: []
});

const previewUrls = ref([]);

const handleFileUpload = (e) => {
    const files = Array.from(e.target.files);
    form.images = [...form.images, ...files];
    
    // Generate live object previews
    files.forEach(file => {
        previewUrls.value.push(URL.createObjectURL(file));
    });
};

const removeImage = (index) => {
    form.images.splice(index, 1);
    URL.revokeObjectURL(previewUrls.value[index]);
    previewUrls.value.splice(index, 1);
};

const submitReturn = () => {
    form.post(route('returns.store', props.order.id), {
        onSuccess: () => {
            form.reset();
            previewUrls.value = [];
            emit('success');
            emit('close');
        }
    });
};
</script>
