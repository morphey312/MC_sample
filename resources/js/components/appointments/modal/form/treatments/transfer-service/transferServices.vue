<template>
    <el-row class="tab-switcher" loading="loading">
        <el-tabs v-model="currentTab" class="tab-group-grey">
            <el-tab-pane
                name="services"
                :label="__('Выбор позиций')">
                <services-table
                    v-if="serviceType === CONSTANTS.APPOINTMENT_SERVICE.SERVICE_TYPE.SERVICE"
                    :showExtraFields="showExtraFields"
                    :appointment="appointment"
                    @selection-changed="selectionChanged" />
                <analysis-table
                    v-else
                    :showExtraFields="showExtraFields"
                    :appointment="appointment"
                    @selection-changed="selectionChanged" />
            </el-tab-pane>
            <el-tab-pane
                name="appointments"
                :label="__('Выбор записи')">
                <appointments-table
                   :patient-id="appointment.patient_id"
                   @selection-changed="setActiveAppointment"/>
            </el-tab-pane>
        </el-tabs>
        <div class="dialog-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="selectedAppointment === null || selectedServices.length === 0"
                @click="transfer">
                {{ __('Перенести') }}
            </el-button>
        </div>
    </el-row>
</template>

<script>
import AppointmentsTable from './AppointmentsList.vue';
import AnalysisTable from './Analysis.vue';
import ServicesTable from './Services.vue';
import CONSTANTS from '@/constants';
import BatchRequest from "@/services/batch-request";

export default {
    components: {
        AppointmentsTable,
        AnalysisTable,
        ServicesTable,
    },
    props:{
        serviceType: {
            type: String,
            required: true
        },
        showExtraFields: Boolean,
        appointment: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            batchRequest: new BatchRequest('/api/v1/appointments/services/transfer'),
            loading: false,
            CONSTANTS: CONSTANTS,
            currentTab: 'services',
            selectedAppointment: null,
            selectedServices: [],
        };
    },
    methods: {
        selectionChanged(selection) {
            this.selectedServices = selection;
        },
        cancel() {
            this.$emit('cancel');
        },
        transfer() {
            if (this.selectedAppointment.id === this.appointment.id) {
                this.$warning(__('Выбирите другую запись'));
            }
            this.batchRequest.reset();
            this.loading = true;

            this.selectedServices.map(item => {
                let service = this.appointment.services.find((service) => {
                    return service.analysis_id ? service.service_id === item : service.id === item
                });
                service.appointment_id = this.selectedAppointment.id;
                service.card_specialization_id = this.selectedAppointment.specialization_id

                this.batchRequest.update(service);
            })

            this.batchRequest.submit(true).then((result) => {
                if (result.failure.length !== 0) {
                    this.$error(__('Не удалось сохранить некоторые данные'));
                } else {
                    this.$info(__('Данные успешно обновлены'));
                }
                this.loading = false;
                this.$emit('done');
            }).catch((error) => {
                this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
                this.$emit('cancel');
            });
        },
        setActiveAppointment(item) {
            this.selectedAppointment = item[0];
        },
    }
}
</script>
