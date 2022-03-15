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
                return this.getDocuments();
            }),
            fields: [
                {
                    name: 'type',
                    title: __('Тип'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$handbook.getOption('person_document', value);
                    },
                },
                {
                    name: 'number',
                    width: '20%',
                    title: __('Номер'),
                },
                {
                    name: 'issued_at',
                    title: __('Дата выдачи'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'issued_by',
                    width: '30%',
                    title: __('Кем выдан'),
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
        getDocuments() {
            return this.model.fetch(['documents']).then(() => {
                return {
                    rows: this.model.employee_documents,
                };
            });
        },
    }
}
</script>