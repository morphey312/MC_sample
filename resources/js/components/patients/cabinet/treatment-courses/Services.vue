<template>
    <manage-table 
        ref="table"
        :fields="fields"
        :repository="repository"
        :enable-pagination="false"
        table-height="300px">
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        rows: Array,
        showDiscounts: Boolean,
        isDoctorSpecialization: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            repository: new ProxyRepository(() => Promise.resolve({rows: this.rows})),
            fields: [
                {
                    name: 'appointment.date',
                    title: __('Дата'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'service.name',
                    title: __('Название услуги'),
                    width: this.showDiscounts ? '50%' : '63%',
                },
                {
                    name: 'service.is_base',
                    title: __('Базовая услуга'),
                    width: '12%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                ...(this.showDiscounts ? [{
                    name: 'service.discount',
                    title: __('Скидка, %'),
                    width: '13%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value, 0);
                    },
                }] : []),
                ...(this.isDoctorSpecialization ? [{
                    name: 'service.cost',
                    title: __('Стоимость, грн.'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                }] : []),
            ],
        };
    },
};
</script>