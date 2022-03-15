<template>
    <div>
        <manage-table
            ref="table"
            :fields="fields"
            :repository="repository"
            table-height="auto"
            :enable-pagination="false"
            @selection-changed="selectionChanged" />
    </div>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        showExtraFields: Boolean,
        appointment: Object
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.getServices(),
                });
            }),
            fields: [
                {
                    name: '__checkbox',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    width: '5%',
                },
                {
                    name: 'name',
                    title: __('Выбранные услуги'),
                    width: '25%',
                    editable: false,
                },
                {
                    name: 'specialization.name',
                    title: __('Спец-ция'),
                    width: '15%',
                    editable: false,
                },
                {
                    name: 'cost',
                    title: __('Ст-ть, грн'),
                    width: '10%',
                },
                {
                    name: 'quantity',
                    title: __('Кол-во'),
                    width: '10%',
                },
                {
                    name: 'discount',
                    title: __('Скидка, %'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'is_base',
                    title: __('Базовая'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'payed',
                    title: __('Опл-но, грн'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                ...(this.showExtraFields ? [
                    {
                        name: 'by_policy',
                        title: __('Гарантия полиса'),
                        width: '10%',
                        formatter: (value) => {
                            return this.$formatter.boolToString(value);
                        },
                    },
                    {
                        name: 'franchise',
                        title: __('Франшиза, %'),
                        width: '10%',
                        titleClass: 'text-right',
                        dataClass: 'text-right',
                    },
                    {
                        name: 'warranter',
                        title: __('Гарант'),
                        width: '10%',
                        titleClass: 'text-right',
                        dataClass: 'text-right',
                    },
                ] : [])
            ],
        }
    },
    methods: {
        getServices() {
            let services = [];
            this.appointment.regular_services.map(service => {
                if (service.id) {
                    services.push(service)
                }
            })

            return services
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
    }
}
</script>
