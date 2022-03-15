<template>
    <form-input
        v-if="!patient"
        :entity="entity"
        :clearable="true"
        :property="patientNameProp"
        :label="label">
        <template slot="label-addon">
            <a 
                href="#"
                @click.prevent="searchPatient">{{ __('Выбрать') }}</a>
        </template>
    </form-input>
    <form-row
        v-else
        :name="patientIdProp"
        :label="label">
        <template slot="label-addon">
            <a href="#"
                @click.prevent="clearPatient">{{ __('Очистить') }}</a> 
            /
            <a 
                href="#"
                @click.prevent="searchPatient">{{ __('Выбрать другого') }}</a>
        </template>
        <el-input
            :disabled="true"
            :value="patient.full_name"
        />
    </form-row>
</template>

<script>
import SearchPatient from '@/components/patients/search/Search.vue';
import Patient from '@/models/patient';

export default {
    props: {
        entity: {
            type: Object,
            required: true,
        },
        patientNameProp: {
            type: String,
            required: true,
        },
        patientIdProp: {
            type: String,
            required: true,
        },
        label: {
            type: String,
            default: __('Пациент'),
        },
    },
    data() {
        return {
            patient: null,
        };
    },
    mounted() {
        this.$watch('$props.entity.' + this.patientNameProp, (val) => {
            if (_.isFilled(val) && _.isFilled(this.entity[this.patientIdProp])) {
                this.entity[this.patientIdProp] = null;
            }
        });
        this.$watch('$props.entity.' + this.patientIdProp, (val) => {
            if (_.isFilled(val)) {
                if (_.isFilled(this.entity[this.patientNameProp])) {
                    this.entity[this.patientNameProp] = null;
                }
                this.loadPatient(val);
            } else {
                this.patient = null;
            }
        });
        if (_.isFilled(this.entity[this.patientIdProp])) {
            this.loadPatient(this.entity[this.patientIdProp]);
        }
    },
    methods: {
        searchPatient() {
            this.$modalComponent(SearchPatient, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    this.patient = patient;
                    this.entity[this.patientIdProp] = patient.id;
                    this.entity[this.patientNameProp] = null;
                    dialog.close();
                    this.$emit('selected', patient);
                },
            }, {
                header: __('Выбор пациента'),
                width: '1270px',
            });
        },
        clearPatient() {
            this.patient = null;
            this.entity[this.patientIdProp] = null;
            this.$emit('cleared');
        },
        loadPatient(id) {
            if (this.patient === null || this.patient.id != id) {
                this.patient = new Patient({id});
                this.patient.fetch();
            }
        },
    }
}
</script>