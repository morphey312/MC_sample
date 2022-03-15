<template>
    <div>
        <div class="mb-20">
            <h3 class="bg-red">{{ __('Назначенные') }}</h3>
            <manage-table 
                ref="table"
                :fields="fields"
                :repository="repository"
                :enable-pagination="false"
                table-height="100px">
                <div 
                    class="totals"
                    slot="footer-bottom">
                    {{ __('Итого себестоимость:') }} {{ $formatter.numberFormat(totalSelfCost) }} {{ __('грн.') }}
                </div>
            </manage-table>
        </div>
        <div>
            <h3 class="bg-blue">{{ __('Выданные') }}</h3>
             <manage-table 
                ref="issuedTable"
                :fields="issuedFields"
                :repository="issuedRepository"
                :enable-pagination="false"
                table-height="100px">
                <div 
                    class="totals"
                    slot="footer-bottom">
                    {{ __('Итого себестоимость:') }} {{ $formatter.numberFormat(totalIssuedSelfCost) }} {{ __('грн.') }}
                </div>
            </manage-table>
        </div>
    </div>
</template>


<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        rows: Array,
        issued: Array,
    },
    computed: {
        totalSelfCost() {
            return this.rows.reduce((sum, val) => {
                return sum + (val.medicine[this.medicineCostField] ? (val.medicine.quantity * val.medicine[this.medicineCostField]) : 0);
            }, 0);
        },
        totalIssuedSelfCost() {
            return this.rows.reduce((sum, val) => {
                return sum + (val.medicine[this.medicineCostField] ? (val.medicine.issued_quantity * val.medicine[this.medicineCostField]) : 0);
            }, 0);
        }
    },
    data() {
        return {
            medicineCostField: this.$store.state.user.isDoctor ? 'base_cost' : 'self_cost',
            repository: new ProxyRepository(() => Promise.resolve({rows: this.rows})),
            issuedRepository: new ProxyRepository(() => Promise.resolve({rows: this.issued})),
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
                    name: 'medicine.name',
                    title: __('Название препарата'),
                },
                {
                    name: 'medicine.quantity',
                    title: __('Количество'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                ...(
                    this.$store.state.user.isDoctor ? [{
                        name: 'medicine.base_cost',
                        title: __('Себестоимость, грн.'),
                        width: '15%',
                        titleClass: 'text-right',
                        dataClass: 'text-right',
                        formatter: (value) => {
                            return this.$formatter.numberFormat(value);
                        },
                    }] : [{
                        name: 'medicine.self_cost',
                        title: __('Себестоимость, грн.'),
                        width: '15%',
                        titleClass: 'text-right',
                        dataClass: 'text-right',
                        formatter: (value) => {
                            return this.$formatter.numberFormat(value);
                        },
                    }, {
                        name: 'medicine.base_cost',
                        title: __('Стоимость, грн.'),
                        width: '15%',
                        titleClass: 'text-right',
                        dataClass: 'text-right',
                        formatter: (value) => {
                            return this.$formatter.numberFormat(value);
                        },
                    }]
                )
            ],
            issuedFields: [
                {
                    name: 'appointment.date',
                    title: __('Дата'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'medicine.name',
                    title: __('Название препарата'),
                },
                {
                    name: 'medicine.issued_quantity',
                    title: __('Количество'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                ...(
                    this.$store.state.user.isDoctor ? [{
                        name: 'medicine.base_cost',
                        title: __('Себестоимость, грн.'),
                        width: '15%',
                        titleClass: 'text-right',
                        dataClass: 'text-right',
                        formatter: (value) => {
                            return this.$formatter.numberFormat(value);
                        },
                    }] : [{
                        name: 'medicine.self_cost',
                        title: __('Себестоимость, грн.'),
                        width: '15%',
                        titleClass: 'text-right',
                        dataClass: 'text-right',
                        formatter: (value) => {
                            return this.$formatter.numberFormat(value);
                        },
                    }, {
                        name: 'medicine.base_cost',
                        title: __('Стоимость, грн.'),
                        width: '15%',
                        titleClass: 'text-right',
                        dataClass: 'text-right',
                        formatter: (value) => {
                            return this.$formatter.numberFormat(value);
                        },
                    }]
                )
            ]
        };
    },
};
</script>