<template>
    <div class="buttons">
        <a 
            href="#"
            @click.prevent="clicked">
            <svg-icon name="plus-alt" class="icon-small icon-blue">
                {{ buttonText }}
            </svg-icon>
        </a>
    </div>
</template>
<script>
export default {
    props: {
        buttonText: {
            type: String,
            default: '',
        },
    },
    methods: {
        clicked() {
            this.$emit('clicked');
        },
    },
}
</script>