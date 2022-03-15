<template>
    <section
        v-loading="loading">
        <div 
            v-if="enquiry"
            class="site-enquiry">
            <div class="manage-table mb-20">
                <enquiry-details 
                    v-if="enquiry !== null"
                    :enquiry="enquiry" />
            </div>
            <el-button 
                :disabled="!canStartProcessEnquiry"
                type="primary"
                @click="startProcessEnquiry">
                {{ __('Взять в обработку') }}
            </el-button>
            <el-button 
                v-if="isPostPayment"
                :disabled="!$canManage('appointments.update', [enquiry.appointment.clinic_id])"
                @click="showAppointment">
                {{ __('Открыть запись') }}
            </el-button>
            <div v-if="isPostPayment">
                <h3>{{ __('Оплаченные услуги') }}</h3>
                <paid-service-list :enquiry="enquiry"/>
            </div>
            <div 
                v-else-if="hasServices || hasUnpaidServices"
                class="site-enquiry mt-20">
                <h3>{{ __('Выбранные услуги') }}</h3>
                <service-list :enquiry="enquiry"/>
            </div>
        </div>
        <no-data-placeholder 
            v-else
            :message="__('Нет заявок для обработки')" />
    </section>
</template>

<script>
import EnquiryDetails from './Details.vue';
import ServiceList from './ServiceList.vue';
import PaidServiceList from './PaidServiceList.vue';
import SiteEnquiryRepository from '@/repositories/site-enquiry';
import CONSTANT from '@/constants';
import {STATE_ONLINE, STATE_AWAY} from '@/services/sip-ua/state-manager';
import {UA} from '@/services/sip-ua';
import AppointmentManager from '@/components/appointments/mixin/manager';
import Appointment from '@/models/appointment';

export default {
    mixins: [
        AppointmentManager,
    ],
    components: {
        EnquiryDetails,
        ServiceList,
        PaidServiceList,
    },
    data() {
        return {
            enquiry: null,
            loading: true,
            ua: UA,
        };
    },
    computed: {
        processing() {
            return this.$store.state.processState.processing;
        },
        canStartProcessEnquiry() {
            return (this.ua.state === STATE_ONLINE || this.ua.state === STATE_AWAY) && !this.processing;
        },
        hasServices() {
            return this.enquiry.service_list.length !== 0 || this.enquiry.analysis_list.length !== 0;
        },
        hasUnpaidServices() {
            return this.enquiry.unpaid_service_list.length !== 0 || this.enquiry.unpaid_analysis_list.length !== 0;
        },
        isPostPayment() {
            return this.enquiry.appointment != null;
        },
    },
    mounted() {
        this.loadNextEnquiry();
        this.$eventHub.$on('processLog:completed', this.onProcessCompleted);
        this.$eventHub.$on('broadcast.new_site_enquiry', this.onNewEnquiry);
    },
    created() {
        this.onProcessCompleted = (processLog) => {
            if (processLog.enquiry !== null) {
                this.loadNextEnquiry();
            }
        };
        this.onNewEnquiry = (data) => {
            if (this.enquiry === null) {
                this.loadNextEnquiry();
            }
        };
    },
    beforeDestroy() {
        this.$ticker.off(this.updateTimers);
        this.$eventHub.$off('processLog:completed', this.onProcessCompleted);
        this.$eventHub.$off('broadcast.new_site_enquiry', this.onNewEnquiry);
    },
    methods: {
        loadNextEnquiry() {
            let repository = new SiteEnquiryRepository();
            this.loading = true;
            repository.fetch({
                operator: this.$store.state.user.employee_id,
                status: CONSTANT.SITE_ENQUIRY.STATUS.NEW,
            }, null, ['default', 'patient', 'services', 'payed', 'appointment'], 1, 1).then((result) => {
                this.loading = false;
                if (result.rows.length !== 0) {
                    this.enquiry = result.rows[0];
                } else {
                    this.enquiry = null;
                }
            });
        },
        startProcessEnquiry() {
            this.$eventHub.$emit('process:enquiry', this.enquiry);
        },
        showAppointment() {
            let appointment = new Appointment({id: this.enquiry.appointment.id});
            appointment.fetch([
                'doctor',
                'clinic',
            ]).then(() => {
                this.makeDaySheetData(appointment, true).then(() => {
                    this.editAppointment((appointment) => {
                        this.daySheetData = {};
                    }, appointment);
                });
            });
        },
    },
}
</script>