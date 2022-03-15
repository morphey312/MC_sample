<template>
    <div class="buttons">
        <a 
            href="#"
            @click.prevent="patientLog">
            <svg-icon name="add" class="icon-small icon-blue">
                {{ __('Настройка участников') }}
            </svg-icon>
        </a>
    </div>
</template>
<script>
export default {
    methods: {
        patientLog() {
            this.$emit('showSettings');
        },
    },
}
</script>