<template>
    <span v-if="hasIcon">
        <img 
            v-if="src" 
            style="height: 20px;"
            :src="src" />
    </span>
</template>

<script>
import LoadImageMixin from './mixin/load-image';

export default {
    mixins: [
        LoadImageMixin,
    ],
    props: {
        model: Object,
    },
    computed: {
        hasIcon() {
            return this.model.attachments.length !== 0;
        },
        attachment() {
            return this.hasIcon ? this.model.attachments_data[0] : null;
        },
    },
    mounted() {
        if (this.hasIcon) {
            this.getImage();    
        }
    },
    watch: {
        ['attachment.id'](val) {
            if (val) {
                this.getImage();
            } else {
                this.src = null;
            }
        }
    },
}   
</script>