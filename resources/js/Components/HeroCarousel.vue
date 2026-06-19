<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    slides: {
        type: Array,
        required: true
    }
});

const currentIndex = ref(0);
const autoplayTimer = ref(null);
const isPaused = ref(false);

const nextSlide = () => {
    if (!props.slides || props.slides.length === 0) return;
    currentIndex.value = (currentIndex.value + 1) % props.slides.length;
};

const prevSlide = () => {
    if (!props.slides || props.slides.length === 0) return;
    currentIndex.value = (currentIndex.value - 1 + props.slides.length) % props.slides.length;
};

const setSlide = (idx) => {
    currentIndex.value = idx;
};

const startAutoplay = () => {
    autoplayTimer.value = setInterval(() => {
        if (!isPaused.value) {
            nextSlide();
        }
    }, 6000);
};

const stopAutoplay = () => {
    if (autoplayTimer.value) {
        clearInterval(autoplayTimer.value);
    }
};

onMounted(() => {
    startAutoplay();
});

onUnmounted(() => {
    stopAutoplay();
});
</script>

<template>
    <section 
        class="w-full relative h-[70vh] sm:h-[80vh] md:h-[85vh] min-h-[500px] max-h-[850px] overflow-hidden bg-slate-950 border-b border-white/[0.04] select-none"
        @mouseenter="isPaused = true"
        @mouseleave="isPaused = false"
    >
        <!-- Slides Wrapper -->
        <div class="w-full h-full relative">
            <div 
                v-for="(slide, idx) in slides" 
                :key="idx"
                class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
                :class="currentIndex === idx ? 'opacity-100 z-10 pointer-events-auto' : 'opacity-0 z-0 pointer-events-none'"
            >
                <!-- Background Image & Zoom Zoom animation -->
                <div class="absolute inset-0 w-full h-full overflow-hidden">
                    <img 
                        :src="slide.image_path" 
                        alt="Hero Banner"
                        class="w-full h-full object-cover object-center transition-transform duration-[6000ms] ease-out scale-100"
                        :class="currentIndex === idx ? 'scale-105' : 'scale-100'"
                    />
                    <!-- Radial Dark Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-slate-950/20 z-10"></div>
                </div>

                <!-- Slide Details Overlay -->
                <div class="absolute inset-0 z-20 flex items-center">
                    <div class="max-w-7xl mx-auto w-full px-6 md:px-12 flex flex-col items-center lg:items-start text-center lg:text-left">
                        
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-black/40 border border-white/10 backdrop-blur-md mb-6 transform translate-y-8 transition-transform duration-700 delay-100" :class="currentIndex === idx ? 'translate-y-0' : 'translate-y-8'">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                            <span class="text-[10px] sm:text-xs font-bold tracking-widest text-slate-300 uppercase">{{ slide.badge }}</span>
                        </div>

                        <!-- Heading -->
                        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold tracking-tight text-white mb-6 leading-[1.15] max-w-4xl transform translate-y-8 transition-transform duration-700 delay-200" :class="currentIndex === idx ? 'translate-y-0' : 'translate-y-8'">
                            {{ slide.title }} <br/>
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-200 via-purple-300 to-pink-200">{{ slide.highlight }}</span>
                        </h1>

                        <!-- Description -->
                        <p class="text-sm sm:text-base md:text-lg text-slate-300 max-w-2xl mb-8 leading-relaxed font-medium transform translate-y-8 transition-transform duration-700 delay-300" :class="currentIndex === idx ? 'translate-y-0' : 'translate-y-8'">
                            {{ slide.description }}
                        </p>

                        <!-- CTA Button -->
                        <div class="transform translate-y-8 transition-transform duration-700 delay-[400ms]" :class="currentIndex === idx ? 'translate-y-0' : 'translate-y-8'">
                            <a 
                                :href="slide.cta_link || '#shop'" 
                                class="glass-button-primary px-8 py-3.5 rounded-full text-sm font-bold tracking-wide hover:scale-105 active:scale-95 transition-all inline-flex items-center gap-2 shadow-lg shadow-indigo-500/10"
                            >
                                {{ slide.cta_text || 'Explore Collection' }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Navigation Chevrons -->
        <button 
            @click="prevSlide" 
            class="absolute left-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full border border-white/5 bg-black/20 hover:bg-white/[0.08] backdrop-blur-md flex items-center justify-center text-slate-300 hover:text-white transition-all cursor-pointer active:scale-95"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button 
            @click="nextSlide" 
            class="absolute right-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full border border-white/5 bg-black/20 hover:bg-white/[0.08] backdrop-blur-md flex items-center justify-center text-slate-300 hover:text-white transition-all cursor-pointer active:scale-95"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Navigation Dots -->
        <div class="absolute bottom-8 left-0 right-0 z-30 flex justify-center gap-3">
            <button 
                v-for="(_, idx) in slides" 
                :key="idx"
                @click="setSlide(idx)"
                class="w-8 h-1.5 rounded-full transition-all duration-300 cursor-pointer"
                :class="currentIndex === idx ? 'bg-white w-10' : 'bg-white/30 hover:bg-white/50'"
            ></button>
        </div>
    </section>
</template>
