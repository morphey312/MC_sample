<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded">
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import Employee from '@/models/employee';

export default {
    props: {
        employee: Object,
    },
    data() {
        return {
            model: new Employee({id: this.employee.id}),
            repository: new ProxyRepository(() => {
                return this.getServiceTypes();
            }),
            fields: [
                {
                    name: 'speciality_name',
                    title: __('Специализация'),
                },
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    width: '15%',
                },
                {
                    name: 'providing_condition',
                    title: __('Условия предоставления'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('service_providing_conditions', value);
                    },
                },
                {
                    name: 'start_date',
                    title: __('Дата начала'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
                {
                    name: 'end_date',
                    title: __('Дата окончания'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
                {
                    name: 'is_deleted',
                    title: __('Активно'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolFormat(!value);
                    },
                },
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
        getServiceTypes() {
            return this.model.fetch(['service_types']).then(() => {
                return {
                    rows: this.model.employee_service_types,
                };
            });
        },
    }
}
</script>