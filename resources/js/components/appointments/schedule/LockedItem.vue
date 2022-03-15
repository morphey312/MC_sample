<template>
    <div class="draggable-cell-info"
        :style="styles"
        @mouseover="hoverIn"
        @mouseleave="hoverOut"
        @contextmenu="showContextMenu"
        v-popover:popover >
        <p class="info-time">
            {{ lock.start }} — {{ lock.end }}. {{ lock.reason ? lock.reason.name : '' }} — {{ lock.comment }}
        </p>
        <template v-if="hasAppointment">
            <p>{{ baseService }}</p>
            <p>{{ cardNumber }} - {{ patientName }}</p>
        </template>
        <el-popover
            ref="popover"
            placement="right-start"
            width="200"
            v-model="contextMenuVisible"
            popper-class="schedule-popover"
            trigger="manual" >
            <slot>
                <context-menu
                    ref="contextMenu"
                    :visible="contextMenuVisible"
                    :lock="lock"
                    @unlock="unlock"
                    @watchblock="watchblock"
                    @close="closePopover"/>
            </slot>
        </el-popover>
    </div>
</template>

<script>
import ContextMenu from './parts/BlockedContextMenu.vue';
import { on, off } from 'element-ui/src/utils/dom';

export default {
    components: {
        ContextMenu,
    },
    props: {
        styles: {
            type: Object,
            default: () => ({})
        },
        lock: {
            type: Object,
            default: () => ({})
        },
    },
    data() {
        return {
            contextMenuVisible: false,
            isHovered: false,
        }
    },
    created() {
        this.documentClickHandler = (e) => {
            if (this.$refs.popover) {
                this.$refs.popover.handleDocumentClick(e);
            }
        };
    },
    mounted() {
        on(document, 'click', this.documentClickHandler);
        on(document, 'contextmenu', this.documentClickHandler);
    },
    beforeDestroy() {
        off(document, 'click', this.documentClickHandler);
        off(document, 'contextmenu', this.documentClickHandler);
    },
    computed: {
        hasAppointment() {
            return _.isFilled(this.lock.appointment);
        },
        patientName() {
            return this.hasAppointment ? this.lock.appointment.patient_name : '';
        },
        cardNumber() {
            return this.hasAppointment ? this.lock.appointment.card_number : '';
        },
        baseService() {
            if (!this.hasAppointment || !this.lock.appointment.services) {
                return '';
            }
            let base = this.lock.appointment.services.find(s => s.is_base === true);
            return base ? base.name : '';
        },
    },
    methods: {
        closePopover() {
            this.contextMenuVisible = false;
        },
        showContextMenu(e) {
            e.preventDefault();
            if (this.$refs.popover) {
                this.$refs.popover.doShow();
            }
        },
        unlock() {
            this.$emit('unlock')
        },
        watchblock() {
            this.$emit('watchblock')
        },
        hoverIn() {
            this.isHovered = true;
            this.$emit('hover-in', {height: this.$el.clientHeight});
        },
        hoverOut() {
            this.isHovered = false;
            this.$emit('hover-out');
        }
    }
}
</script>
