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
                return this.getDegrees();
            }),
            fields: [
                {
                    name: 'degree',
                    title: __('Степень'),
                    formatter: (value) => {
                        return this.$handbook.getOption('science_degree', value);
                    },
                    width: '20%',
                },
                {
                    name: 'speciality',
                    title: __('Специальность'),
                    width: '40%',
                },
                {
                    name: 'institution_name',
                    title: __('Учреждение'),
                    width: '40%',
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
        getDegrees() {
            return this.model.fetch(['science_degrees']).then(() => {
                return {
                    rows: this.model.employee_science_degrees,
                };
            });
        },
    }
}
</script>