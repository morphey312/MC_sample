<template>
    <div class="context-menu-wrapper">
        <div class="popover-close-btn" @click="close">
            <div>
                <i class="el-icon-close"></i>
            </div>
        </div>
        <template v-if="blockedTimeActions.length !== 0">
            <div
                v-for="(item, index) in blockedTimeActions"
                :key="`bt-${index}`"
                @click="emitEvent(item.action)">
                {{ item.title }}
            </div>
        </template>
    </div>
</template>

<script>

export default {
    props: {
        lock: Object,
        visible: {
            type: Boolean,
            default: false,
        },
        readonly: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            blockedTimeActions: this.getBlockedTimeActions(),
        }
    },
    methods: {
        close() {
            this.$emit('close');
        },
        emitEvent(name) {
            this.$emit(name, {visible: false});
        },
        getBlockedTimeActions() {
            return [
                {
                    action: 'unlock',
                    title: __('Разблокировать'),
                    visible: !this.readonly && this.$canManage('appointments.doctor-block'),
                },
                {
                    action: 'watchblock',
                    title: __('Посмотреть блок'),
                    visible: !this.readonly,
                },
            ].filter((item) => item.visible !== false);
        },
    },
    watch: {
        visible(v) {
            if (v) {
                this.blockedTimeActions = this.getBlockedTimeActions();
            }
        },
    },
}
</script>
