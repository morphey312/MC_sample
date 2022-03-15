<template>
    <div
        class="webcam-preview__wrap">
        <div
            class="drag-container"
            :class="dragging ? 'dragging' : ''"
            draggable="true"
            :style="style"
            @dragstart="dragstart"
            @dragend="dragend">
            <div class="preview-container" ref="preview"></div>
        </div>
    </div>
</template>

<script>
const {createLocalVideoTrack} = require('twilio-video');


export default {
    props: {
        appointment: Object,
    },
    computed: {
        style() {
            return {
                left: `${this.currentX}px`,
                top: `${this.currentY}px`,
            };
        },
    },
    data() {
        return {
            started: false,
            dragging: false,
            currentX: 0,
            currentY: 0,
            startX: 0,
            startY: 0,
            previewVideoTrack: null
        };
    },
    mounted() {
        createLocalVideoTrack().then(track => {
            const localMediaContainer = this.$refs.preview;
            localMediaContainer.appendChild(track.attach());
            this.previewVideoTrack = track;
        });
    },
    beforeDestroy() {
        if(this.previewVideoTrack){
            this.previewVideoTrack.stop();
        }
    },
    methods: {
        dragstart(e) {
            this.dragging = true;
            this.startX = e.clientX;
            this.startY = e.clientY;
        },
        dragend(e) {
            this.dragging = false;
            this.currentX += (e.clientX - this.startX);
            this.currentY += (e.clientY - this.startY);
        },
        hide() {
            this.$emit('hidden');
        },
        toggleCameraPreview() {
            this.cameraPreview = !this.cameraPreview;
            this.$emit('cameraPreview', this.cameraPreview);
        },
    },
}
</script>
