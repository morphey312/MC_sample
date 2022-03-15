<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="name"
                    :label="__('Полное название')" />
                <form-input
                    :entity="model"
                    property="post_address"
                    :label="__('Юридический адрес')" />
            </el-col>
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="short_name"
                    :label="__('Краткое название')" />
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