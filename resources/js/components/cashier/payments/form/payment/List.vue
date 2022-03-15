<template>
    <manage-table 
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        :enablePagination="false"
        table-uid="cashier-payments-list"
        @header-filter-updated="syncFilters">
    </manage-table>
</template>
<script>
import PaymentRepository from '@/repositories/payment';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new PaymentRepository(),
            fields: [
                {
                    name: 'appointment.date',
                    title: __('Дата записи'),
                    width: '85px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'appointment.card_number',
                    title: __('Номер карты'),
                    width: '70px',
                },
                {
                    name: 'doctor.name',
                    title: __('Врач'),
                    width: '185px',
                },
                {
                    name: 'service.name',
                    title: __('Услуга'),
                },
                {
                    name: 'cashbox.name',
                    title: __('Форма оплаты'),
                    width: '104px',
                },
                {
                    name: 'discount',
                    title: __('Скидка банк карты, %'),
                    width: '75px',
                },
                {
                    name: 'amount',
                    title: __('Сумма, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    width: '200px',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            scopes: [
                'appointment',
                'patient',
                'cashbox',
                'payment_destination',
                'doctor',
                'cashier',
                'service',
            ]
        }  
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
    }
}
</script>