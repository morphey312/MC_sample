<template>
    <model-form :model="model">
        <form-text
            :entity="model"
            property="cancel_summary"
            :label="__('Пояснительный билет')" />
        <form-select
            :entity="model"
            property="status_reason"
            :label="__('Причина удаления эпизода')"
            options="ehealth_cancellation_reasons" />
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
