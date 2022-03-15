<template>
    <model-form :model="model">
        <form-input
            :entity="model"
            property="name"
            :label="__('Название страны')" />
        <form-input
            :entity="model"
            property="code"
            :label="__('Код ISO')" />
        <slot name="buttons"></slot>
    </model-form>
</template>
<script>

export default {
    props: {
        model: Object
    },
}
</script>
