<template>
    <div>
        <div class="mb-20">
            <h3 class="bg-red">{{ __('Назначенные') }}</h3>
            <manage-table
                ref="assignedTable"
                :fields="assignedFields"
                :repository="assignedRepository"
                :enable-pagination="false"
                table-height="100px">
                <div
                    class="totals"
                    slot="footer-bottom">
                    {{ __('Итого назначено:') }} {{ totalAssignedCount }}
                    /
                    {{ __('Итого стоимость:') }} {{ $formatter.numberFormat(totalAssignedCost) }} {{ __('грн.') }}
                    /
                    {{ __('Итого себестоимость:') }} {{ $formatter.numberFormat(totalAssignedSelfCost) }} {{ __('грн.') }}
                </div>
            </manage-table>
         </div>
        <div>
            <h3 class="bg-blue">{{ __('Выполненные') }}</h3>
            <manage-table
                ref="table"
                :fields="fields"
                :repository="repository"
                :enable-pagination="false"
                table-height="100px">
                <div
                    class="totals"
                    slot="footer-bottom">
                    {{ __('Итого выполнено:') }} {{ totalCount }}
                    /
                    {{ __('Итого стоимость:') }} {{ $formatter.numberFormat(totalCost) }} {{ __('грн.') }}
                    /
                    {{ __('Итого себестоимость:') }} {{ $formatter.numberFormat(totalSelfCost) }} {{ __('грн.') }}
                </div>
            </manage-table>
        </div>
    </div>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import TotalsMixin from './mixin/totals';

export default {
    mixins: [
        TotalsMixin,
    ],
    props: {
        rows: Array,
        assigned: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => Promise.resolve({rows: this.rows})),
            assignedRepository: new ProxyRepository(() => Promise.resolve({rows: this.assigned})),
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
                    title: __('Название физиотерапии'),
                    width: '48%',
                },
                {
                    name: 'service.quantity',
                    title: __('Количество'),
                    width: '12%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'service.cost',
                    title: __('Стоимость, грн.'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'service.self_cost',
                    title: __('Себестоимость, грн.'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
            ],
            assignedFields: [
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
                    title: __('Название физиотерапии'),
                },
                {
                    name: 'service.assigned_quantity',
                    title: __('Количество'),
                    width: '12%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'service.cost',
                    title: __('Стоимость, грн.'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'service.self_cost',
                    title: __('Себестоимость, грн.'),
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
};
</script>
