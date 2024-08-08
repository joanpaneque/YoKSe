<script setup>
import { defineProps } from 'vue';
import { router } from '@inertiajs/vue3';


const props = defineProps({
    channel: {
        type: Array,
        required: true,
    },
    videos: {
        type: Array,
        required: true,
    },
});

</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold mb-4">Videos en cola pendientes para renderizar</h1>
        <h2 class="text-xl font-semibold mb-4">Canal: {{ props.channel.name }}</h2>

        <div class="grid gap-4">
            <div v-for="video in props.videos" :key="video.id" class="mb-4">
                <a :href="video.url" target="_blank" class="text-blue-500 hover:underline">{{ video.url }}</a>
            </div>

        </div>

        
    </div>
</template>