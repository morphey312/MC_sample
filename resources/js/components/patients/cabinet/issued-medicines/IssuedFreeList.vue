<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :flex-height="true"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot="to_issue"
            slot-scope="props">
            {{ getToIssue(props.rowData) }}
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
            <div
                class="text-right"
                style="padding-right: 5px;">
                <p class="mt-10 mb-0">
                    <b>{{ __('Выдано препаратов на сумму:') }} {{ $formatter.numberFormat(issuedCost) }}  {{ __('грн') }}</b>
                </p>
                <p class="mt-0 mb-0">
                    <b>{{ __('Количество выданных:') }} {{ issuedQuantity }} {{ __('шт.') }}</b>
                </p>
                <p><b>{{ __('Осталось выдать:') }} {{ toIssueQuantity }} {{ __('шт.') }}</b></p>
            </div>
        </template>
    </manage-table>
</template>
<script>
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import IssueListMixin from './mixins/issue-list';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        IssueListMixin,
    ],
    data() {
        return {
            fields: [
                {
                    name: 'created',
                    title: __('Дата выдачи медикамента'),
                    width: '90px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'created',
                },
                {
                    name: 'medicine.card_number',
                    title: __('Номер карты'),
                    width: '70px',
                    filter: true,
                    filterField: 'patient_card_number',
                },
                {
                    name: 'medicine.card_specialization_name',
                    title: __('Специализация карты'),
                    width: '100px',
                    filter: true,
                    filterField: 'specialization_card_name',
                },
                {
                    name: 'medicine.name',
                    title: __('Название медикаментов'),
                    filter: true,
                    filterField: 'medicine_name',
                },
                {
                    name: 'issuer_name',
                    title: __('Оператор'),
                    width: '100px',
                },
                {
                    name: 'medicine.assigner.full_name',
                    title: __('Назначил врач'),
                    width: '140px',
                },
                {
                    name: 'medicine.self_cost',
                    title: __('Стоимость, грн за шт'),
                    width: '80px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'medicine.quantity',
                    title: __('Назначено, шт'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'issued',
                    title: __('Выдано, шт'),
                    width: '60px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'to_issue',
                    title: __('Осталось выдать, шт'),
                    width: '70px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
            ],
            filters: {
                patient: this.patient.id,
                is_free: true,
            },
        }
    },
    methods: {
        getIssuedCost(rows) {
            return rows.reduce((sum, row) => {
                return sum += Number(row.issued) * Number(row.medicine.self_cost);
            }, 0);
        },
        getToIssueFilters() {
            return {
                patient: [this.patient.id],
                clinic: this.$store.state.user.clinics,
                is_free: true,
                should_issue: true,
                medicine_type: CONSTANTS.ASSIGNED_MEDICINE.TYPES.MEDICINE,
            };
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
    }
}
</script>
