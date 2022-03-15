<template>
    <model-form :model="model">
        <form-date
            :entity="model"
            property="date"
            :label="__('Дата')" />
        <form-input
            :entity="model"
            property="place"
            :label="__('Местоположение')" />
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: {
            type: Object
        },
    },
}
</script>
