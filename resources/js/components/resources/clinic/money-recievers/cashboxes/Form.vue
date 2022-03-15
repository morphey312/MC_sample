<template>
    <model-form :model="model">
        <form-input
            :entity="model"
            property="name"
            :label="__('Название кассы')"
        />
        <form-input
            :entity="model"
            property="cashbox_key"
            :label="__('API ключ от кассы')"
        />
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: Object,
    },
}
</script>

<style scoped>

</style>
