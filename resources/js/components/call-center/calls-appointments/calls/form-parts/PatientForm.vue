<template>
    <el-row :gutter="20">
        <el-col :span="6">
            <form-input
                :entity="model"
                property="contact.lastname"
                :label="__('Фамилия')"
                :disabled="isContactExist"
            />
            <form-input
                :entity="model"
                property="contact.firstname"
                :label="__('Имя')"
                :disabled="isContactExist"
            />
            <form-input
                :entity="model"
                property="contact.middlename"
                :label="__('Отчество')"
                :disabled="isContactExist"
            />
        </el-col>
        <el-col :span="6">
            <form-date
                :entity="model"
                property="contact.birthday"
                :label="__('Дата рождения')"
                :disabled="isContactExist"
            />
            <form-switch
                :entity="model"
                :options="genders"
                property="contact.gender"
                :label="__('Пол')"
                :disabled="isContactExist"
            />
            <form-input-address-elastic
                :entity="model"
                property="contact.location"
                :contexts="{ 'place': ['city', 'village', 'town'] }"
                :label="__('Город')"
                :disabled="isContactExist"
            />
        </el-col>
        <el-col :span="6">
            <div class="form-input-group">
                <form-input
                    :entity="model"
                    property="contact.contact_details.primary_phone_number"
                    :label="__('Телефон')"
                    :disabled="isContactExist"
                />
                <form-select
                    :entity="model"
                    ref="clinic"
                    :options="clinics"
                    :required="isFilled(model.contact.contact_details.primary_phone_number)"
                    property="contact.contact_details.primary_phone_clinic"
                    :filterable="true"
                    :label="__('Клиника')"
                    :disabled="isFilled(model.contact.contact_details.primary_phone_clinic)"
                />
            </div>
            <div class="form-input-group">
                <form-input
                    :entity="model"
                    property="contact.contact_details.secondary_phone_number"
                    :label="__('Телефон (доп.)')"
                    :disabled="isContactExist"
                />
                <form-select
                    :entity="model"
                    :options="clinics"
                    :required="isFilled(model.contact.contact_details.secondary_phone_number)"
                    property="contact.contact_details.secondary_phone_clinic"
                    :filterable="true"
                    :label="__('Клиника')"
                    :disabled="isFilled(model.contact.contact_details.secondary_phone_clinic)"
                />
            </div>
            <form-input
                :entity="model"
                property="contact.contact_details.email"
                label="Email"
                :disabled="isContactExist"
            />
        </el-col>
        <el-col :span="6">
            <template v-if="isPatient">
                <form-select
                    :entity="model"
                    :repository="sources"
                    :min-query-len="0"
                    property="contact.source_id"
                    :label="__('Источник информации')"
                    :disabled="isContactExist && isFilled(model.contact.source_id)"
                />
                <form-select
                    :entity="model"
                    :multiple="true"
                    :options="clinics"
                    :filterable="true"
                    property="contact.clinics"
                    :label="__('Клиники')"
                    :disabled="isContactExist && isFilled(model.contact.clinics)"
                />
            </template>
            <form-input
                :entity="model"
                property="contact.comment"
                :label="__('Комментарий')"
                :disabled="isContactExist"
            />
        </el-col>
    </el-row>
</template>

<script>
import CONSTANTS from '@/constants';

export default {
    props: {
        model: {
            type: Object,
            required: true
        },
        sources: {
            type: Object,
            required: true
        },
        clinics: {
            type: Object,
            required: true
        },
        genders: {
            type: String,
            required: true
        }
    },
    computed: {
        isPatient() {
            return this.model.contact.status !== CONSTANTS.PATIENT.STATUS.GUEST;
        },
        isContactExist() {
            return this.model.contact.id !== null;
        },
    },
    methods: {
        attachPhoneClinics(clinics) {
            if (_.isArray(clinics)) {
                var details = this.model.contact.contact_details;
                if (clinics.length > 0) {
                    if (_.isVoid(details.primary_phone_clinic)) {
                        details.primary_phone_clinic = clinics[0];
                    }
                }
                if (clinics.length > 1) {
                    if (_.isVoid(details.secondary_phone_clinic)) {
                        details.secondary_phone_clinic = clinics[1];
                    }
                }
            }
        },
        setClinic(clinics) {
            if (_.isArray(clinics) && clinics.length === 1 && _.isVoid(this.model.clinic_id)) {
                this.model.clinic_id = clinics[0];
            }
        },
        addClinic(clinic) {
            if (this.model.contact.clinics.indexOf(clinic) === -1) {
                this.model.contact.clinics.push(clinic);
            }
        },
        isFilled(val) {
            return _.isFilled(val);
        },
    },
    watch: {
        ['model.contact.clinics'](val) {
            if (this.isPatient) {
                this.attachPhoneClinics(val);
                this.setClinic(val);
                if (val && val.length === 1 && _.isVoid(this.model.contact.location)) {
                    let clinics = this.$refs.clinic.getAvailableOptions();
                    let clinic = _.findById(clinics, val[0]);
                    if (clinic !== undefined && clinic.need_apply_city) {
                        this.model.contact.location = this.$handbook.getOption('city', clinic.city);
                    }
                }
            }
        },
        ['model.contact.contact_details.primary_phone_clinic'](val) {
            if (this.isPatient && val) {
                this.addClinic(val);
            }
        },
        ['model.contact.contact_details.secondary_phone_clinic'](val) {
            if (this.isPatient && val) {
                this.addClinic(val);
            }
        },
    },
}
</script>
