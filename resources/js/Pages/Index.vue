<script setup>
import { defineProps } from 'vue';
import { router } from '@inertiajs/vue3';


const props = defineProps({
    channels: {
        type: Array,
        required: true,
    },
});

</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold mb-4">Lista de canales</h1>
        <button
            v-for="channel in props.channels" :key="channel.id"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            @click="router.get(route('channels.show', { channel: channel.id }))"
            >
            {{ channel.name }}
        </button>
        
    </div>
</template>