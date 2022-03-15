<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="8">
                        <form-select
                            :entity="model"
                            :options="clinics"
                            property="clinic_id"
                            :filterable="true"
                            :label="__('Клиника')"
                        />
                        <form-select
                            :entity="model"
                            :options="operators"
                            property="operator_id"
                            :label="__('Оператор')"
                        />
                    </el-col>
                    <el-col :span="8">
                        <form-select
                            :entity="model"
                            :options="specializations"
                            property="specialization_id"
                            :label="__('Специализация')"
                        />
                        <form-date
                            :entity="model"
                            property="date"
                            :label="__('Крайний срок выполнения')" />
                    </el-col>
                    <el-col :span="8">
                        <form-text
                            :entity="model"
                            property="comment"
                            :label="__('Примечание')"
                        />
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="8">
                        <form-upload
                            ref="attachments"
                            :entity="model"
                            property="attachments"
                        />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <select-patients v-model="model.patients" />
            </section>
        </div>
        <slot name="buttons" :count-uploads="$refs.attachments ? $refs.attachments.countUploads : 0"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import PatientRepository from '@/repositories/patient';
import SelectPatients from '@/components/patients/select/SelectPatients.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        SelectPatients,
    },
    props: {
        model: {
            type: Object,
        },
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            operators: new EmployeeRepository({
                limitClinics: this.limitClinics,
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR}
            }),
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
            }),
            specializations: new SpecializationRepository({
                limitClinics: this.limitClinics,
            }),
        }
    },
    watch: {
        ['model.clinic_id'](val) {
            this.specializations.setFilters(_.onlyFilled({clinic: val}));
            this.operators.setFilters(_.onlyFilled({
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR,
                clinic: val,
            }));
        },
    },
}
</script>
