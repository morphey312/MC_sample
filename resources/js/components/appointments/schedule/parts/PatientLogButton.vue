<template>
    <div class="buttons">
        <a 
            href="#"
            @click.prevent="patientLog">
            <svg-icon name="report" class="icon-small icon-blue">
                {{ __('История изменений пациента') }}
            </svg-icon>
        </a>
    </div>
</template>
<script>
export default {
    methods: {
        patientLog() {
            this.$emit('patientLog');
        },
    },
}
</script>