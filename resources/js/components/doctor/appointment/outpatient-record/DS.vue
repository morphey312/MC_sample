<template>
    <span class="ds-input">
        <label class="prefix">D</label>
        <input 
            v-if="readonly"
            :value="val" 
            readonly="readonly" /> 
        <el-popover
            v-else
            placement="bottom"
            trigger="manual"
            v-model="showPanel"
            popper-class="ds-input-popper"
            width="100px">
            <input 
                slot="reference"
                ref="input"
                v-model="val" 
                @focus="displayPanel"
                @blur="onBlur"
                @keydown="checkKey" /> 
            <el-button-group>
                <button 
                    type="button"
                    class="el-button el-button--default" 
                    @focus="btnFocus" 
                    @click="setVal('&lt;', true)">&lt;</button>
                <button 
                    type="button"
                    class="el-button el-button--default" 
                    @focus="btnFocus" 
                    @click="setVal('=', true)">=</button>
                <button 
                    type="button"
                    class="el-button el-button--default" 
                    @focus="btnFocus" 
                    @click="setVal('&gt;', true)">&gt;</button>
            </el-button-group>
        </el-popover>
        <label class="suffix">S</label>
    </span>
</template>

<script>
export default {
    props: {
        value: String,
        readonly: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            val: this.value,
            showPanel: false,
            focused: false,
        };
    },
    methods: {
        setVal(v, refocus = false) {
            this.val = v;
            if (refocus) {
                this.$refs.input.focus();
                this.focused = false;
            }
        },
        checkKey(e) {
            if (e.keyCode === 187) {
                this.setVal('=');
            } else if (e.keyCode === 190) {
                this.setVal('>');
            } else if (e.keyCode === 188) {
                this.setVal('<');
            } else if (['<', '=', '>'].indexOf(e.key) !== -1) {
                this.setVal(e.key);
            } else if (e.keyCode === 9) {
                // Keeps tabulation operational
                return;
            }
            e.preventDefault();
        },
        displayPanel() {
            this.showPanel = true;
        },
        onBlur() {
            this.$nextTick(() => {
                if (!this.focused) {
                    this.showPanel = false;
                }
            });
        },
        btnFocus() {
            this.focused = true;
        },
    },
    watch: {
        val(v) {
            this.$emit('input', v);
        },
        value(v) {
            this.val = v;
        },
    },
};
</script>