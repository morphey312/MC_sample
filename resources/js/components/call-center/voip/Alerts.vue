<template>
    <div class="alerts">
        <template v-if="patient">
            <alert 
                type="black-mark"
                v-if="patient.black_mark">
                {{ __('У пациента черная метка:') }} {{ blackMarkInfo }}
            </alert>
            <alert 
                type="skk"
                v-if="patient.is_skk">
                {{ __('Обращение в СКК:') }} {{ skkInfo }}
            </alert>
            <alert 
                type="info"
                v-if="patient.comment">
                {{ __('Примечание:') }} {{ patient.comment }}
            </alert>
            <alert 
                type="attention"
                v-if="patient.is_attention">
                {{ __('Метка «Внимание!»') }}: {{ patient.attention_comment }}
            </alert>
        </template>
        <alert
            type="conference"
            v-if="isConference">
            {{ __('Конференц-звонок:') }}
            {{ $formatter.listFormat(call.participants, 'versaName') }}.
            <a 
                href="#"
                @click.prevent="showConference">
                {{ __('Показать') }}
            </a>
        </alert>
        <alert 
            type="info"
            v-if="needHighlighSource(callSource)">
            {{ __('Источник обработки:') }} {{ $handbook.getOption('call_log_source', callSource) }}
        </alert>
    </div>
</template>

<script>
import { ConferenceCall } from '@/services/sip-ua/conference-call';
import Conference from './Conference.vue';
import  { PatientContact } from '@/services/sip-ua/process-state';
import CONSTANTS from '@/constants';

export default {
    props: {
        call: Object,
    },
    data() {
        return {
            patient: null,
            callSource: null,
            isConference: null,
        };
    },
    computed: {
        blackMarkInfo() {
            return this.$formatter.listFormat([
                this.$handbook.getOption('black_mark_reason', this.patient.black_mark_reason),
                this.patient.black_mark_comment,
            ]);
        },
        skkInfo() {
            return this.$formatter.listFormat([
                this.$handbook.getOption('skk_reason', this.patient.skk_reason),
                this.patient.skk_comment,
            ]);
        },
    },
    mounted() {
        this.checkConference(this.call);
        this.checkCallSource(this.$store.state.processState.processLog);
        this.checkPatient(this.$store.state.processState.currentContact);
    },
    methods: {
        showConference() {
            this.$modalComponent(Conference, {
                ua: this.call.ua,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            },
            {
                header: __('Конференц-звонок'),
                width: '600px',
            });
        },
        checkConference(call) {
            if (call !== null) {
                this.isConference = call instanceof ConferenceCall;
            } else {
                this.isConference = false;
            }
        },
        checkCallSource(process) {
            if (process !== null && process !== undefined) {
                this.callSource = process.source;
            } else {
                this.callSource = null;
            }
        },
        checkPatient(contact) {
            if (contact instanceof PatientContact) {
                this.patient = contact.patient;
            } else {
                this.patient = null;
            }
        },
        needHighlighSource(source) {
            return [
                CONSTANTS.CALL_LOG.SOURCE.CALLBACK,
                CONSTANTS.CALL_LOG.SOURCE.WEBCALLBACK,
            ].indexOf(source) !== -1;
        },
    },
    watch: {
        call(call) {
            this.checkConference(call);
        },
        ['$store.state.processState.currentContact'](contact) {
            this.checkPatient(contact);
        },
        ['$store.state.processState.processLog'](process) {
            this.checkCallSource(process);
        },
    },
};
</script>