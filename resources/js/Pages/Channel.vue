<script setup>
import { defineProps, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    channel: {
        type: Object,
        required: true,
    },
    videos: {
        type: Array,
        required: true,
    },
    url: {
        type: String,
        required: false,
    },
});

const videos = ref([...props.videos]);

const moveUp = (index) => {
    if (index > 0) {
        const temp = videos.value[index];
        videos.value[index] = videos.value[index - 1];
        videos.value[index - 1] = temp;
    }
};

const moveDown = (index) => {
    if (index < videos.value.length - 1) {
        const temp = videos.value[index];
        videos.value[index] = videos.value[index + 1];
        videos.value[index + 1] = temp;
    }
};

const moveToTop = (index) => {
    if (index > 0) {
        const temp = videos.value.splice(index, 1)[0];
        videos.value.unshift(temp);
    }
};

const moveToBottom = (index) => {
    if (index < videos.value.length - 1) {
        const temp = videos.value.splice(index, 1)[0];
        videos.value.push(temp);
    }
};

const deleteVideo = (index) => {
    videos.value.splice(index, 1);
};

function renderVideo() {
    router.post(route('videos.render', { channel: props.channel.id }), {
        videos: videos.value.map((video) => video.watermarked_video),
    });
}
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold mb-4">Videos en cola pendientes para renderizar</h1>
        <h2 class="text-xl font-semibold mb-4">Canal: {{ props.channel.name }}</h2>

        <div class="grid gap-4">
            <div v-for="(video, index) in videos" :key="video.id" class="mb-4 flex items-center">
                <button @click="moveUp(index)" class="subir bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                    &lt;
                </button>
                <button @click="moveDown(index)" class="bajar bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">&lt;</button>
                <button @click="moveToTop(index)" class="move-top bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">Top</button>
                <button @click="moveToBottom(index)" class=" bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mr-2">Bottom</button>
                <a :href="video.url" target="_blank" class="text-blue-500 hover:underline mr-4">
                    <img :src="video.thumbnail" alt="Thumbnail" class="w-32 h-24">
                </a>
                <button @click="deleteVideo(index)" class="delete-video bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Delete
                </button>
            </div>
        </div>

        <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" @click="renderVideo">
            Renderizar vídeo
        </button>
        <button v-if="props.url" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="window.open(props.url)">
            Descargar vídeo
        </button>
    </div>
</template>

<style scoped>
.subir {
    transform: rotate(90deg);
}

.bajar {
    transform: rotate(-90deg);
}

.move-top {
    transform: rotate(0deg);
}

.move-bottom {
    transform: rotate(180deg);
}
</style>
