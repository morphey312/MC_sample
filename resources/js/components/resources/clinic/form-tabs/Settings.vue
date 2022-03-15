<template>
    <div class="sections-wrapper">
        <section>
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-input
                        :disabled="true"
                        :entity="model"
                        :label="__('ID Клиники')"
                        property="id"/>
                    <form-checkbox
                        :entity="model"
                        property="works_with_apteka24"
                        :label="__('Работа с apteka24')" />
                </el-col>
                <el-col :span="18">
                    <form-checkbox
                        :entity="model"
                        property="need_apply_city"
                        :label="__('Автоматически подтягивать город при создании пациента')" />
                    <form-checkbox
                        :entity="model"
                        property="export_patients_contacts"
                        :label="__('Отправлять контакты пациентов в eSputnik')" />
                    <form-checkbox
                        :entity="model"
                        property="is_default"
                        :label="__('Использовать эту клинику по умолчанию для новых пациентов')" />
                    <form-checkbox
                        :entity="model"
                        property="secure_analysis"
                        :label="__('Шифровать результаты анализов')" />
                </el-col>
            </el-row>
        </section>
    </div>
</template>

<script>
export default {
    props: {
        model: Object,
    },
}
</script>
