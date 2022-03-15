<template>
    <span class="inline-checklist">
        <el-popover
            placement="bottom"
            v-model="showPopover">
            <el-checkbox-group v-model="val">
                <div 
                    v-for="opt in options" 
                    class="list-option">
                    <el-checkbox 
                        :label="opt.id"
                        :key="opt.id">
                        {{ opt.value }}
                    </el-checkbox>
                </div>
            </el-checkbox-group>
            <div class="popover-footer">
                <el-button 
                    size="mini" 
                    @click="cancel">
                    {{ __('Отмена') }}
                </el-button>
                <el-button 
                    type="primary" 
                    size="mini" 
                    @click="apply">
                    {{ __('Ок') }}
                </el-button>
            </div>
            <span
                slot="reference"
                class="static"
                @click="togglePopover">
                {{ formatValue(list) }}
            </span>
        </el-popover>
    </span>
</template>
<script>
import InlineInput from '@/mixins/inline-input';

export default {
    mixins: [
        InlineInput,
    ],
    props: {
        value: Array,
        options: Array,
    },
    data() {
        return {
            showPopover: false,
            list: [],
        };
    },
    mounted() {
        this.updateList();
    },
    methods: {
        updateList() {
            this.list = this.options.filter((opt) => this.value.indexOf(opt.id) !== -1);
        },
        togglePopover() {
            this.showPopover = !this.showPopover;
        },
        cancel() {
            this.showPopover = false;
        },
        apply() {
            if (this.confirm()) {
                this.showPopover = false;
            }
        },
        focusByTab() {
            this.showPopover = true;
        },
        formatValue(value) {
            return this.formatter !== undefined 
                ? this.formatter(value) 
                : this.$formatter.listFormat(value, 'value');
        },
    },
    watch: {
        options(val) {
            this.updateList();
        },
        value(val) {
            this.updateList();
        },
        showPopover(val) {
            if (val) {
                if (!this.editMode) {
                    this.edit();
                }
            } else {
                if (this.editMode) {
                    this.revert();
                }
            }
        },
    },
};
</script>