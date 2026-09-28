<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import NavigationBar from '@/Components/NavigationBar.vue';
import WishlistDrawer from '@/Components/WishlistDrawer.vue';

const showWishlistDrawer = ref(false);

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: ''
});

const submit = () => {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Contact Us - ESTY" />

    <div class="min-h-screen relative overflow-hidden pb-20 selection:bg-indigo-500/30 selection:text-indigo-200">
        <NavigationBar 
            active="contact" 
            @open-wishlist="showWishlistDrawer = true" 
        />

        <main class="pt-32 px-4 max-w-3xl mx-auto space-y-8 relative z-10">
            <div class="text-center">
                <h1 class="text-4xl font-black text-white tracking-tight mb-2">Get in Touch</h1>
                <p class="text-slate-400 font-medium">Have a question about your order or our collections? Drop us a message.</p>
            </div>

            <div class="glass-card p-6 sm:p-10 border-white/10 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-tr from-white/[0.01] to-transparent pointer-events-none"></div>
                
                <form @submit.prevent="submit" class="space-y-5 relative z-10">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Your Name</label>
                            <input type="text" v-model="form.name" class="glass-input" required placeholder="John Doe" />
                            <span v-if="form.errors.name" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.name }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Email Address</label>
                            <input type="email" v-model="form.email" class="glass-input" required placeholder="name@example.com" />
                            <span v-if="form.errors.email" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.email }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Subject</label>
                        <input type="text" v-model="form.subject" class="glass-input" placeholder="What is this about?" />
                        <span v-if="form.errors.subject" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.subject }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 ml-1">Message</label>
                        <textarea v-model="form.message" rows="5" class="glass-input resize-none" required placeholder="How can we help you?"></textarea>
                        <span v-if="form.errors.message" class="text-xs text-rose-400 mt-1 block ml-1">{{ form.errors.message }}</span>
                    </div>

                    <div class="pt-4 text-right">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="glass-button-primary px-8 py-3.5 font-bold rounded-xl transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 inline-flex items-center"
                        >
                            <span v-if="form.processing" class="inline-block animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <WishlistDrawer :show="showWishlistDrawer" @close="showWishlistDrawer = false" />
    </div>
</template>
