<template>
    <div class="buttons">
        <a 
            href="#"
            @click.prevent="editCardType">
            {{ __('Редактировать тип карты') }}
        </a>
    </div>
</template>

<script>
export default {
    methods: {
        editCardType() {
            this.$emit('click');
        }
    }
}   
</script>