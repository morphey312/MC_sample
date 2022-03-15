<template>
    <div>
        <manage-table
            ref="table"
            :fields="fields"
            :repository="repository"
            :selectable-rows="true"
            @selection-changed="selectionChanged"
            @loaded="loaded">
        </manage-table>
        <div class="mt-10">
            {{ __('Итого клиник') }}: {{ totalClinics }}
        </div>
    </div>
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
                return this.getClinics();
            }),
            totalClinics: 0,
            fields: [
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                },
                {
                    name: 'is_primary',
                    title: __('Основная клиника'),
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'position_name',
                    title: __('Должность'),
                },
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    }
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    formatter: (value) => {
                        return this.$handbook.getOption('employee_status', value);
                    }
                },
            ],
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.totalClinics = this.model.employee_clinics.length
            this.$emit('loaded');
        },
        getClinics() {
            return this.model.fetch([
                'clinics.clinic',
                'clinics.position',
                'clinics.specializations',
            ]).then(() => {
                return {
                    rows: this.model.employee_clinics,
                };
            });
        },
    },
}
</script>
