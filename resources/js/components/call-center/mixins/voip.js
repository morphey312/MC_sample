import {
    STATE_OFFLINE,
    STATE_ONLINE,
    STATE_BUSY,
    STATE_WRAP_UP,
    STATE_AWAY,
    STATE_CONFERENCE,
} from '@/services/sip-ua/state-manager';

export default {
    computed: {
        canStartSession() {
            return this.isState(STATE_OFFLINE)
                && this.ua.connected;
        },
        canEndSession() {
            return this.canTransit(STATE_OFFLINE)
                && this.hasCall() === false;
        },
        canStartCall() {
            return this.canTransit(STATE_BUSY) 
                && this.hasCall() === false;
        },
        canAnswerCall() {
            return this.hasCall((call) => call.pending && call.isIncoming);
        },
        canRejectCall() {
            return this.hasCall((call) => call.pending && call.isIncoming);
        },
        canTerminateCall() {
            return this.hasCall((call) => call.isIncoming && call.progress || call.isOutgoing);
        },
        canHoldCall() {
            return this.isState(STATE_CONFERENCE) === false
                && this.hasCall((call) => call.canBeParked);
        },
        canStartConference() {
            return this.isState(STATE_CONFERENCE) === false
                && this.hasCall((call) => call.progress)
                && this.ua.parkedCalls.length !== 0;
        },
        canUnpause() {
            return this.isState(STATE_AWAY);
        },
        canPause() {
            return this.canTransit(STATE_AWAY)
                && this.hasCall() === false;
        },
        isConference() {
            return this.isState(STATE_CONFERENCE);
        },
        isCallCenter() {
            return this.$route && this.$route.name == 'voip'; 
        },
    },
    methods: {
        goVoip() {
            this.$router.push({name: 'voip'});
        },
        isState(state) {
            return this.ua.state === state;
        },
        canTransit(state) {
            return this.ua.canTransit(state);
        },
        hasCall(predicate = null) {
            if (this.ua.call === null) {
                return false;
            }
            if (predicate === null) {
                return true;
            }
            return predicate(this.ua.call);
        },
        startSession() {
            this.ua.startSession();
        },
        endSession() {
            this.ua.endSession();
        },
        answerCall() {
            this.ua.answer();
        },
        rejectCall() {
            this.ua.reject();
        },
        terminateCall() {
            this.ua.terminate();
        },
        endWrapUp() {
            this.ua.stateManager.transit(STATE_ONLINE);
        },
        startCall(sip) {
            this.ua.makeCall(sip);
        },
        holdCall() {
            this.ua.park().catch(() => {
                this.$error(__('Не удалось поставить абонента на удержание'));
            });
        },
        unholdCall(call) {
            this.ua.withdrawParked(call);
        },
    },
};