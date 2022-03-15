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
import CheckboxCredentialsRepository from "@/repositories/employee/checkbox-credentials";
import ProxyRepository from '@/repositories/proxy-repository';
import Employee from '@/models/employee';

export default {
    props: {
        employee: Object,
    },
    data() {
        return {
            model: new Employee({id: this.employee.id}),
            repository: new CheckboxCredentialsRepository({
                filters: {
                    employee: this.employee.id,
                },
                scopes: ['money_reciever_cashbox']
            }),
            fields: [
                {
                    name: 'cashbox_name',
                    title: __('Название кассы'),
                },
                {
                    name: 'money_reciever_name',
                    title: __('Получатель денег'),
                },
            ],
        }
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
    }
}
</script>
