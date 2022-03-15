<template>
    <div v-bind:style="{width: width+'px', height: height + 'px' }" style="position: relative;" >
        <div v-html="html" ref="sketchpadWrapper" :class="{readonly : readonly}">
        </div>
        <div class="sketchpad-tools" style="top: 0;" v-show="!readonly">
            <svg-icon name="arrow-circle" class="icon-small icon-blue cursor-pointer" style="transform: scaleX(-1);" :title="__('Назад')" @click="undo"></svg-icon>
            <svg-icon name="arrow-circle" class="icon-small icon-blue cursor-pointer" :title="__('Вперёд')" @click="redo"
                      style="margin-top: 10px; width: 20px;"></svg-icon>
            <svg-icon name="delete" class="icon-small icon-blue cursor-pointer" @click="clear" :title="__('Очистить')"
                      style="margin-top: 10px; width: 20px;"></svg-icon>
            <input type="color" class="cursor-pointer" style="margin-top: 10px; width: 20px;" :title="__('Цвет')" @input="color">
        </div>
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
    mounted() {

        let sketchPadBlock = this.$refs.sketchpadWrapper.getElementsByTagName('sketchpad')[0];

        if(this.readonly){
            sketchPadBlock.classList.add('readonly')
        }

        this.sketchpad = new Sketchpad(sketchPadBlock, {
            width: this.width,
            height: this.height,
            readOnly: this.readonly,
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
    },
    methods: {
        clear() {
            this.sketchpad.clear();
            this.inputUpdated();
        },
        undo() {
            this.sketchpad.undo()
            this.inputUpdated();
        },
        redo() {
            this.sketchpad.redo();
            this.inputUpdated();
        },
        color(event) {
            this.sketchpad.setLineColor(event.target.value);
            this.inputUpdated();
        },
        inputUpdated(){
            this.$emit('input', JSON.stringify(this.sketchpad.toJSON()));
        }
    }
};
</script>
