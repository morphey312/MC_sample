<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
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
import ServiceRepository from '@/repositories/service';
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ServiceRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    sortField: 'name',
                    title: __('Услуга'),
                    width: '12%',
                    dataClass: 'text-select',
                    filter: true,
                },
                {
                    name: 'name_lc1',
                    sortField: 'name_lc1',
                    title: __('Название') + ' (' + this.getLangBySuffix('lc1') + ')',
                    width: '12%',
                    dataClass: 'text-select',
                    filter: true,
                },
                {
                    name: 'name_ua_i18n',
                    sortField: 'name_ua',
                    title: __('Название для чека'),
                    dataClass: 'text-select',
                    width: '12%',
                    filter: true,
                },
                {
                    name: 'specialization_name',
                    sortField: 'specialization',
                    filterField: 'specialization',
                    title: __('Специализация'),
                    width: '12%',
                    filter: new SpecializationRepository({
                        limitClinics: this.$isAccessLimited('services'),
                    }),
                },
                {
                    name: 'disabled',
                    sortField: 'disabled',
                    filterField: 'disabled',
                    title: __('Не использовать'),
                    width: '13%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'service_status',
                },
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
                    name: 'is_base',
                    sortField: 'is_base',
                    filterField: 'base',
                    title: __('Базовая'),
                    width: '9%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'payment_destination',
                    sortField: 'payment_destination_name',
                    title: __('Назначение платежа'),
                    width: '15%',
                    filter: new PaymentDestinationRepository({
                        limitClinics: this.$isAccessLimited('services'),
                    }),
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
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
