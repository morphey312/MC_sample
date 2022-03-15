<template>
    <page
        :title="__('Приход')"
        type="flex">
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
                <payment-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <payment-list
                ref="table"
                :filters="filters"
                @loaded="loaded"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <div class="text-right">
                        <b>{{ __('Итого сумма:') }} {{ $formatter.numberFormat(periodDifference) }} {{ __('грн') }}</b>
                    </div>
                </div>
            </payment-list>
         </section>
    </page>
</template>
<script>
import PaymentFilter from './doctor/Filter.vue';
import PaymentList from './doctor/List.vue';
import ManageMixin from '@/mixins/manage';
import PaymentRepository from '@/repositories/payment';

export default {
    mixins: [
        ManageMixin
    ],
    components: {
        PaymentFilter,
        PaymentList,
    },
    data() {
        return {
            displayFilter: true,
            paymentMethods: [],
            repo: new PaymentRepository(),
            periodDifference: 0,
            reportRepository: new PaymentRepository(),
            loading: false,
        }
    },
    methods: {
        loaded() {
            this.getTotal();
            this.refreshed();
        },
        getTotal() {
            this.repo.getTotal(this.filters).then(response => {
                if (response && response.total != undefined) {
                    this.periodDifference = (response.total);
                } else {
                    this.periodDifference = 0;
                }
            })
        },
        getData() {
            let table = this.getManageTable();
            return table ? table.getData() : [];
        },
        getDefaultFilters() {
            let today = this.$moment().format('YYYY-MM-DD');
            return {
                clinic: this.$store.state.user.clinics,
                doctor: [this.$store.state.user.employee_id],
                createdStart: today,
                createdEnd: today,
            }
        },
    }
}
</script>