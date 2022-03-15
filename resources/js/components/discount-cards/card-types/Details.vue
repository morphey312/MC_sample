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
        cardType: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.cardType.payment_destinations,
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Назначение платежа'),
                    width: '25%',
                },
                {
                    name: 'discount_percent',
                    title: __('Размер скидки'),
                    width: '25%',
                },
                {
                    name: 'date_start',
                    title: __('Действует с'),
                    width: '25%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'date_end',
                    title: __('Действует до'),
                    width: '25%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
            ],
        };
    },
}
</script>