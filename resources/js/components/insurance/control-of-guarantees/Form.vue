<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="24">
                <form-text
                    v-if="editField === 'task'"
                    :entity="model"
                    property="task"
                    :label="__('Задание')" />
                <form-text
                    v-else
                    :entity="model"
                    property="note"
                    :label="__('Примечание')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: Object,
        editField: String
    },
}
</script>
