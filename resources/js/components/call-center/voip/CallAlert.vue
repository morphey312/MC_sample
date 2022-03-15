<template>
    <transition name="fade">
        <div
            v-if="!isCallCenter && canAnswerCall"
            class="call-alert">
            <div class="body">
                <svg-icon 
                    :name="name === null ? 'question-alt' : 'user-alt'"
                    class="icon-large" />
                <div class="details">
                    <div>{{ __('Вам звонит:') }}</div>
                    <div>{{ name || __('Неизвестный') }}</div>
                    <div>{{ $formatter.phoneNumberFormat(phone) }}</div>
                </div>
            </div>
            <div class="footer">
                <el-button
                    type="primary"
                    :disabled="!canAnswerCall"
                    @click="answerCallAndRedirect">
                    {{ __('Принять звонок') }}
                </el-button>
                <el-button
                    type="danger"
                    :disabled="!canRejectCall"
                    @click="rejectCall">
                    {{ __('Отклонить звонок') }}
                </el-button>
            </div>
        </div>
    </transition>
</template>

<script>
import {UA} from '@/services/sip-ua';
import VoipMixins from '@/components/call-center/mixins/voip';

export default {
    mixins: [
        VoipMixins,
    ],
    data() {
        return {
            ua: UA,
            name: null,
            phone: null,
        };
    },
    methods: {
        answerCallAndRedirect() {
            this.answerCall();
            this.goVoip();
        },
    },
    watch: {
        ['ua.call'](val) {
            if (val !== null) {
                this.name = null;
                this.phone = val.info.phoneNumber;
            }
        },
        ['$store.state.call'](val) {
            if (val) {
                this.name = val.versaName;
            }
        },
    },
}
</script>