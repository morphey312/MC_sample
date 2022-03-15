<template>
    <service-type-form 
        :model="model"
        :employee="employee">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
            <el-button
                v-if="$can('employee-service-types.send-ehealth')"
                type="primary"
                :disabled="isMakingRequest"
                @click="createAndPost">
                {{ __('Создать и отправить в e-Health') }}
            </el-button>
        </div>
    </service-type-form>
</template>

<script>
import EmployeeServiceType from '@/models/employee/service-type';
import ServiceTypeForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import EhealthMixin from './mixins/ehealth';

export default {
    mixins: [
        CreateMixin,
        EhealthMixin,
    ],
    components: {
        ServiceTypeForm,
    },
    props: {
        employee: Object,
    },
    data() {
        return {
            model: new EmployeeServiceType({
                employee_id: this.employee.id,
            }),
        }
    },
    methods: {
        createAndPost() {
            this.$clearErrors();
            this.prepareRequest(this.employee).then(() => {
                this.create();
            });
        },
    },
}
</script>