<template>
    <model-form :model="model">
        <form-input
            :entity="model"
            property="name"
            :label="__('Название группы')" />
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
};
</script>