<template>
    <div class="status-panel">
        <span class="current-status">{{ __('Cтатус:') }}
            <el-popover
                placement="top"
                :title="__('Очереди')"
                width="250"
                trigger="hover"
                @show="updateQueueList">
                <p>{{ __('Пауза:') }} <i>{{ queuePause }}</i></p>
                <p>{{ __('Активно:') }} <i>{{ queueActive }}</i></p>
                <b slot="reference">{{ currentStatus }} *</b>
            </el-popover>
        </span>
        <span class="spacer" />
        <span class="session-started">{{ __('Начало работы:') }} <b>{{ $formatter.timeFormat(sessionStart) || __('работа не начата') }}</b></span>
        <span class="spacer" />
        <span class="pauses-time">{{ __('Паузы (нет на месте):') }} <b>{{ pauseTotalCount }} {{ __('пауз, общее время:') }} {{ $formatter.durationFormat(pauseTotalDuration, 'ms') || __('0 сек') }}</b></span>
        <span class="spacer" />
        <span class="call-time">{{ __('В разговоре:') }} <b>{{ $formatter.durationFormat(callTotalDuration, 'ms') || __('0 сек') }}</b></span>
        <span class="spacer" />
        <span class="current-time">{{ __('Время:') }} <b>{{ $formatter.timeFormat(currentTime) }}</b></span>
        <span class="spacer" />
        <span
            v-if="isVoipContainer"
            class="toggle-voip-client">
            <a href="#" @click.prevent="setDefaultClient">{{ __('Включить клиент по умолчанию') }}</a>
        </span>
        <span
            v-else
            class="toggle-voip-client">
            <a href="#" @click.prevent="setVoipContainer">{{ __('Включить экспериментальный клиент') }}</a>
        </span>
    </div>
</template>

<script>
import {
    STATE_OFFLINE,
    STATE_ONLINE,
    STATE_BUSY,
    STATE_WRAP_UP,
    STATE_AWAY,
    STATE_CONFERENCE,
} from '@/services/sip-ua/state-manager';
import serverTime from '@/services/server-time';
import {UA} from '@/services/sip-ua';

export default {
    props: {
        stateManager: Object,
    },
    computed: {
        currentStatus() {
            switch (this.stateManager.state.name) {
                case STATE_OFFLINE: return __('не в работе');
                case STATE_ONLINE: return __('свободен');
                case STATE_BUSY: return __('разговор');
                case STATE_WRAP_UP: return __('обработка');
                case STATE_AWAY: return __('нет на месте');
                case STATE_CONFERENCE: return __('конференция');
            }
            return '';
        },
        sessionStart() {
            return this.stateManager.sessionStartTime;
        },
        pauseTotalCount() {
            return this.stateManager.pauseTotalCount;
        },
        isVoipContainer() {
            return localStorage.getItem('use_voip_container') === 'true';
        },
    },
    data() {
        return {
            currentTime: null,
            pauseTotalDuration: 0,
            callTotalDuration: 0,
            queuePause: '-',
            queueActive: '-',
        };
    },
    created() {
        this.updateTimersListener = () => {
            this.updateTimers();
        }
    },
    mounted() {
        this.$ticker.on(this.updateTimersListener);
        this.updateTimers();
    },
    beforeDestroy() {
        this.$ticker.off(this.updateTimersListener);
    },
    methods: {
        updateTimers() {
            this.currentTime = serverTime.now(),
                this.pauseTotalDuration = this.stateManager.pauseTotalDuration;
            this.callTotalDuration = this.stateManager.callTotalDuration;
        },
        setDefaultClient() {
            localStorage.removeItem('use_voip_container');
            location.reload();
        },
        setVoipContainer() {
            localStorage.setItem('use_voip_container', 'true');
            location.reload();
        },
        updateQueueList() {
            let paused = UA.pausedQueues;
            let active = UA.activeQueues;
            this.queuePause = paused.length !== 0 ? this.$formatter.listFormat(paused) : '-';
            this.queueActive = active.length !== 0 ? this.$formatter.listFormat(active) : '-';
        },
    },
};
</script>
