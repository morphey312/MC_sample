<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :flex-height="true"
        @loaded="loaded"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
        <template slot="analysis-info" slot-scope="props">
            <analysis-info :model="props.rowData" >
                <template slot="column">
                    {{ props.rowData.patient.full_name }}
                </template>
            </analysis-info>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import LaboratoryContainerRepository from '@/repositories/analysis/laboratory/order/item/container';
import ClinicRepository from '@/repositories/clinic';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import AnalysisInfo from "./AnalysisDetails.vue";

export default {
    components: {
        AnalysisInfo,
    },
    data() {
        return {
            repository: new LaboratoryContainerRepository(),
            fields: [
                {
                    name: '__checkbox',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    width: '30px',
                },
                {
                    name: 'analysis_name',
                    title: __('Анализ'),
                    width: '200px',
                },
                {
                    name: 'clinic_name',
                    title: __('Клиника записи'),
                    width: '200px',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('laboratory-orders'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'laboratory_name',
                    title: __('Лаборатория анализа(МЦ)'),
                    width: '200px',
                    filter: new LaboratoryRepository({
                        accessLimit: this.$isAccessLimited('laboratory-orders'),
                    }),
                    filterField: 'laboratory',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'analysis-info',
                    title: __('Пациент'),
                    width: '250px',
                    filterField: 'patient_name',
                    filter: true,
                },
            ],
            initialSortOrder: [
                {field: 'appointment_date', direction: 'desc'},
            ],
            scopes: [
                'patient',
                'analysis',
            ],
            filters: {
                is_postponed: true,
                has_transfer_id: false,
                clinic: this.$store.state.user.clinics,
            }
        }
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
}
</script>
