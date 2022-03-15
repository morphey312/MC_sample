<template>
    <section
        v-loading="loading">
        <div 
            v-if="waitListRecord"
            class="site-enquiry">
            <div class="manage-table mb-20">
                <record-details 
                    v-if="waitListRecord !== null"
                    :record="waitListRecord" />
            </div>
            <el-button 
                :disabled="!canStartProcessRecord"
                type="primary"
                @click="startProcessRecord">
                {{ __('Взять в обработку') }}
            </el-button>
            <div class="site-enquiry mt-20" v-if="waitListRecord.prepayment_service">
                <h3>{{ __('Выбранные услуги') }}</h3>
                <service-list :service="waitListRecord.prepayment_service"/>
            </div>
        </div>
        <no-data-placeholder 
            v-else
            :message="__('Нет заявок для обработки')" />
    </section>
</template>

<script>
import RecordDetails from './Details.vue';
import WaitListRecordRepository from '@/repositories/wait-list-record';
import WaitListRecord from '@/models/wait-list-record';
import CONSTANTS from '@/constants';
import voipCounters from '@/services/voip/counters';
import {STATE_ONLINE, STATE_AWAY} from '@/services/sip-ua/state-manager';
import {UA} from '@/services/sip-ua';
import ServiceList from './ServiceList.vue';
import CancelProcessing from './CancelProcessing.vue';

export default {
    components: {
        RecordDetails,
        ServiceList,
    },
    data() {
        return {
            waitListRecord: null,
            loading: true,
            ua: UA,
        };
    },
    computed: {
        processing() {
            return this.$store.state.processState.processing;
        },
        canStartProcessRecord() {
            return (this.ua.state === STATE_ONLINE || this.ua.state === STATE_AWAY) && !this.processing;
        },
    },
    mounted() {
        this.loadNextRecord();
        this.$eventHub.$on('processLog:completed', this.onProcessCompleted);
        this.$eventHub.$on('broadcast.wait_list_record_found', this.onNewRecord);
    },
    created() {
        this.onProcessCompleted = (processLog) => {
            if (processLog.wait_list_record !== null) {
                this.loadNextRecord();
            }
        };
        this.onNewRecord = (data) => {
            if (this.waitListRecord === null) {
                this.loadNextRecord();
            }
        };
    },
    beforeDestroy() {
        this.$ticker.off(this.updateTimers);
        this.$eventHub.$off('processLog:completed', this.onProcessCompleted);
        this.$eventHub.$off('broadcast.wait_list_record_found', this.onNewRecord);
    },
    methods: {
        loadNextRecord() {
            let repository = new WaitListRecordRepository();
            this.loading = true;
            repository.fetch({
                operator: this.$store.state.user.employee_id,
                status: CONSTANTS.WAIT_LIST_RECORD.STATUS.NEW,
                blank_cancel_reason: true,
            }, null, ['clinic', 'patient', 'doctor', 'specialization', 'prepayment_service'], 1, 1).then((result) => {
                this.loading = false;
                if (result.rows.length !== 0) {
                    this.waitListRecord = result.rows[0];
                } else {
                    this.waitListRecord = null;
                }
            });
        },
        startProcessRecord() {
            let record = new WaitListRecord({id: this.waitListRecord.id});
            
            record.fetch().then(() => {
                if (record.status === CONSTANTS.WAIT_LIST_RECORD.STATUS.NEW) {
                    this.$eventHub.$emit('process:wait-list-record', this.waitListRecord);
                } else if (record.status === CONSTANTS.WAIT_LIST_RECORD.STATUS.PROCESSED) {
                    this.$modalComponent(CancelProcessing, {}, {
                        cancel: (dialog) => {
                            dialog.close();
                            this.loadNextRecord();
                        },
                    }, {
                        width: '400px',
                        closeOnEscape: false,
                        showClose: false,
                    });
                } else {
                    this.$warning(__('Вы не можете взять заявку в обработку, у нее изменился статус'));
                    this.loadNextRecord();
                }
            });
            
        },
    },
}
</script>