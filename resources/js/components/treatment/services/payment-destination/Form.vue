<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input-i18n
                            :entity="model"
                            property="name"
                            :label="__('Назначение платежа')"
                        />
                        <form-select
                            :entity="model"
                            options="additional_service_mark"
                            property="additional_service_mark"
                            :label="__('Специальная отметка')"
                        />
                    </el-col>
                    <el-col :span="12">
                        <form-row name="color">
                            <label class="input-label">{{ __('Выберите цвет строк:') }}</label>
                            <el-color-picker v-model="model.color" />
                        </form-row>
                        <form-checkbox
                            :entity="model"
                            property="disabled"
                            :label="__('Не использовать')"
                            css-class="aligned-checkbox" />
                        <form-checkbox
                            :entity="model"
                            property="include_in_specialization_report"
                            :label="__('Включить в Оборот по специализации')"
                            css-class="aligned-checkbox"
                        />
                    </el-col>
                </el-row>
            </section>
            <section>
                <form-row name="clinics">
                    <transfer-table
                        v-if="model.loading === false"
                        :items="clinics"
                        v-model="model.clinics"
                        value-key="clinic_id"
                        :left-title="__('Клиника')"
                        left-width="180px"
                        :right-title="__('Клиника')"
                        right-width="180px">
                    </transfer-table>
                </form-row>
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        model: Object
    },
    data() {
        return {
            clinics: new ClinicRepository(),
        }
    }
}
</script>
