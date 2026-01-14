<template>
    <div class="relative">
        <canvas 
            ref="canvasRef" 
            :width="width" 
            :height="height"
            class="image-pixelated border border-gray-500 shadow-xl bg-white"
            :style="{ width: `${width * zoom}px`, height: `${height * zoom}px` }"
        ></canvas>
        
        <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white text-xs p-1">
            Reverb Status: {{ connectionStatus }}
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    projectId: Number,
    canvasId: Number,
    width: Number,
    height: Number,
    bgColor: String
});

const canvasRef = ref(null);
const zoom = ref(10); // 10x zoom for pixel art look
const connectionStatus = ref('Disconnected');

onMounted(() => {
    const ctx = canvasRef.value.getContext('2d');
    
    ctx.fillStyle = props.bgColor || '#ffffff';
    ctx.fillRect(0, 0, props.width, props.height);
    
    console.log(`Canvas initialized for Project ${props.projectId}, Canvas ${props.canvasId} [${props.width}x${props.height}]`);
    
});
</script>

<style scoped>
.image-pixelated {
    image-rendering: pixelated;
}
</style>
