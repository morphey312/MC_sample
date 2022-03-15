<template>
    <el-row :gutter="20">
        <el-col :span="8">
            <form-switch
                :entity="model"
                options="patient_status"
                property="status"
                :label="__('Статус клиента')"
            />
            <form-input
                :entity="model"
                property="lastname"
                :label="__('Фамилия')"
            />
            <form-input
                :entity="model"
                property="firstname"
                :label="__('Имя')"
            />
            <form-input
                :entity="model"
                property="middlename"
                :label="__('Отчество')"
            />
            <form-switch
                :entity="model"
                options="gender"
                property="gender"
                :label="__('Пол')"
            />
            <form-date
                :entity="model"
                property="birthday"
                :label="__('Дата рождения')"
            />
        </el-col>
        <el-col :span="8" v-if="$can('patient-cabinet.info')">
            <form-select
                v-if="isPatient"
                :entity="model"
                ref="clinic"
                :multiple="true"
                :options="clinics"
                :filterable="true"
                property="clinics"
                :label="__('Клиники пациента')"
            />
            <div class="form-input-group">
                <form-input
                    :entity="model"
                    property="contact_details.primary_phone_number"
                    :label="__('Номер телефона')"
                />
                <form-select
                    :entity="model"
                    :options="clinics"
                    :required="isFilled(model.contact_details.primary_phone_number)"
                    property="contact_details.primary_phone_clinic"
                    :filterable="true"
                    :label="__('Клиника')"
                />
            </div>
            <form-input
                :entity="model"
                property="contact_details.primary_phone_comment"
                :label="__('Примечание к номеру телефона')"
            />
            <div class="form-input-group">
                <form-input
                    :entity="model"
                    property="contact_details.secondary_phone_number"
                    :label="__('Доп. номер')"
                />
                <form-select
                    :entity="model"
                    :options="clinics"
                    :required="isFilled(model.contact_details.secondary_phone_number)"
                    property="contact_details.secondary_phone_clinic"
                    :filterable="true"
                    :label="__('Клиника')"
                />
            </div>
            <form-input
                :entity="model"
                property="contact_details.secondary_phone_comment"
                :label="__('Примечание к доп. номеру телефону')"
            />
            <form-select
                v-if="isPatient"
                :entity="model"
                :repository="sources"
                :min-query-len="0"
                property="source_id"
                :class="{'hightlight-action': $isMobileNavigator}"
                :label="__('Источник информации')"
            />
        </el-col>
        <el-col :span="8">
            <template v-if="$can('patient-cabinet.info')">
                <div class="form-input-group">
                    <form-input-address-elastic
                        :entity="model"
                        property="location"
                        :contexts="{ 'place': ['city', 'village', 'town'] }"
                        :class="{'hightlight-action': $isMobileNavigator}"
                        :label="__('Город')"
                    />
                    <form-input-address-elastic
                        :entity="model"
                        property="street"
                        :contexts="{ 'highway': ['residential', 'service', 'primary', 'secondary', 'tertiary'] }"
                        :class="{'hightlight-action': $isMobileNavigator}"
                        :label="__('Улица')"
                    />
                </div>
                <div class="form-input-group">
                    <form-input
                        :entity="model"
                        property="house_number"
                        :label="__('№ дома')"
                    />
                    <form-input
                        :entity="model"
                        property="apartment_number"
                        :label="__('№ квартиры')"
                    />
                </div>
                <form-input
                    :entity="model"
                    property="contact_details.email"
                    label="Email"
                />
            </template>
            <form-select
                v-if="isPatient"
                :entity="model"
                options="med_insurance_availability"
                property="med_insurance"
                :label="__('Наличие мед. страховки')"
            />
            <form-text
                :entity="model"
                property="comment"
                :label="__('Примечание')"
            />
        </el-col>
    </el-row>
</template>
<script>
import ClinicRepository from '@/repositories/clinic';
import InformationSourceRepository from '@/repositories/patient/information-source';
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        model: {
            type: Object
        },
        isPatient: {
            type: Boolean,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('patients')
            }),
            sources: new ProxyRepository(({filters}) => {
                let combined = this.sources.getFilters(filters);
                let repository = new InformationSourceRepository();
                if ((combined.filters.query === undefined || combined.filters.query.length === 0) &&
                    combined.filters.clinic.length === 0) {
                    return Promise.resolve([]);
                }
                return repository.fetchList(combined.filters, null, 30);
            }, {
                filters: this.getSourceFilters(),
            }),
        };
    },
    methods: {
        isFilled(val) {
            return _.isFilled(val);
        },
        attachPhoneClinics(clinics) {
            if (_.isArray(clinics)) {
                var details = this.model.contact_details;
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
        addClinic(clinic) {
            if (this.model.clinics.indexOf(clinic) === -1) {
                this.model.clinics.push(clinic);
            }
        },
        getSourceFilters() {
            return {
                clinic: this.model.clinics,
            };
        },
    },
    watch: {
        ['model.clinics'](val) {
            if (val && val.length === 1 && _.isVoid(this.model.location)) {
                let clinics = this.$refs.clinic.getAvailableOptions();
                let clinic = _.findById(clinics, val[0]);
                if (clinic !== undefined && clinic.need_apply_city) {
                    this.model.location = this.$handbook.getOption('city', clinic.city);
                }
            }
            if (this.isPatient) {
                this.attachPhoneClinics(val);
                this.sources.setFilters(this.getSourceFilters());
            }
        },
        ['model.contact_details.primary_phone_clinic'](val) {
            if (this.isPatient) {
                this.addClinic(val);
            }
        },
        ['model.contact_details.secondary_phone_clinic'](val) {
            if (this.isPatient) {
                this.addClinic(val);
            }
        },
    },
}
</script>
