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
                return this.getSpecialities();
            }),
            fields: [
                {
                    name: 'speciality_name',
                    title: __('Специализация'),
                    width: '40%',
                },
                {
                    name: 'primary',
                    title: __('Основная'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'level',
                    title: __('Уровень'),
                    formatter: (value) => {
                        return this.$handbook.getOption('speciality_level', value);
                    },
                    width: '20%',
                },
                {
                    name: 'attestation_name',
                    title: __('Учреждение, проводившее аттестацию'),
                    width: '30%',
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
        getSpecialities() {
            return this.model.fetch(['specialities']).then(() => {
                return {
                    rows: this.model.employee_specialities,
                };
            });
        },
    }
}
</script>