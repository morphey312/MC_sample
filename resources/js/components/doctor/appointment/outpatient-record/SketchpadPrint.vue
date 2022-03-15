<template>
    <div v-bind:style="{width: width+'px', height: height + 'px' }" style="position: relative;">
        <div v-html="html" ref="sketchpadWrapper">
        </div>
        <img v-if="readonly && imgData" :src="imgData" alt="" class="sketchpad_readonly_img" style=" position: absolute;
        top: 0;
        left: 0;">
    </div>
</template>

<script>
import Sketchpad from '@/vendors/responsive-sketchpad'

export default {
    props: {
        readonly: {
            type: Boolean,
            default: false,
        },
        html: String,
        width: String,
        height: String,
        value: String,
    },
    data() {
        return {
            sketchpad: null,
            imgData: null
        }
    },
    computed: {
        parsedValue(){
            return JSON.parse(this.value);
        }
    },
    beforeMount() {
        let sketchPadBlock = document.createElement("div");

        this.sketchpad = new Sketchpad(sketchPadBlock, {
            width: this.width,
            height: this.height,
            line: {
                color: '#000000',
                size: 2
            },
            onDrawEnd: () => {
                this.inputUpdated();
            }
        })

        if(this.value){
            this.sketchpad.loadJSON(this.parsedValue)
        }

        if(this.value && this.readonly){
            this.sketchpad.canvas.style.display = 'none';
            this.imgData = this.sketchpad.toDataURL();
        }
    },
};
</script>

<style lang="scss">
.sketchpad__wrap {
    position: relative;
    display: block;
}
</style>
