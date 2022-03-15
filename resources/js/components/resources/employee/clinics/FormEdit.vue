<template>
    <clinic-form 
        :model="model"
        :limit-clinics="limitClinics">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </clinic-form>
</template>

<script>
import EmployeeClinic from '@/models/employee/clinic';
import ClinicForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        ClinicForm,
    },
    props: {
        item: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            model: new EmployeeClinic({id: this.item.id}),
        };
    },
    mounted() {
        this.model.fetch([
            'specializations',
            'enquiry_categories',
            'doctor',
        ]);
    },
}
</script>
