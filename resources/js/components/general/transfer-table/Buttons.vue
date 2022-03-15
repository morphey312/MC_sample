<template>
    <div class="buttons">
        <a 
            :class="['put', {active: enablePut}]" 
            href="#" 
            @click.prevent="put">
            <svg-icon name="arrow-right-alt" />
        </a>
        <a 
            :class="['pull', {active: enablePull}]"
            href="#" 
            @click.prevent="pull">
            <svg-icon name="arrow-left-alt" />
        </a>
    </div>
</template>

<script>
export default {
    props: {
        enablePut: Boolean,
        enablePull: Boolean,
    },
    methods: {
        put() {
            if (this.enablePut) {
                this.$emit('put');
            }
        },
        pull() {
            if (this.enablePull) {
                this.$emit('pull');
            }
        },
    }
}
</script>