<template>
    <div class="buttons">
        <span>
            <el-switch
                v-model="switcherState"
                active-color="#B7B308"
                inactive-color="#BDBDBD"
                @change="toggle">
            </el-switch>
            <span class="ml-10 mr-10">{{ __('Платные медикаменты') }}</span>
        </span>
        <a 
            href="#"
            @click.prevent="clicked">
            <svg-icon name="plus-alt" class="icon-small icon-blue">
                {{ __('Добавить медикаменты из аптек') }}
            </svg-icon>
        </a>
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
        },
        clicked() {
            this.$emit('clicked');
        }
    }
}
</script>