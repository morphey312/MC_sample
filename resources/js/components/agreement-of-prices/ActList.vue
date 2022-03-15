<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :selectable-rows="true"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import PriceAgreementAct from '@/repositories/price-agreement-act';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from "@/components/general/table/DateHeaderFilter.vue";
import AnalysisRepository from "@/repositories/analysis";

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new PriceAgreementAct({
                limitClinics: this.$isAccessLimited('price-agreement-acts')
            }),
            fields: [
                {
                    name: 'clinic_names',
                    filterField: 'clinic',
                    title: __('Клиники'),
                    width: '15%',
                    formatter: (value) => {
                        return value ? value.join(', ') : '';
                    },
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('services'),
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'created_at',
                    title: __('Дата формирования'),
                    width: '12%',
                    filter: DateHeaderFilter,
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'id',
                    sortField: 'id',
                    title: __('Номер согласования'),
                    dataClass: 'text-select',
                    width: '12%',
                    filter: true,
                },
                {
                    name: 'type',
                    title: __('Тип акта'),
                    formatter: (val) => {
                        return this.$handbook.getOption('price_agreement_act_type', val);
                    },
                    dataClass: 'text-select',
                    width: '12%',
                },
                {
                    name: 'status',
                    title: __('Статус акта'),
                    formatter: (val) => {
                        return this.$handbook.getOption('price_agreement_act_status', val);
                    },
                    dataClass: 'text-select',
                    width: '12%',
                },
                {
                    name: 'date_from',
                    title: __('Дата начала действия тарифа'),
                    filter: DateHeaderFilter,
                    width: '12%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
            ],
            initialSortOrder: [
                {field: 'id', direction: 'desc'},
            ],
            scopes: [
                'clinics',
            ],
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        getLangBySuffix(suffix) {
            return (this.$store.state.user.langBySuffix(suffix) || {}).short_name || '';
        },
    },
}
</script>
