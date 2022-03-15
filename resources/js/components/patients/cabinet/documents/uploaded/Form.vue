<template>
    <model-form :model="model">
        <form-select
            :entity="model"
            options="patient_upload_type"
            property="type"
            :label="__('Тип документа')" />
        <form-text
            :entity="model"
            property="description"
            :label="__('Описание документа')" />        
        <form-upload
            :entity="model"
            :multiple="false"
            accept="image/jpeg,image/png,application/pdf"
            property="file_id"
            :label="__('Выберите файл')" />
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
