<template>
    <manage-table
        v-if="loadList"
        ref="table"
        :fields="fields"
        :repository="repository"
        :filters="filters"
        :scopes="scopes">
        <template
            slot="name_link"
            slot-scope="props">
            <a @click.prevent="selected(props.rowData)">
                {{ props.rowData.full_name }}    
            </a>
        </template>
    </manage-table>
</template>

<script>
import EmployeeRepository from '@/repositories/employee';

export default {
    props: {
        filters: Object,
        scopes: {
            type: Array,
            default: null,
        },
        loadList: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            repository: new EmployeeRepository(),
            fields: [
                {
                    name: 'name_link',
                    title: __('ФИО'),
                },
                {
                    name: 'clinic_names',
                    title: __('Клиника'),
                    width: '18%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    scopes: ['clinics.clinic'],
                },
                {
                    name: 'position_names',
                    title: __('Должность'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    scopes: ['clinics.position'],
                },
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    scopes: ['clinics.specializations'],
                },
                {
                    name: 'phone',
                    title: __('Телефон'),
                    width: '12%',
                },
            ],
        };
    },
    methods: {
        selected(item) {
            this.$emit('selected', item);
        },
    },
}
</script>