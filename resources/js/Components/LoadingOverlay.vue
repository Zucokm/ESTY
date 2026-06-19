<script setup>
defineProps({
    active: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: 'Loading...',
    },
    fullscreen: {
        type: Boolean,
        default: false,
    }
});
</script>

<template>
    <Transition name="fade">
        <div 
            v-if="active" 
            :class="[
                fullscreen ? 'fixed inset-0 z-[100]' : 'absolute inset-0 z-30 rounded-[inherit]',
                'flex flex-col items-center justify-center bg-slate-950/70 backdrop-blur-md border border-white/5'
            ]"
        >
            <div class="flex flex-col items-center gap-4 p-6 text-center">
                <!-- Glowing Spinner -->
                <div class="relative w-16 h-16">
                    <!-- Outer glowing ring -->
                    <div class="absolute inset-0 rounded-full border-4 border-indigo-500/10 border-t-indigo-500 animate-spin"></div>
                    <!-- Inner secondary glowing ring -->
                    <div class="absolute inset-2 rounded-full border-4 border-purple-500/10 border-b-purple-500 animate-spin-reverse"></div>
                    <!-- Center decorative point -->
                    <div class="absolute inset-5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 shadow-[0_0_15px_rgba(99,102,241,0.5)]"></div>
                </div>

                <!-- Custom Message -->
                <p 
                    v-if="message" 
                    class="text-sm font-bold text-slate-300 tracking-wider uppercase animate-pulse select-none"
                >
                    {{ message }}
                </p>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.animate-spin-reverse {
    animation: spin-reverse 1.2s linear infinite;
}

@keyframes spin-reverse {
    from {
        transform: rotate(360deg);
    }
    to {
        transform: rotate(0deg);
    }
}

/* Vue Fade Transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
