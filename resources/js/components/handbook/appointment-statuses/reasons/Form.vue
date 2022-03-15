<template>
    <model-form :model="model">
        <form-input-i18n
            :entity="model"
            property="name"
            :label="__('Название причины')"
        />
        <form-checkbox
            :entity="model"
            property="esputnik_no_answer"
            :label="__('Отслеживать в eSputnik (Нет ответа)')"
        />
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
