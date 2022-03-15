<template>
    <div class="buttons">
        <span 
            v-if="ua.connected"
            class="sip-number">
            {{ __('SIP') }}: <span>{{ ua.number }}</span>
        </span>
        <a 
            v-if="$canCreate('calls')"
            href="#"
            @click.prevent="addCall">
            <svg-icon name="plus-alt" class="icon-small icon-blue">
                {{ __('Добавить звонок') }}
            </svg-icon>
        </a>
        <a 
            v-if="$canCreate('patients')"
            href="#"
            @click.prevent="addPatient">
            <svg-icon name="user-female-alt" class="icon-small icon-blue">
                {{ __('Добавить пациента') }}
            </svg-icon>
        </a>
        <a 
            v-if="$canCreate('appointments')"
            href="#"
            @click.prevent="addAppointment">
            <svg-icon name="report-alt" class="icon-small icon-blue">
                {{ __('Записать на прием') }}
            </svg-icon>
        </a>
        <a 
            v-if="$canProcessCalls() && canStartSession"
            href="#"
            @click.prevent="startSession">
            <svg-icon name="play-alt" class="icon-small icon-blue">
                {{ __('Начать работу') }}
            </svg-icon>
        </a>
        <a 
            v-if="$canProcessCalls() && canUnpause"
            href="#"
            @click.prevent="unpause">
            <svg-icon name="play-alt" class="icon-small icon-blue">
                {{ __('Снять с паузы /') }} {{ $formatter.durationShortFormat(currentPauseDuration, 'ms') || '0:00' }}
            </svg-icon>
        </a>
        <a 
            v-if="$canProcessCalls() && canPause"
            href="#"
            @click.prevent="pause">
            <svg-icon name="pause-alt" class="icon-small icon-blue">
                {{ __('Сделать перерыв') }}
            </svg-icon>
        </a>
    </div>
</template>

<script>
import VoipMixins from '../mixins/voip';
import CallCreate from '@/components/call-center/calls-appointments/calls/FormCreate.vue';
import PatientCreate from '@/components/patients/patient/FormCreate.vue';

export default {
    mixins: [
        VoipMixins,
    ],
    props: {
        ua: Object,
    },
    data() {
        return {
            currentPauseDuration: 0,
        };
    },
    created() {
        this.updateTimers = () => {
            if (this.ua.stateManager.isPaused) {
                this.currentPauseDuration = this.ua.stateManager.currentPauseDuration;
            }
        }
    },
    mounted() {
        this.$ticker.on(this.updateTimers);
    },
    beforeDestroy() {
        this.$ticker.off(this.updateTimers);
    },
    methods: {
        addCall() {
            this.$modalComponent(CallCreate, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Добавить звонок'),
                width: '1200px',
            });
        },
        addPatient() {
            this.displayCreatePatientForm();
        },
        addAppointment() {
            this.$router.push({name: 'appointment-schedule'});
        },
        pause() {
            this.currentPauseDuration = 0;
            this.ua.available = false;
        },
        unpause() {
            this.ua.available = true;
        },
    },
};
</script>