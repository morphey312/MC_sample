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
import Clinic from '@/models/clinic';

export default {
    props: {
        clinic: Object,
    },
    data() {
        return {
            model: new Clinic({id: this.clinic.id}),
            repository: new ProxyRepository(() => {
                return this.getServiceTypes();
            }),
            fields: [
                {
                    name: 'speciality_type_name',
                    title: __('Специализация'),
                },
                {
                    name: 'providing_condition',
                    title: __('Условия предоставления'),
                    width: '40%',
                    formatter: (value) => {
                        return this.$handbook.getOption('service_providing_conditions', value);
                    },
                },
                {
                    name: 'is_active',
                    title: __('Статус'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$handbook.getOption('active_status', value);
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
                    rows: this.model.service_types,
                };
            });
        },
    }
}
</script>