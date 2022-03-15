<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="last_name"
                    :label="__('Фамилия')" />
                <form-input
                    :entity="model"
                    property="first_name"
                    :label="__('Имя')" />
                <form-input
                    :entity="model"
                    property="middle_name"
                    :label="__('Отчество')" />
                <form-switch
                    :entity="model"
                    options="gender"
                    property="gender"
                    :label="__('Пол')" />
                <form-date
                    :entity="model"
                    property="birth_date"
                    :label="__('Дата рождения')" />
                <form-input
                    :entity="model"
                    property="phone"
                    :label="__('Номер телефона')" />
                <form-input
                    :entity="model"
                    property="additional_phone"
                    :label="__('Дополнительный номер телефона')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="email"
                    :label="__('E-mail')" />
                <form-switch
                    :entity="model"
                    :options="document_types"
                    property="no_tax_id"
                    :label="__('Документы')" />
                <form-input
                    :entity="model"
                    property="tax_id"
                    :label="model.no_tax_id ? __('Номер паспорта') : __('РНУКПН')" />
                <form-input
                    :entity="model"
                    property="experience"
                    :label="__('Опыт работы')" />
                <form-text
                    :entity="model"
                    property="about"
                    :rows="3"
                    :label="__('Допонительно')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="user.login"
                    :label="__('Логин')" />
                <form-input
                    :entity="model"
                    property="user.password"
                    type="password"
                    :label="__('Пароль')" />
                <form-select
                    :entity="model"
                    :options="roles"
                    :multiple="true"
                    property="user.roles"
                    :label="__('Группы доступа')" />
                <form-select
                    :entity="model"
                    :options="permissions"
                    :multiple="true"
                    :filterable="true"
                    groupBy="group"
                    property="user.permissions"
                    :label="__('Дополнительные полномочия')" />
                <form-select
                    :entity="model"
                    options="employee_system_status"
                    property="system_status"
                    :label="__('Техническое назначение')" />
                <form-select
                    :entity="model"
                    :multiple="false"
                    options="employee_preferred_language"
                    property="preferred_language"
                    :label="__('Язык отображения программы')" />
                <form-checkbox
                    :entity="model"
                    property="is_translator"
                    :label="__('Является переводчиком')" />
                <form-checkbox
                    v-if="hasClinics && $can('employees.copy-to-portal')"
                    :entity="model"
                    :disabled="alreadyCopied"
                    property="copy_to_portal"
                    :label="__('Зарегистрировать на обучающем портале')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import RoleRepository from '@/repositories/role';
import PermissionRepository from '@/repositories/permission';

export default {
    props: {
        model: {
            type: Object
        },
        hasClinics: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            alreadyCopied: this.model.copy_to_portal,
            roles: new RoleRepository({sort: [{field: 'name', direction: 'asc'}]}),
            permissions: new PermissionRepository(),
            document_types: [
                {id: false, value: __('РНУКПН')},
                {id: true, value: __('Паспорт')},
            ],
        };
    },
}
</script>
