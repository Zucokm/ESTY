<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md">
        <div class="glass-card w-full max-w-lg border-white/10 shadow-2xl p-6 sm:p-8 rounded-[2rem] space-y-6 transform scale-100 transition-all duration-300">
            <div class="flex justify-between items-center border-b border-white/[0.04] pb-4">
                <div>
                    <h3 class="text-xl font-bold text-white tracking-tight">Request Return</h3>
                    <p class="text-xs text-slate-400 mt-1">Order #VR-{{ order?.id }}</p>
                </div>
                <button @click="$emit('close')" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form @submit.prevent="submitReturn" class="space-y-5">
                <div>
                    <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Reason for Return</label>
                    <select v-model="form.reason" class="glass-input w-full py-2.5 px-4 text-sm bg-slate-900 appearance-none" required>
                        <option value="" disabled>Select a reason</option>
                        <option value="Damaged/Defective">Damaged or Defective Item</option>
                        <option value="Wrong Item">Received Wrong Item</option>
                        <option value="Size/Fit Issue">Size or Fit Issue</option>
                        <option value="Not as Expected">Item not as expected</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Detailed Description</label>
                    <textarea v-model="form.description" rows="3" class="glass-input w-full py-2.5 px-4 text-sm resize-none" placeholder="Please explain the issue in detail..." required></textarea>
                </div>

                <div>
                    <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">Photo Evidence (Required for damaged/wrong items)</label>
                    <div class="relative">
                        <input type="file" @change="handleFileUpload" multiple accept="image/*" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-500/10 file:text-indigo-400 hover:file:bg-indigo-500/20" />
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">You can upload multiple photos. Max 5MB per photo.</p>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-white/[0.04]">
                    <button type="button" @click="$emit('close')" class="glass-button py-2.5 px-5 rounded-xl font-bold text-xs">
                        Cancel
                    </button>
                    <button type="submit" :disabled="form.processing" class="bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs py-2.5 px-6 rounded-xl hover:shadow-lg hover:shadow-orange-500/25 active:scale-95 transition-all duration-200 disabled:opacity-50">
                        {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    order: Object
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    reason: '',
    description: '',
    images: []
});

const handleFileUpload = (e) => {
    form.images = Array.from(e.target.files);
};

const submitReturn = () => {
    form.post(route('returns.store', props.order.id), {
        onSuccess: () => {
            form.reset();
            emit('success');
            emit('close');
        }
    });
};
</script>
