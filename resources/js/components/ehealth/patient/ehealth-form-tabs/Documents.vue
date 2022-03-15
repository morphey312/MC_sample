<template>
    <div>
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-checkbox
                        :entity="model"
                        property="no_tax_id"
                        :label="__('Отказ от ИНН')" />
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        :disabled="model.no_tax_id"
                        property="tax_id"
                        :label="__('ИНН')" />
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="unzr"
                        :label="__('Уникальный номер записи в ЭДДР')" />
                </el-col>
            </el-row>
        </section>
        <hr />
        <documents
            :patient="model" />
    </div>
</template>

<script>
import Documents from './documents/List.vue';

export default {
    components: {
        Documents
    },
    props: {
        model: {
            type: Object
        },
    },
};
</script>
