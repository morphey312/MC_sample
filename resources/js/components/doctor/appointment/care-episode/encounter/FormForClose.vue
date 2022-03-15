<template>
    <model-form :model="model">
        <form-text
            :entity="model"
            property="explanatory_letter"
            :label="__('Пояснительный билет')" />
        <form-select
            :entity="model"
            property="cancellation_reason"
            :label="__('Причина удаления взаимодействия')"
            options="ehealth_cancellation_reasons" />
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: Object,
        outpatientRecord: Object,
    },
}
</script>
