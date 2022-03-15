<template>
    <page
        :title="__('Онлайн оплата. Возвраты')"
        type="flex"
        v-loading="loading">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey">
                <refund-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <refund-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @header-filter-updated="syncFilters"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('payments.online-refund')"
                        :disabled="notRefundable()"
                        @click="refund">
                        {{ __('Вернуть') }}
                    </el-button>
                </div>
            </refund-list>
         </section>
    </page>
</template>
<script>
import RefundFilter from './online-refunds/Filter.vue';
import RefundList from './online-refunds/List.vue';
import ManageMixin from '@/mixins/manage';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        RefundFilter,
        RefundList,
    },
    data() {
        return {
            displayFilter: true,
            loading: false,
        }
    },
    methods: {
        getDefaultFilters() {
            return {
                refund_status: [CONSTANTS.SITE_ENQUIRY.SERVICE_REFUND.STATUS_REFUND, CONSTANTS.SITE_ENQUIRY.SERVICE_REFUND.STATUS_REFUNDED],
            }
        },
        refund() {
            this.$confirm(__('Вы уверены что хотите оформить возврат?'), () => {
                this.activeItem.refund_status = CONSTANTS.SITE_ENQUIRY.SERVICE_REFUND.STATUS_REFUNDED;
                this.activeItem.save().then(() => {
                    this.refresh();
                });
            });
        },
        notRefundable() {
            return this.activeItem == null || this.activeItem.refund_status === CONSTANTS.SITE_ENQUIRY.SERVICE_REFUND.STATUS_REFUNDED;
        }
    }
}
</script>