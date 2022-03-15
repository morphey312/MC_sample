<template>
    <el-row :gutter="20">
        <el-col :span="12">
            <form-checkbox
                :entity="model"
                property="propose_to_disable_on_copy"
                :label="__('При передаче предлагать проставить «Не использовать» владельцу карты')" /> 
        </el-col>
        <el-col :span="12">
            <form-input
                :entity="model"
                property="max_owners"
                type="number"
                :min="0"
                :step="1"
                :label="__('Максимальное количество пациентов пользующихся картой')" />
        </el-col>
    </el-row>
</template>

<script>
export default {
    props: {
        model: Object,
    },
}   
</script>