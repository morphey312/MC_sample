<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :selectable-rows="true"
        :flex-height="true"
        @header-filter-updated="syncFilters"
        @selection-changed="selectionChanged"
        @loaded="loaded">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    props: {
        appointments: [Object, Array],
        selectedPatientId: null
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                if (this.selectedPatientId) {
                    return Promise.resolve({
                        rows: this.appointments[this.selectedPatientId]
                            ? this.sortList(this.appointments[this.selectedPatientId])
                            : [],
                    });
                }

                let out = [];

                Object.keys(this.appointments).forEach(key => out = [...out, ...this.appointments[key]])

                return Promise.resolve({
                    rows: out,
                });
            }),
            fields: [
                {
                    name: 'patient_name',
                    title: __('Пациент'),
                    dataClass: 'no-dash',
                },
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    dataClass: 'no-dash',
                    width: '15%',
                },
                {
                    name: 'doctor_name',
                    title: __('Врач'),
                    dataClass: 'no-dash',

                    width: '15%',
                },
                {
                    name: 'specialization_name',
                    title: __('Специализация'),
                    dataClass: 'no-dash',
                    width: '15%',
                },
                {
                    name: 'date',
                    title: __('Дата записи'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    dataClass: 'no-dash',
                    width: '10%',
                },
                {
                    name: 'from',
                    title: __('С'),
                    dataClass: 'no-dash',
                    width: '10%',
                },
                {
                    name: 'end',
                    title: __('По'),
                    dataClass: 'no-dash',
                    width: '10%',
                },
            ],
            initialSortOrder: [
                {field: 'date_time', direction: 'desc'},
            ],
        };
    },
    watch: {
        ['selectedPatientId'](val) {
            this.repository.setFilters({patient_id: val});
        },
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        selectionChanged(selection) {
            this.$emit('appointment-selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        addRowClass(item, index) {
            return item.call_delete_reason_id ? 'deleted-row' : '';
        },
        sortList(appointments) {
            return _.orderBy(appointments, ['date','from'], ['desc','asc'])
        }
    }
};
</script>
