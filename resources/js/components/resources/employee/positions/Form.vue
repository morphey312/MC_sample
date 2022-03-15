<template>
    <model-form :model="model">
        <form-input-i18n
            :entity="model"
            property="name"
            :label="__('Название должности')" />
        <form-select
            :entity="model"
            property="ehealth_position"
            :options="ehealth_positions"
            :label="__('Аналог в eHealth')" />
        <form-select
            :entity="model"
            property="ehealth_type"
            options="ehealth_employee_type"
            :label="__('Тип сотрудника в eHealth')" />
        <form-checkbox
            :entity="model"
            property="has_specialization"
            :label="__('Есть специализации')"
        />
        <form-checkbox
            :entity="model"
            property="has_voip"
            :label="__('Есть доступ к телефонии')"
        />
        <form-checkbox
            :entity="model"
            property="is_doctor"
            :label="__('Является врачом')"
        />
        <form-checkbox
            :entity="model"
            property="is_operator"
            :label="__('Является оператором')"
        />
        <form-checkbox
            :entity="model"
            property="is_cashier"
            :label="__('Является кассиром')"
        />
        <form-checkbox
            :entity="model"
            property="is_marketing"
            :label="__('Является сотрудником отдела маркетинга')"
        />
        <form-checkbox
            :entity="model"
            property="is_reception"
            :label="__('Является сотрудником регистратуры')"
        />
        <form-checkbox
            :entity="model"
            property="is_collector"
            :label="__('Является коллектором')"
        />
        <form-checkbox
            :entity="model"
            property="is_superviser"
            :label="__('Является супервайзером')"
        />
        <form-checkbox
            :entity="model"
            property="is_surgery"
            :label="__('Учавствует в операции')"
        />
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import EhealthPositionRepository from '@/repositories/ehealth/position';

export default {
    props: {
        model: Object
    },
    data() {
        return {
            ehealth_positions: [],
        };
    },
    mounted() {
        let repository = new EhealthPositionRepository();
        repository.fetchList().then((list) => {
            this.ehealth_positions = list.map(item => ({id: item.code, value: item.value}));
        });
    },
}
</script>