<template>
    <relation-form 
        ref="relationForm"
        :model="model"
        :patient="patient" >
        <div 
            slot="buttons" 
            class="form-footer">
            <el-button @click="findPatient">
                {{ __('Выбрать существующего пациента') }}
            </el-button>
            <el-button @click="createPatient">
                {{ __('Добавить нового пациента') }}
            </el-button>
            <el-button 
                @click="create"
                type="primary">
                {{ __('Добавить') }}
            </el-button>
            <el-button @click="cancel">
                {{ __('Отмена') }}
            </el-button>
        </div>
    </relation-form>
</template>
<script>
import RelationForm from './Form.vue';
import PatientRelative from '@/models/patient/relative';
import SearchMixin from '@/components/patients/mixins/search';

export default {
    mixins: [
        SearchMixin,
    ],
    components: {
        RelationForm,
    },
    props: {
        patient: Object,
    },
    data() {
        return {
            model: new PatientRelative(),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        createPatient() {
            this.displayCreatePatientForm((patient) => {
                this.setPatient(patient);
            });
        },
        setPatient(patient) {
            this.model.id = patient.id;
            this.model.full_name = patient.full_name;
            this.model.birthday = patient.birthday;
        },
        findPatient() {
            return this.searchPatient(this.setPatient, {
                skipId: this.getRelatives(),
            });
        },
        create() {
            this.$clearErrors();
            this.model.validate().then((errors) => {
                if (_.isEmpty(errors)) {
                    return this.$emit('created', this.model); 
                }

                return this.$displayErrors({errors});
            });
        },
    },
}
</script>