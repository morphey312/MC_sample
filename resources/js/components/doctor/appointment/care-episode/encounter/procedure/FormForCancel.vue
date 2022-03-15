<template>
    <model-form :model="model">
        <form-text
            :entity="model"
            property="explanatory_letter"
            :label="__('Обоснование ложного внесения медицинской документации')" />
        <form-select
            :entity="model"
            property="status_reason"
            :label="__('Причина удаления процедуры')"
            options="ehealth_procedure_status_reasons" />
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
