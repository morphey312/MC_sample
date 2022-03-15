<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :flex-height="true"
        :scopes="scopes"
        :enable-pagination="false"
        @selection-changed="selectionChanged"
        @loaded="loaded">
        <template slot="record" slot-scope="props">
            {{ props.rowData.appointment.clinic_name + ' ' + props.rowData.appointment.doctor.name}}
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import ManageMixin from '@/mixins/manage';
import ServiceRepository from "@/repositories/appointment/service";

export default {
    mixins: [
        ManageMixin,
    ],
    props: {
        appointmentIds: null
    },
    data() {
        return {
            repository: new ServiceRepository(),
            fields: [
                {
                    name: 'appointment.patient.full_name',
                    title: __('Пациент'),
                    dataClass: 'no-dash',
                },
                {
                    name: 'record',
                    title: __('Запись'),
                    dataClass: 'no-dash',
                    width: '25%',
                },
                {
                    name: 'name',
                    title: __('Услуга'),
                    dataClass: 'no-dash',
                    width: '20%',
                },
                {
                    name: 'cost',
                    title: __('Стоимость'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                    dataClass: 'no-dash',
                    width: '10%',
                },
                {
                    name: 'payed',
                    title: __('Оплата'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                    dataClass: 'no-dash',
                    width: '10%',
                },
            ],
            filters: { appointment_id: this.appointmentIds },
            scopes: [
                'light_appointment'
            ],
            initialSortOrder: [
                {field: 'date_time', direction: 'desc'},
            ],
        };
    },
    watch: {
        ['appointmentIds'](val) {
            this.filters = { appointment_id : val.length ? val : null };
        },
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        selectionChanged(selection) {
            this.$emit('service-selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
    }
};
</script>
