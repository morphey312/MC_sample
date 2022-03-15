<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-input-i18n
                    :entity="model"
                    property="name"
                    :label="__('Название специализации')" />
                <form-select
                    :entity="model"
                    options="active_status"
                    property="status"
                    :label="__('Статус')" />
                <form-input
                    :entity="model"
                    property="order"
                    type="number"
                    :step="1"
                    :min="0"
                    :label="__('Позиция в листах записи')" />
                <form-checkbox
                    :entity="model"
                    property="is_non_profile_patient"
                    :label="__('Данная специализация является признаком непрофильности пациентов')" />
                <form-checkbox
                    :entity="model"
                    property="is_non_treatment"
                    :label="__('По данной специализации не проводится лечение')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="genitive_name"
                    :label="__('Название в родительном падеже')" />
                <form-input
                    :entity="model"
                    property="course_days"
                    type="number"
                    :step="1"
                    :min="0"
                    :label="__('Количество дней на курс лечения')" />
                <form-checkbox
                    :entity="model"
                    property="not_use_for_new_patient_call"
                    :label="__('Не использовать специализацию для новых звонков пациентов')" />
                <form-checkbox
                    :entity="model"
                    property="not_show_signal_records"
                    :label="__('Не показывать сигнальные обозначения автоматически')" />
                <form-checkbox
                    :entity="model"
                    property="is_check_up"
                    :label="__('Является CHECK UP-ом')" />
                <form-checkbox
                    :entity="model"
                    property="once_in_report"
                    :label="__('Единоразово в отчете')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="short_name"
                    :label="__('Сокращенное название специализации')" />
                <form-select
                    :entity="model"
                    :options="cardTemplates"
                    property="card_template_id"
                    :label="__('Шаблон амбулаторной карты')" />
                <form-select
                    :entity="model"
                    options="specialization_service_group"
                    property="service_group"
                    :label="__('Услуги относятся к типу')" />
                <form-checkbox
                    :entity="model"
                    property="online_appointment"
                    :disabled="model.is_real_time_appointment"
                    :label="__('Возможность записи в листы из ЛК')" />
                <form-checkbox
                    :entity="model"
                    property="is_real_time_appointment"
                    :disabled="model.online_appointment"
                    :label="__('Показывать реальное свободное время в ЛК')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import RecordTemplateRepository from '@/repositories/patient/card/record-template';

export default {
    props: {
        model: {
            type: Object,
        },
    },
    data() {
        return {
            cardTemplates: new RecordTemplateRepository({
                sort: [
                    {field: 'name', direction: 'asc'},
                ]
            }),
        };
    },
};
</script>
