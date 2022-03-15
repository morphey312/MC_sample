<template>
    <span class="inline-input">
        <span
            v-if="!editMode"
            class="static"
            @click="focus">
            {{ formatValue(value) }}
        </span>
        <div
            v-if="editMode"
            class="el-input">
            <input
                ref="input"
                v-model="val"
                class="el-input__inner"
                @blur="confirmAndBlur"
                @keydown.enter.tab.esc.prevent="controlKeyPressed" />
        </div>
    </span>
</template>
<script>
import InlineInput from '@/mixins/inline-input';

export default {
    mixins: [
        InlineInput,
    ],
    props: {
        value: [String, Number],
    },
    methods: {
        focus() {
            this.edit();
            this.$nextTick(() => {
                this.$refs.input.select();
                this.$refs.input.focus();
            });
        },
        controlKeyPressed(e) {
            switch (e.keyCode) {
                case 27:
                    this.revert();
                    break;
                case 9:
                case 13:
                    this.confirm();
                    break;
            }
        },
        confirmAndBlur() {
            if (this.editMode) {
                if (!this.confirm(false)) {
                    this.revert();
                }
            }
        },
        focusByTab() {
            this.focus();
        },
        formatValue(value) {
            return this.formatter !== undefined
                ? this.formatter(value)
                : value;
        },
    }
};
</script>
