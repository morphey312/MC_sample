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
                @click="update"
                type="primary">
                {{ __('Сохранить') }}
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
        item: Object,
        patient: Object,
    },
    data() {
        return {
            model: this.item.clone(),
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
        findPatient() {
            return this.searchPatient(this.setPatient, {
                skipId: this.getRelatives(),
            });
        },
        setPatient(patient) {
            this.model.id = patient.id;
            this.model.full_name = patient.full_name;
            this.model.birthday = patient.birthday;
        },
        update() {
            this.$clearErrors();
            this.model.validate().then((errors) => {
                if (_.isEmpty(errors)) {
                    return this.$emit('saved', this.model); 
                }

                return this.$displayErrors({errors});
            });
        },
    },
}
</script>