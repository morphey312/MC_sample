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
                    rows: this.getAnalysis(),
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
                    name: 'date_pass',
                    title: __('Дата сдачи'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    width: '10%',
                },
                {
                    name: 'analysis.laboratory_code',
                    title: __('Код лаборатории'),
                    width: '10%',
                },
                {
                    name: 'analysis.clinic.code',
                    title: __('Код клиники'),
                    width: '10%',
                },
                {
                    name: 'analysis.name',
                    title: __('Название анализов'),
                    width: '20%',
                },
                {
                    name: 'analysis.clinic.duration_days',
                    title: __('Кол-во дней на анализ'),
                    width: '10%',
                },
                {
                    name: 'quantity',
                    title: __('Кол-во'),
                    width: '10%',
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн'),
                    width: '10%',
                },
                {
                    name: 'discount',
                    title: __('Скидка'),
                    width: '10%',
                },
                ...(this.showExtraFields ? [
                    {
                        name: 'by_policy',
                        title: __('Гарантия полиса'),
                        width: '10%',
                        formatter: (value) => {
                            return this.$formatter.boolToString(value, '<span class="check-yes" />');
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
        getAnalysis() {
            let services = [];
            this.appointment.analyses_results.map(service => {
                if (service.id) {
                    services.push(service)
                }
            })

            return services
        },
        getSelection() {
            return this.getVuetable().selectedTo;
        },
        getVuetable() {
            return this.$refs.table.$refs.vuetable;
        },
        getSelectedRows() {
            let selection = this.getSelection();
            return this.getAnalysis().filter((row) => selection.indexOf(row.id) !== -1);
        },
        selectionChanged(selection) {
            console.log(this.getSelectedRows())
            this.$emit('selection-changed', this.getSelectedRows().map(row => {return row.service_id}));
        },
    }
}
</script>
