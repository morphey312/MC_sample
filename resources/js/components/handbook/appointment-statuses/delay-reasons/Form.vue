<template>
    <model-form :model="model">
        <form-input-i18n
            :entity="model"
            property="name"
            :label="__('Название причины задержки')" />
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: Object
    }
}
</script>
