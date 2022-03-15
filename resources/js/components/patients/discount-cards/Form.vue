<template>
    <form>
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :options="card_types"
                    property="discount_card_type_id"
                    :disabled="disabled"
                    :label="__('Тип карты')" />
                <form-select
                    :entity="model"
                    :options="clinics"
                    property="clinic_id"
                    :filterable="true"
                    :label="__('Клиника')" />
                <form-checkbox
                    :entity="currentPatient"
                    property="disabled"
                    :label="__('Карта не действует')" />
            </el-col>
            <el-col :span="8">
                <form-date
                    :entity="model"
                    :disabled="blockDates"
                    property="issued"
                    :label="__('Дата выдачи')" />
                <form-date
                    :entity="model"
                    property="valid_from"
                    :disabled="blockDates"
                    :label="__('Дата начала действия')" />
                <form-date
                    :entity="model"
                    property="expires"
                    :disabled="blockDates"
                    :label="__('Дата окончания действия')" />
            </el-col>
            <el-col :span="8">
                <form-text
                    :entity="model"
                    property="comment"
                    :rows="8"
                    class="three-rows-height"
                    :label="__('Комментарий')" />
            </el-col>
        </el-row>
        <slot name="buttons" />
    </form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import DiscountCardTypeRepository from '@/repositories/discount-card-type';

export default {
    props: {
        model: Object,
        patient: Object,
        disabled: {
            type: Boolean,
            default: false,
        }
    },
    data() {
        return {
            clinics: [],
            card_types: [],
            currentPatient: {},
        };
    },
    computed: {
        blockDates() {
            if (this.patient.isNew() || this.model.isNew()) {
                return false;
            }
            let currentPatient = this.model.patients.find(item => item.patient_id == this.patient.id);
            if (currentPatient) {
                return !currentPatient.is_owner;
            }
            return false;
        },
    },
    mounted() {
        this.getCardTypes();
        this.model.addHolder(this.patient.id);
        this.setCurrentPatient();

        if(!this.model.isNew()) {
            this.getClinics(false);
        }
    },
    methods: {
        setCurrentPatient() {
            this.currentPatient = this.model.patients.find(patient => {
                return patient.patient_id === this.patient.id;
            });
        },
        getCardTypes() {
            let cardType = new DiscountCardTypeRepository();
            cardType.fetchList(this.getCardTypeFilters()).then((response) => {
                this.card_types = response;
            });
        },
        getClinics(unset = true) {
            if (unset) {
                this.model.clinic_id = null;
            }

            if (_.isFilled(this.model.discount_card_type_id)) {
                let repo = new ClinicRepository();
                return repo.fetchList({discount_card_type: this.model.discount_card_type_id})
                        .then((response) => {
                            this.clinics = response;
                        });
            } else {
                this.clinics = [];
            }
        },
        getCardTypeFilters() {
            return _.onlyFilled({
                dont_use_for_patient: 0,
            });
        },
        setClinicData(val) {
            if (_.isFilled(val)) {
                let clinic = this.clinics.find(clinic => clinic.id == this.model.clinic_id);
                this.model.set('clinic_name', clinic.value);
            } else {
                this.model.set('clinic_name', '');
            }
        },
        setTypeData() {
            let cardType = this.card_types.find(type => type.id == this.model.discount_card_type_id);
            this.model.type.name = cardType.value;
            this.model.type.discount_percent = cardType.discount_percent;
            this.updateExpire(cardType);
        },
        updateExpire(cardType) {
            let validFrom = this.$moment(this.model.valid_from);
            let expires = validFrom.add(cardType.expire_period, 'days');
            this.model.expires = expires.format('YYYY-MM-DD');
        },
    },
    watch: {
        ['model.discount_card_type_id']() {
            this.setTypeData();
            this.getClinics();
        },
        ['model.clinic_id'](val) {
            this.setClinicData(val);
        },
    },
}
</script>
