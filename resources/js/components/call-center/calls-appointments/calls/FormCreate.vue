<template>
    <content-placeholder 
        v-if="model === null" />
    <call-form 
        v-else
        :model="model"
        :limit-clinics="$isCreationLimited('calls')">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button 
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </call-form>
</template>

<script>
import Call from '@/models/call';
import Patient from '@/models/patient';
import Employee from '@/models/employee';
import ClinicRepository from '@/repositories/clinic';
import CONSTANT from '@/constants';
import CreateMixin from '@/mixins/generic-create';
import InitFromProcessMixin from '@/components/patients/mixins/init-from-process';
import CallForm from './Form.vue';

export default {
    mixins: [
        CreateMixin,
        InitFromProcessMixin,
    ],
    components: {
        CallForm,
    },
    data() {
        return {
            model: null,
        }
    },
    mounted() {
        this.initFromProcess().then(() => {
            this.initTime();
            this.initOperator();
            this.initDefaultClinic();
        });
    },
    methods: {
        initFromProcess() {
            let state = this.$store.state.processState;
            let contact = state.currentContact;
            
            if (contact !== undefined && contact.isPatientContact) {
                let patient = new Patient({id: contact.id});
                return patient.fetch().then(() => {
                    this.initPatientFromProcessState(patient, state, contact);
                    this.model = new Call({contact: patient});
                    this.initCallFromProcessState(this.model, state);
                });
            }
            
            if (contact !== undefined && contact.isEmployeeContact && state.phoneNumber) {
                let employee = new Employee({id: contact.id});
                return employee.fetch().then(() => {
                    this.model = new Call({contact: employee});
                    this.initCallFromProcessState(this.model, state);
                });
            }
            
            if (contact !== undefined) {
                let patient = new Patient();
                this.initPatientFromProcessState(patient, state, contact);
                this.model = new Call({contact: patient});
            } else {
                this.model = new Call();
            }
            
            this.initCallFromProcessState(this.model, state);
            return Promise.resolve();
        },
        initCallFromProcessState(call, state) {
            if (state.processing) {
                if (state.processLog.is_first_visit == CONSTANT.PROCESS_LOG.VISIT_TYPE.FIRST) {
                    call.is_first = CONSTANT.CALL.REQUEST_TYPES.FIRST;
                } else if (state.processLog.is_first_visit == CONSTANT.PROCESS_LOG.VISIT_TYPE.RETURN) {
                    call.is_first = CONSTANT.CALL.REQUEST_TYPES.REPEATED;
                }
            }
        },
        initTime() {
            this.model.time = this.$moment().format('HH:mm:ss');
        },
        initOperator() {
            this.model.employee_id = this.$store.state.user.employee_id;
        },
        initDefaultClinic() {
            let contact = this.model.contact;
            if ((contact instanceof Patient) && (contact.clinics.length === 0)) {
                let clinics = new ClinicRepository();
                clinics.getDefaultClinic().then((res) => {
                    if (res) {
                        contact.clinics = [res.id];
                    }
                });
            }
        },
    },
}
</script>