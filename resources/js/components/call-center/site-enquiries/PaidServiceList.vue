<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        class="selectable-data">
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        enquiry: {
            type: Object,
            default: () => ({}),
        }
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: [
                        ...this.enquiry.used_service_list,
                        // ...this.enquiry.used_analysis_list, // to be implemented later
                    ].map((row) => {
                        let service = _.find(this.enquiry.appointment.services, (s) => {
                            return s.service_id == row.service_id;
                        });
                        return {
                            service: row.service,
                            cost: service ? service.cost : '',
                            debt: service ? Math.max(0, service.cost - service.paid) : '',
                            payed_amount: row.payed_amount,
                        };
                    }),
                });
            }),
            fields: [
                {
                    name: 'service.name',
                    title: __('Название услуги'),
                },
                {
                    name: 'service.specialization.name',
                    title: __('Специализация'),
                    width: '20%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'debt',
                    title: __('Долг, грн'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'payed_amount',
                    title: __('Оплачено, грн'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
            ],
        };
    },
    mounted() {
        this.$watch('enquiry.services', () => {
            this.$refs.table.refresh();
        }, { deep: true });
    },
};
</script>
