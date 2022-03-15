<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    options="person_document"
                    property="type"
                    :label="__('Тип')" />
                <form-input
                    :entity="model"
                    property="number"
                    :label="__('Номер')" />
                <form-date
                    :entity="model"
                    property="issued_at"
                    :options="pickerOptionsIssued"
                    :label="__('Дата выдачи')" />
                <form-date
                    :entity="model"
                    property="expiration_date"
                    :options="pickerOptionsExpiration"
                    :label="__('Срок действия')" />
            </el-col>
            <el-col :span="12">
                <form-text
                    :entity="model"
                    property="issued_by"
                    :rows="2"
                    :label="__('Кем выдан')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: {
            type: Object
        },
    },
    data() {
        return {
            pickerOptionsIssued: {
                disabledDate: this.checkDisabledDateForIssued,
            },
            pickerOptionsExpiration: {
                disabledDate: this.checkDisabledDateForExpiration,
            }
        }
    },
    methods: {
        checkDisabledDateForIssued (date) {
            return this.$moment(date).isAfter(this.$moment(), "day");
        },
        checkDisabledDateForExpiration (date) {
            return this.$moment(date).isSameOrBefore(this.$moment(), "day");
        },
    }
}
</script>
