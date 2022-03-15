<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository">
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        employee: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.employee.employee_clinics,
                });
            }),
            fields: [
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    width: '25%',
                },
                {
                    name: 'position_name',
                    title: __('Должность'),
                    width: '30%',
                },
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: '30%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('employee_status', value);
                    }
                },
            ],
        };
    },
}
</script>