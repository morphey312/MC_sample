<template>
    <div class="buttons">
        <span>
            <el-switch
                v-model="switcherState"
                active-color="#B7B308"
                inactive-color="#BDBDBD"
                @change="toggle">
            </el-switch>
            <span class="ml-10">{{ switcherText }}</span>
        </span>
    </div>
</template>
<script>
export default {
    props: {
        switcherText: String,
        initialState: {
            type: Boolean, 
            default: false,
        },
    },
    data() {
        return {
            switcherState: this.initialState,
        }
    },
    methods: {
        toggle() {
            this.$emit('toggleCost', this.switcherState);
        }
    }
}
</script>