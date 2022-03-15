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
                return this.getQualifications();
            }),
            fields: [
                {
                    name: 'institution_name',
                    title: __('Название учреждения'),
                    width: '30%',
                },
                {
                    name: 'type',
                    title: __('Тип'),
                    width: '25%',
                    formatter: (value) => {
                        return this.$handbook.getOption('qualification_type', value);
                    },
                },
                {
                    name: 'speciality',
                    title: __('Специальность'),
                    width: '25%',
                },
                {
                    name: 'issued_date',
                    title: __('Дата выдачи'),
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    width: '20%',
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
        getQualifications() {
            return this.model.fetch(['qualifications']).then(() => {
                return {
                    rows: this.model.employee_qualifications,
                };
            });
        },
    }
}
</script>