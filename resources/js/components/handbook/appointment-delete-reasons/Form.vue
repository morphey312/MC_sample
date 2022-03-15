<template>
    <model-form :model="model">
        <form-input-i18n
            :entity="model"
            property="name"
            :label="__('Название причины удаления записи пациента')" />
        <form-checkbox
            :entity="model"
            property="include_to_report"
            :label="__('Причина включается в отчет')" />
        <form-checkbox
            :entity="model"
            property="not_use_for_appointment_delete"
            :label="__('Не использовать причину удаления записи пациента')" />
        <form-checkbox
            :entity="model"
            property="comment_required"
            :label="__('Необходим комментарий по поводу удаления записи пациента')" />
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