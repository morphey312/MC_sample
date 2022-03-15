<template>
    <div class="sections-wrapper">
        <drawer :open="displayFilter">
            <section class="grey pb-0 pt-10">
                <payment-filter 
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters"  />
            </section>
        </drawer>
        <section 
            class="grey-cap flex-content pb-0"
            :style="{'height': listHeight}">
                <payment-list
                    ref="table"
                    :filters="filters" />
        </section>
        <section class="pt-0">
            <div class="form-footer text-right">
                <el-button
                    type="primary"
                    @click="cancel">
                    {{ __('Закрыть') }}
                </el-button>
            </div>
        </section>
    </div>
</template>
<script>
import PaymentFilter from './payment/Filter.vue';
import PaymentList from './payment/List.vue';
import ManageMixin from '@/mixins/manage';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        PaymentFilter,
        PaymentList,
    },
    props: {
        patient_id: Number,
    },
    data() {
        return {
            displayFilter: true,
        }
    },
    computed: {
        listHeight() {
            return this.displayFilter ? '360px' : '460px';
        },
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        getFilterUid() {
            return false;
        },
        toggleFilter(val) {
            this.displayFilter = val;
        },
        getDefaultFilters() {
            return {
                patient: this.patient_id,
                payment_type: CONSTANTS.PAYMENT.TYPES.INCOME,
            }
        },
    },
}
</script>