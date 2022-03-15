<template>
    <div>
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="trusted_person_last_name"
                        :label="__('Фамилия')" />
                    <div class="form-input-group">
                        <form-input
                            :entity="model"
                            property="trusted_person_phone_number"
                            :label="__('Номер телефона')"
                        />
                        <form-select
                            :entity="model"
                            options="ehealth_phone_type"
                            property="trusted_person_phone_type"
                            :label="__('Тип номера')"
                        />
                    </div>
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="trusted_person_first_name"
                        :label="__('Имя')" />
                    <form-checkbox
                        :entity="model"
                        property="incompetent"
                        :label="__('Пациент недееспособный')" />
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="trusted_person_second_name"
                        :label="__('Отчество')" />
                </el-col>
            </el-row>
        </section>
    </div>
</template>

<script>
import PhoneType from '@/components/ehealth/patient/mixins/phone-type-check';

export default {
    mixins: [
        PhoneType
    ],
    props: {
        model: {
            type: Object
        },
    },
    watch: {
        ['model.trusted_person_phone_number'](val) {
            this.model.trusted_person_phone_type = this.isMobileNumber(val) ? 'mobile' : 'land_line';
        },
    },
};
</script>
