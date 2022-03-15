<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :flex-height="true"
        :row-class="onRowClass"
        @loaded="loaded"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
        <template
            slot-scope="props"
            slot="attachment_data">
            <attachments :model="props.rowData" />
        </template>
        <template
            slot-scope="props"
            slot="logbtn">
            <svg-icon
                name="info-alt"
                class="icon-tiny icon-grey"
                :title="__('Операции')"
                @click.stop="showLog(props.rowData.id)" />
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import Result from '@/models/analysis/result';
import ResultRepository from '@/repositories/analysis/result';
import ProxyRepository from '@/repositories/proxy-repository';
import ClinicRepository from '@/repositories/clinic';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import InformationSourceRepository from '@/repositories/patient/information-source';
import Attachments from './attachments/Attachments.vue';
import CONSTANTS from '@/constants';
import AnalysisResultLog from '@/components/action-log/AnalysisResult.vue';

export default {
    components: {
        Attachments,
    },
    props: {
        filters: Object,
    },
    data() {
        return {
            rows: [],
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repository = new ResultRepository();
                return repository.fetch(this.getFilters(filters), sort, scopes, page, limit).then((result) => {
                    return {
                        rows: result.rows,
                        pagination: result.pagination,
                    }
                });
            }),
            fields: [
                ...(this.$canUpdate('analysis-results') ? [{
                    name: '__checkbox',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    width: '30px',
                }] : []),
                ...(this.$can('action-logs.access') ? [{
                    name: 'logbtn',
                    title: '',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    width: '30px',
                }] : []),
                {
                    name: 'analysis.laboratory_code',
                    sortField: 'laboratory_code',
                    title: __('Код лаборатории'),
                    width: '100px',
                    dataClass: 'no-dash',
                    filterField: 'laboratory_code',
                    filter: true,
                },
                {
                    name: 'analysis.laboratory_name',
                    sortField: 'laboratory_name',
                    title: __('Лаборатория'),
                    width: '100px',
                    dataClass: 'no-dash',
                    filterField: 'laboratory',
                    filter: new LaboratoryRepository(),
                },
                {
                    name: 'analysis.clinic.code',
                    sortField: 'clinic_code',
                    title: __('Код клиники'),
                    width: '100px',
                    dataClass: 'no-dash',
                    filterField: 'clinic_code',
                    filter: true,
                },
                {
                    name: 'analysis.clinic.name',
                    sortField: 'clinic_name',
                    title: __('Клиника'),
                    width: '100px',
                    filterField: 'clinic',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('analysis-results'),
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'analysis.name',
                    sortField: 'analysis_name',
                    title: __('Название'),
                    width: '250px',
                    dataClass: 'no-ellipsis',
                    filterField: 'analysis_name',
                    filter: true,
                },
                {
                    name: 'patient.card_number',
                    title: __('№ карты'),
                    width: '100px',
                    dataClass: 'no-dash',
                    filterField: 'patientCardNumber',
                    sortField: 'patient_card_number',
                    filter: true,
                },
                {
                    name: 'patient.full_name',
                    title: __('Пациент'),
                    width: '250px',
                    filterField: 'patient_name',
                    sortField: 'patient_name',
                    filter: true,
                },
                {
                    name: 'assigner.name',
                    title: __('Врач назначивший'),
                    width: '250px',
                    dataClass: 'no-dash',
                    filterField: 'assigner_fullname',
                    sortField: 'assigner_fullname',
                    filter: true,
                    scopes: ['assigner'],
                },
                {
                    name: 'patient.email',
                    title: __('Email пациента'),
                    width: '200px',
                    dataClass: 'no-dash text-select',
                    filterField: 'patient_email',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'patient.mailing_analysis',
                    title: __('Отправлять на Email'),
                    width: '150px',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filterField: 'patient_mailing_analysis',
                    filter: 'yes_no',
                },
                {
                    name: 'status',
                    title: __('Статус анализа'),
                    width: '100px',
                    formatter: (value) => {
                        return this.$handbook.getOption('analysis_status', value);
                    },
                    filterField: 'status',
                    filter: 'analysis_status',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'quantity',
                    title: __('Количество'),
                    width: '100px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'price.cost',
                    title: __('Цена'),
                    width: '100px',
                    sortField: 'price_cost',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                    filter: true,
                    filterField: 'price_cost',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    scopes: ['price'],
                },
                {
                    name: 'discount',
                    title: __('Скидка, %'),
                    width: '100px',
                    filter: true,
                    filterField: 'discount',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'cost',
                    title: __('Стоимость'),
                    width: '100px',
                    filter: true,
                    filterField: 'cost',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'attachment_data',
                    title: __('Результаты'),
                    filterField: 'attachments',
                    filter: 'yes_no',
                    width: '200px',
                },
                {
                    name: 'created',
                    sortField: 'created',
                    title: __('Дата назначения'),
                    width: '120px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            scopes: [
                'appointment',
                'patient',
                'patient_card_number',
                'analysis',
                'attachments',
            ],
        };
    },
    methods: {
        getFilters(filters) {
            return _.onlyFilled({
                has_appointment: false,
                clinic: filters.clinic,
                analysis_name: filters.analysis_name,
                laboratory: filters.laboratory,
                laboratory_code: filters.laboratory_code,
                clinic_code: filters.clinic_code,
                date_pass_start: filters.date_pass_start,
                date_pass_end: filters.date_pass_end,
                patientCardNumber: filters.patientCardNumber,
                patientCardSpecialization: filters.patientCardSpecialization,
                patient_lastname: filters.patient_lastname,
                patient_firstname: filters.patient_firstname,
                patient_middlename: filters.patient_middlename,
                patient_name: filters.patient_name,
                assigner_fullname: filters.assigner_fullname,
                price_cost: filters.price_cost,
                discount: filters.discount,
                cost: filters.cost,
                date_appointed_start: filters.date_appointed_start,
                date_appointed_end: filters.date_appointed_end,
                status: [
                    CONSTANTS.ANALYSIS_RESULT.STATUSES.ASSIGNED,
                    CONSTANTS.ANALYSIS_RESULT.STATUSES.TEST_IN_OTHER_LABORATORY,
                    CONSTANTS.ANALYSIS_RESULT.STATUSES.ASSIGNED_BUT_NOT_BE_TEST
                ],
            })
        },
        selectionChanged(selection) {
            this.rows = selection;
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        getSelectedRows() {
            let rows = [];
            let selected = this.$refs.table.getSelectedRows();
            let list = this.$refs.table.getData();
            list.forEach((item) => {
                if (selected.indexOf(item.id) !== -1) {
                    rows.push(new Result(item.attributes));
                }
            });
            return rows;
        },
        onRowClass(dataItem, index) {
            let className = [];

            if (this.rows.indexOf(dataItem.id) !== -1) {
                className.push('selected-table-row');
            }
            return className;
        },
        showLog(id) {
            this.$modalComponent(AnalysisResultLog, {
                id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения результата анализа'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
}
</script>
