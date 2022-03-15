<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        table-height="auto"
        @loaded="loaded">
        <template
            slot="featured"
            slot-scope="props" >
            <featured
                :model="props.rowData"
                :featured-list="featuredList"
                @featured-changed="featuredChanged" />
        </template>
        <template
            slot="add-selection"
            slot-scope="props" >
            <div class="has-icon">
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Добавить анализ') }}</a>
            </div>
        </template>
    </manage-table>
</template>

<script>
import AnalysisRepository from '@/repositories/analysis';
import ProxyRepository from '@/repositories/proxy-repository';
import Featured from './Featured.vue';
import CreateResultMixin from '@/mixins/appointment/analysis/create-result';

export default {
    mixins: [
        CreateResultMixin,
    ],
    components: {
        Featured,
    },
    props: {
        filters: Object,
        featuredList: Array,
        insurancePolicy: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getAnalysis();
            }),
            fields: [
                {
                    name: 'featured',
                    title: '',
                    width: "30px",
                    dataClass: 'text-center',
                },
                {
                    name: 'analysis.laboratory_code',
                    title: __('Код лаборатории'),
                    width: "120px",
                },
                {
                    name: 'analysis.laboratory_name',
                    title: __('Название лаборатории'),
                    width: "150px",
                },
                {
                    name: 'analysis.clinic.code',
                    title: __('Код клиники'),
                    width: "90px",
                },
                {
                    name: 'analysis.name',
                    title: __('Название анализов'),
                },
                {
                    name: 'analysis.price',
                    title: __('Стоимость, грн'),
                    width: "100px",
                },
                {
                    name: 'add-selection',
                    title: '',
                    width: "130px",
                    dataClass: 'text-right',
                },
            ],
        };
    },
    methods: {
        getAnalysis() {
            let repo = new AnalysisRepository();
            let params = { sort: [{field: 'laboratory_clinic_priority', direction : 'asc'}]};
            if (this.insurancePolicy) {
                params.withInsurer = this.insurancePolicy.insurance_company_id;
            }
            return repo.fetchListForAppointment(this.filters, params).then((response) => {
                return Promise.resolve({
                    rows: response.map((row) => this.createResultModel(row, this.filters))
                });
            });
        },
        toggleSelection(row) {
            this.$emit('selection-changed', row);
        },
        loaded() {
            this.$emit('loaded');
        },
        featuredChanged(item) {
            this.$emit('featured-changed', item);
        },
    }
}
</script>
