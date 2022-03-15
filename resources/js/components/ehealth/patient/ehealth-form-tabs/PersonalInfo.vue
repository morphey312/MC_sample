<template>
    <div>
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-switch
                        :entity="model"
                        options="patient_type"
                        property="patient_type"
                        :label="__('Тип пациента')"
                    />
                    <form-input
                        :entity="model"
                        property="last_name"
                        :label="__('Фамилия')"
                    />
                    <form-input
                        :entity="model"
                        property="first_name"
                        :label="__('Имя')"
                    />
                    <form-input
                        :entity="model"
                        property="second_name"
                        :label="__('Отчество')"
                    />
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="birth_country"
                        :label="__('Страна рождения')"
                    />
                    <form-input
                        :entity="model"
                        property="birth_settlement"
                        :label="__('Город рождения')"
                    />
                    <form-date
                        :entity="model"
                        property="birth_date"
                        :label="__('Дата рождения')"
                    />
                    <form-switch
                        :entity="model"
                        options="gender"
                        property="gender"
                        :label="__('Пол')"
                    />
                </el-col>
                <el-col :span="8">
                    <div class="form-input-group">
                        <form-input
                            :entity="model"
                            property="phone_number"
                            :label="__('Номер телефона')"
                        />
                        <form-select
                            :entity="model"
                            options="ehealth_phone_type"
                            property="phone_type"
                            :label="__('Тип номера')"
                        />
                    </div>
                    <form-input
                        :entity="model"
                        property="email"
                        label="Email"
                    />
                    <form-select
                        :entity="model"
                        options="preferred_way_communication"
                        property="preferred_way_communication"
                        :label="__('Предпочтительный тип связи')"
                    />
                    <form-input
                        :entity="model"
                        property="secret"
                        :label="__('Слово пароль')"
                    />
                </el-col>
            </el-row>
        </section>
        <hr />
        <authentications
            v-if="model.birth_date"
            :patient="model" />
    </div>
</template>
<script>
import PhoneType from '@/components/ehealth/patient/mixins/phone-type-check';
import Authentications from './authentications/List.vue';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        PhoneType
    ],
    components: {
        Authentications
    },
    props: {
        model: {
            type: Object
        },
    },
    watch: {
        ['model.phone_number'](val) {
            this.model.phone_type = this.isMobileNumber(val) ? 'mobile' : 'land_line';
        },
    },
    data() {
        return {
            CONSTANTS: CONSTANTS
        }
    }
}
</script>
