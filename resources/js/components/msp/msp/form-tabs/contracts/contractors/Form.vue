<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            :disabled="true"
                            property="name"
                            :label="__('Название')" />
                        <form-input
                            :entity="model"
                            :disabled="true"
                            property="edrpou"
                            :label="__('ЕГРПОУ')" />
                        <form-date
                            :entity="model"
                            property="issued_at"
                            :label="__('Дата заключения договора')" />
                    </el-col>
                    <el-col :span="12">
                        <form-select
                            :entity="model"
                            :disabled="true"
                            options="msp_type"
                            property="type"
                            :label="__('Тип учреждения')" />
                        <form-input
                            :entity="model"
                            property="contract_number"
                            :label="__('Номер договора')" />
                        <form-date
                            :entity="model"
                            property="expires_at"
                            :label="__('Дата истечения договора')" />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <form-row name="clinics">
                    <transfer-table
                        key="clinics"
                        :items="clinics"
                        :fields="clinicFields"
                        v-model="model.clinics"
                        :left-title="__('Клиника')"
                        left-width="170px"
                        :right-title="__('Клиника')"
                        right-width="170px">
                        <template slot="service" slot-scope="props">
                            <form-select
                                :entity="props.rowData.data"
                                options="subcontrator_service_type"
                                property="service"
                                control-size="mini"
                                css-class="table-row" />
                        </template>
                    </transfer-table>
                </form-row>
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: {
            type: Object
        },
        clinics: {
            type: Object,
        }
    },
    data() {
        return {
            clinicFields: [
                {
                    name: 'service',
                    title: __('Услуга'),
                    width: '120px',
                },
            ],
        }
    }
}
</script>
