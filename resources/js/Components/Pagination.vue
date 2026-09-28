<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center gap-1 mt-6">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="px-4 py-2 text-sm font-medium text-slate-500 bg-white/[0.02] border border-white/[0.05] rounded-lg cursor-not-allowed"
                v-html="link.label"
            ></div>
            <Link
                v-else
                :href="link.url"
                class="px-4 py-2 text-sm font-medium border rounded-lg transition-colors"
                :class="[
                    link.active
                        ? 'bg-indigo-500/20 border-indigo-500/30 text-indigo-300'
                        : 'bg-white/[0.02] border-white/[0.05] text-slate-300 hover:bg-white/[0.05] hover:text-white',
                ]"
                v-html="link.label"
            />
        </template>
    </div>
</template>
