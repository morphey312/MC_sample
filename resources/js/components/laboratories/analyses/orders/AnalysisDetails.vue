<template>
    <div class="has-icon">
        <span class="ellipsis">
            <slot name="column"></slot>
        </span>
        <el-tooltip
            v-if="hasWarning"
            placement="bottom"
            effect="light"
            :open-delay="500"
            popper-class="light-popover-content patient-warning pl-0 pr-0">
            <template slot="content">
                <template v-if="notPayed">
                    <div class="pl-10 pr-10">
                        <b :style="{color: colorForNotPayed}">{{ __('Анализ не оплачен') }}</b>
                    </div>
                </template>
                <template v-if="byPolicy">
                    <div class="pl-10 pr-10">
                        <b :style="{color: colorForByPolicy}">{{ __('Страховой анализ') }}</b>
                    </div>
                </template>
                <template v-if="legal_entity">
                    <div class="pl-10 pr-10">
                        <b :style="{color: colorForLegalEntity}">{{ __('Корпоративный клиент') }}</b>
                    </div>
                </template>
            </template>
            <svg-icon
                name="info-alt"
                class="icon-tiny icon-red" />
        </el-tooltip>
    </div>
</template>

<script>
import CONSTANTS from '@/constants';

export default {
    props: {
        model: Object,
    },
    computed: {
        colorForNotPayed() {
            return CONSTANTS.COLORS.ANALYSIS_CONTAINERS_WARNING.NOT_PAYED
        },
        colorForByPolicy() {
            return CONSTANTS.COLORS.ANALYSIS_CONTAINERS_WARNING.BY_POLICY
        },
        colorForLegalEntity() {
            return CONSTANTS.COLORS.ANALYSIS_CONTAINERS_WARNING.LEGAL_ENTITY
        },
        notPayed() {
            return this.model.payed === 0;
        },
        byPolicy() {
            if (this.model.results) {
                return !_.isNull(_.head(this.model.results).by_policy);
            }
        },
        legal_entity() {
            if (this.model.results) {
                return !_.isNull(_.head(this.model.results).legal_entity);
            }
        },
        hasWarning() {
            return this.notPayed || this.byPolicy || this.legal_entity;
        },
    }
}
</script>
