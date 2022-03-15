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
                return this.getEducations();
            }),
            fields: [
                {
                    name: 'institution_name',
                    title: __('Учебное заведение'),
                    width: '40%',
                },
                {
                    name: 'speciality',
                    title: __('Специальность'),
                    width: '20%',
                },
                {
                    name: 'degree',
                    title: __('Степень'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$handbook.getOption('education_degree', value);
                    },
                },
                {
                    name: 'issued_date',
                    title: __('Дата выпуска'),
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
        getEducations() {
            return this.model.fetch(['educations']).then(() => {
                return {
                    rows: this.model.employee_educations,
                };
            });
        },
    }
}
</script>