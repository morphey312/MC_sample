<template>
    <model-form :model="model">
        <form-input-i18n
            :entity="model"
            property="name"
            :label="__('Название')" />
        <form-select
            :entity="model"
            property="media_type"
            options="media_type"
            :label="__('Вид рекламы')" />
        <form-select
            :entity="model"
            property="is_active"
            options="active_status"
            :label="__('Статус')" />
        <form-select
            :entity="model"
            :multiple="true"
            property="clinics"
            :options="clinics"
            :filterable="true"
            :label="__('Клиники')" />
        <form-select
            :entity="model"
            :repository="employees"
            :clearable="true"
            property="employee_id"
            :label="__('Сотрудник для перенаправлений')" />
        <form-checkbox
            :entity="model"
            property="is_collective_form"
            :label="__('Собирательный образ для вида рекламы')" />
        <form-checkbox
            :entity="model"
            property="show_in_appointment"
            :label="__('Показывать имя источника в записи')" />
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';

export default {
    props: {
        model: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
            }),
            employees: new EmployeeRepository({
                filters: {
                    clinic: this.model.clinics,
                }
            }),
        };
    },
}
</script>
