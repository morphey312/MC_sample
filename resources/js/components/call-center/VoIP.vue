<template>
    <page
        :title="__('Call-центр')"
        type="call-center">
        <template
            slot="header-addon">
            <header-buttons :ua="ua" />
        </template>
        <alerts :call="ua.call" />
        <template v-if="hasVoIP && $canProcessCalls()">
            <section
                v-if="isPrimaryTab === true"
                v-loading="!ua.connected"
                :element-loading-text="__('Подключение к серверу телефонии...')"
                class="grey">
                <main-control :ua="ua" />
            </section>
            <alert
                type="info"
                v-else-if="isPrimaryTab === false">
                {{ __('Телефония активна в другой вкладке.') }}
            </alert>
        </template>
        <el-tabs v-model="activeTab">
            <el-tab-pane
                v-if="$canAccess('process-logs')"
                :lazy="true"
                :label="__('Архив')"
                name="archive">
                <call-process-logs-pane />
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('calls') || $canAccess('appointments')"
                :lazy="true"
                :label="__('Записи и звонки')"
                name="appointments">
                <calls-appointments-pane
                    ref="appointments"/>
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('call-logs')"
                :lazy="true"
                :label="__('Реальные звонки')"
                name="calls">
                <call-logs-pane />
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('call-logs-missed')"
                :lazy="true"
                :label="__('Пропущенные звонки')"
                name="missed-calls">
                <missed-calls-pane />
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('sms-reminders')"
                :lazy="true"
                label="SMS"
                name="sms">
                <sms-reminders-pane />
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('call-requests')"
                :lazy="true"
                :label="__('Заявки на прозвон')"
                name="callback">
                <call-requests-pane
                    @show-appointments="showAppointments" />
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('site-enquiries.process')"
                :lazy="true"
                name="feedback">
                <span slot="label">
                    {{ __('Форма с сайта') }}
                    <span v-if="counters.enquiries !== 0" class="counter">{{ counters.enquiries }}</span>
                </span>
                <process-enquiry-pane />
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('wait-list-record.process') || $canAccess('wait-list-record')"
                :lazy="true"
                name="waitListRecord">
                <span slot="label">
                    {{ __('Запись в листе ожидания') }}
                    <span v-if="counters.wait_list_records !== 0" class="counter">{{ counters.wait_list_records }}</span>
                </span>
                <process-wait-list-record-pane />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                name="tasks">
                <span slot="label">
                    {{ __('Индивидуальные задания') }}
                    <span v-if="counters.tasks !== 0" class="counter">{{ counters.tasks }}</span>
                </span>
                <tasks-pane />
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('site-enquiries')"
                :lazy="true"
                name="enquiries"
                :label="__('База заявок с сайта')">
                <site-enquiries-pane />
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('wait-list-record')"
                :lazy="true"
                name="waitListRecords"
                :label="__('База ожидания')">
                <wait-list-records />
            </el-tab-pane>
        </el-tabs>
        <div class="sticky-footer">
            <status :state-manager="ua.stateManager" />
        </div>
    </page>
</template>

<script>
import {UA} from '@/services/sip-ua';
import HeaderButtons from './voip/HeaderButtons.vue';
import MainControl from './voip/MainControl.vue';
import Status from './voip/Status.vue';
import Alerts from './voip/Alerts.vue';
import CallLogsPane from './call-logs/Pane.vue';
import MissedCallsPane from './missed-calls/Pane.vue';
import SmsRemindersPane from './sms-reminders/Pane.vue';
import CallProcessLogsPane from './call-process-logs/Pane.vue';
import CallsAppointmentsPane from './calls-appointments/Pane.vue';
import CallRequestsPane from './call-requests/Pane.vue';
import TasksPane from './personal-tasks/Pane.vue';
import ProcessEnquiryPane from './site-enquiries/Process.vue';
import SiteEnquiriesPane from './site-enquiries/Pane.vue';
import ProcessWaitListRecordPane from './wait-list-records/Process.vue';
import WaitListRecords from './wait-list-records/Pane.vue';
import voipCounters from '@/services/voip/counters';
import tabsync from '@/services/tabsync';

export default {
    components: {
        HeaderButtons,
        MainControl,
        Status,
        CallLogsPane,
        MissedCallsPane,
        SmsRemindersPane,
        CallProcessLogsPane,
        CallsAppointmentsPane,
        CallRequestsPane,
        TasksPane,
        ProcessEnquiryPane,
        SiteEnquiriesPane,
        Alerts,
        ProcessWaitListRecordPane,
        WaitListRecords,
    },
    data() {
        return {
            ua: UA,
            activeTab: this.getFirstTab(),
            counters: voipCounters,
            isPrimaryTab: undefined,
        };
    },
    computed: {
        hasVoIP() {
            return this.$store.state.user.hasVoIP;
        },
    },
    mounted() {
        this.fetchCounters();
        _.waitUntil(() => tabsync.isPrimaryTab !== undefined).then(() => {
            this.isPrimaryTab = tabsync.isPrimaryTab;
        });
    },
    methods: {
        fetchCounters() {
            this.counters.load();
        },
        showAppointments(patient) {
            this.activeTab = 'appointments';
            this.$nextTick(() => {
                this.$refs.appointments.filterByPatient(patient);
            });
        },
        getFirstTab() {
            if (this.$canAccess('process-logs')) {
                return 'archive';
            }
            if (this.$canAccess('calls') || this.$canAccess('appointments')) {
                return 'appointments';
            }
            if (this.$canAccess('call-logs')) {
                return 'calls';
            }
            if (this.$canAccess('call-logs-missed')) {
                return 'missed-calls';
            }
            if (this.$canAccess('call-requests')) {
                return 'callback';
            }
            return 'tasks';
        },
    },
};
</script>
