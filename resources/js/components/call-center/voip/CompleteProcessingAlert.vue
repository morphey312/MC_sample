<template>
    <div
        v-if="!isCallCenter && isProcessing"
        class="complete-processing-alert">
        <div 
            class="body" 
            :class="dragging ? 'dragging' : ''"
            draggable="true"
            :style="style"
            @dragstart="dragstart"
            @dragend="dragend">
            <svg-icon name="warning" class="icon-small icon-red" />
            <div class="message">
                {{ __('Не забудьте') }} <a href="#" @click.prevent="goVoip">{{ __('завершить обработку') }}</a>.
            </div>
        </div>
    </div>
</template>

<script>
import VoipMixins from '@/components/call-center/mixins/voip';

export default {
    mixins: [
        VoipMixins,
    ],
    computed: {
        isProcessing() {
            return this.$store.state.processState.processing;
        },
        style() {
            return {
                left: `${this.currentX}px`,
                top: `${this.currentY}px`,
            };
        },
    },
    data() {
        return {
            dragging: false,
            currentX: 0,
            currentY: 0,
            startX: 0,
            startY: 0,
        };
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
    },
    watch: {
        isProcessing(val) {
            this.currentX = 0;
            this.currentY = 0;
        },
        isCallCenter(val) {
            this.currentX = 0;
            this.currentY = 0;
        },
    },
}
</script>