<template>
    <div class="buttons">
        <a 
            href="#"
            @click.prevent="addMedicines">
            <svg-icon name="plus-alt" class="icon-small icon-blue">
                {{ __('Добавить медикаменты к выдаче') }}
            </svg-icon>
        </a>
    </div>
</template>
<script>
export default {
    methods: {
        addMedicines() {
            this.$emit('addMedicine');
        },
    },
}
</script>