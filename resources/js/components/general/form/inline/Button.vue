<template>
    <button
        type="button"
        ref="button"
        :class="cssClass"
        @click="action"
        @keydown.enter.tab.prevent="controlKeyPressed">
        <slot />
    </button>
</template>
<script>
import InlineInput from '@/mixins/inline-input';

export default {
    mixins: [
        InlineInput,
    ],
    computed: {
        cssClass() {
            return [
                'el-button',
                'inline-button',
                'el-button--default',
                this.class,
            ];
        },
    },
    methods: {
        focus() {
            this.$refs.button.focus();
        },
        controlKeyPressed(e) {
            switch (e.keyCode) {
                case 13: 
                    this.action(); 
                    break;
                case 9: 
                    this.nextTabIndex();
                    break;    
            }
        },
        focusByTab() {
            this.focus();
        },
        action() {
            this.$emit('click');
        },
    }
};
</script>