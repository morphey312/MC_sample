<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="name"
                    :label="__('Полное название')" />
                <form-input
                    :entity="model"
                    property="post_address"
                    :label="__('Почтовый адрес')" />
                <form-input
                    :entity="model"
                    property="tax_number"
                    :label="__('ИНН')" />
                <form-row
                    name="for_show_price"
                    label="&nbsp;">
                    <form-checkbox
                        :entity="model"
                        property="show_price"
                        :label="__('Отображать в прайсах')" />
                </form-row>
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="short_name"
                    :label="__('Краткое название')" />
                <form-input
                    :entity="model"
                    property="bank_account"
                    :label="__('Расчетный счет')" />
                <form-input
                    :entity="model"
                    property="signer"
                    :label="__('Подписант')" />
                <form-input
                    :entity="model"
                    property="signer_position"
                    :label="__('Должность подписанта')">
                </form-input>
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="location"
                    :label="__('Юридический адрес')" />
                <form-input
                    :entity="model"
                    property="egrpo"
                    :label="__('ЕГРПОУ(ОКПО)')" />
                <form-input
                    :entity="model"
                    property="phone_number"
                    :label="__('Контактный телефон')" />
            </el-col>
        </el-row>
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
