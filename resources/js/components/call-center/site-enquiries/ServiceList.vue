<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        class="selectable-data">
        <template
            slot-scope="props"
            slot="specialization">
            {{ (props.rowData.service && props.rowData.service.specialization) ? props.rowData.service.specialization.name : '' }}
        </template>
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
                        ...this.enquiry.service_list,
                        ...this.enquiry.analysis_list,
                        ...this.enquiry.unpaid_service_list,
                        ...this.enquiry.unpaid_analysis_list,
                    ],
                });
            }),
            fields: [
                {
                    name: 'service.name',
                    title: __('Название услуги'),
                },
                {
                    name: 'specialization',
                    title: __('Специализация'),
                    width: '15%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'discount',
                    title: __('Скидка, %'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'payed_amount',
                    title: __('Оплачено, грн'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'is_prepayment',
                    title: __('Предоплата'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
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
