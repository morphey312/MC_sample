<template>
    <div class="buttons">
        <a 
            href="#"
            @click.prevent="editLimitation">
            {{ __('Редактировать ограничение') }}
        </a>
    </div>
</template>

<script>
export default {
    methods: {
        editLimitation() {
            this.$emit('click');
        }
    }
}   
</script>