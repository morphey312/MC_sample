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
        @header-filter-updated="syncFilters"
        @refresh="refresh">
        <template
            slot-scope="props"
            slot="analysis_name">
            <template v-if="props.rowData.attachments_data.length !== 0">
                <a
                    href="#"
                    @click.prevent="viewFile(props.rowData)">
                {{ props.rowData.analysis ? props.rowData.analysis.name : props.rowData.custom_name }}
                </a>
            </template>
            <template v-else>
                {{ props.rowData.analysis ? props.rowData.analysis.name : props.rowData.custom_name }}
            </template>
        </template>
        <template
            slot-scope="props"
            slot="attachment_data">
            <attachments :model="props.rowData" />
        </template>
        <template
            slot-scope="props"
            slot="delivery_status">
            <div style="display: flex; justify-content: space-between">
                <span>{{ $handbook.getOption('delivery_status', props.rowData.delivery_status) }}</span>
                <svg-icon
                    v-if="$can('action-logs.email')"
                    name="info-alt"
                    class="icon-tiny icon-grey"
                    :title="__('Операции')"
                    @click.stop="showEmailLog(props.rowData.id)" />
            </div>
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
import ClinicRepository from '@/repositories/clinic';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import Attachments from './attachments/Attachments.vue';
import DateRangeHeaderFilter from '@/components/general/table/DateRangeHeaderFilter.vue';
import FileActionMixin from '@/mixins/file-action';
import CONTANTS from '@/constants';
import InformationSourceRepository from '@/repositories/patient/information-source';
import AnalysisResultLog from '@/components/action-log/AnalysisResult.vue';
import EmailLog from "@/components/patients/analysis-results/email-log/EmailLog.vue";

export default {
    mixins: [
        FileActionMixin,
    ],
    components: {
        Attachments,
    },
    props: {
        filters: Object,
    },
    data() {
        return {
            rows: [],
            viewedAnalysis: null,
            repository: new ResultRepository(),
            fields: [
                ...(this.$canUpdate('analysis-results') ? [{
                    name: '__checkbox',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    width: '30px',
                }] : []),
                {
                    name: 'attachment_data',
                    title: '',
                    width: '30px',
                },
                {
                    name: 'appointment.date',
                    title: __('Дата записи'),
                    width: '100px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'appointment_date',
                    sortField: 'appointment_date',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'analysis.laboratory_code',
                    sortField: 'laboratory_code',
                    title: __('Код лаборатории'),
                    width: '100px',
                    dataClass: 'no-dash',
                    filterField: 'laboratory_code',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
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
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'analysis.clinic.name',
                    sortField: 'clinic_name',
                    title: __('Клиника'),
                    width: '150px',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('analysis-results'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'analysis_name',
                    sortField: 'analysis_name',
                    title: __('Название'),
                    width: '250px',
                    dataClass: 'no-ellipsis text-select',
                    filterField: 'analysis_name',
                    filter: true,
                },
                {
                    name: 'appointment.card_number',
                    title: __('№ карты'),
                    width: '100px',
                    dataClass: 'no-dash text-select',
                    filterField: 'patient_card_number',
                    filter: true,
                    sortField: 'patient_card_number',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'patient.archive_card_number',
                    filterField: 'patient_archive_card_number',
                    title: __('Архив № карты'),
                    width: '100px',
                    dataClass: 'no-dash text-select',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                    scopes: ['patient_archive_card_number'],
                },
                {
                    name: 'patient.full_name',
                    title: __('Пациент'),
                    width: '250px',
                    dataClass: 'no-dash text-select',
                    filterField: 'patient_name',
                    sortField: 'patient_name',
                    filter: true,
                },
                {
                    name: 'patient.birthday',
                    sortField: 'birthday',
                    title: __('Дата рождения'),
                    width: '130px',
                    dataClass: 'no-dash text-select',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value, 'DD.MM.YYYY');
                    },
                    filterField: 'birthday_range',
                    filter: DateRangeHeaderFilter,
                },
                {
                    name: 'patient.source_name',
                    sortField: 'patient_source',
                    title: __('Источник (пациент)'),
                    width: '130px',
                    dataClass: 'no-dash text-select',
                    filterField: 'patient_source',
                    filter: new InformationSourceRepository(),
                },
                {
                    name: 'appointment.source_name',
                    sortField: 'appointment_source',
                    title: __('Источник (запись)'),
                    width: '130px',
                    dataClass: 'no-dash text-select',
                    filterField: 'appointment_source',
                    filter: new InformationSourceRepository(),
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
                    name: 'status',
                    title: __('Статус анализа'),
                    width: '150px',
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
                    name: 'date_pass',
                    title: __('Дата сдачи'),
                    width: '100px',
                    dataClass: 'no-dash text-select',
                    sortField: 'date_pass_start',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'date_pass',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'date_expected_ready',
                    title: __('Дата предп. готовности'),
                    width: '150px',
                    dataClass: 'no-dash',
                    sortField: 'date_expected_pass',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'date_expected_ready',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'date_ready',
                    title: __('Дата готовности'),
                    width: '150px',
                    dataClass: 'no-dash',
                    sortField: 'date_ready',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'date_ready',
                    filter: DateHeaderFilter,
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
                    sortField: 'mailing_analysis',
                    width: '150px',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filterField: 'patient_mailing_analysis',
                    filter: 'yes_no',
                },
                {
                    name: 'date_sent_email',
                    title: __('Дата отправки на email'),
                    width: '150px',
                    dataClass: 'no-dash',
                    sortField: 'date_sent_email',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'date_sent_email',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'delivery_status',
                    title: __('Статус доставки'),
                    width: '120px',
                    filterField: 'delivery_status',
                    filter: 'delivery_status',
                },
                ...(this.$can('action-logs.access') ? [{
                    name: 'logbtn',
                    title: '',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    width: '30px',
                }] : []),
                {
                    name: 'quantity',
                    title: __('Количество'),
                    width: '100px',
                    sortField: 'quantity',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'price.cost',
                    title: __('Цена'),
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    filterField: 'price_cost',
                    sortField: 'price_cost',
                    filter: true,
                    scopes: ['price'],
                },
                {
                    name: 'price.self_cost',
                    title: __('Себестоимость'),
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                    sortField: 'self_cost',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'discount',
                    title: __('Скидка, %'),
                    width: '100px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    filterField: 'discount',
                    filter: true,
                },
                {
                    name: 'cost',
                    title: __('Стоимость'),
                    width: '100px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    filterField: 'cost',
                    filter: true,
                },
                {
                    name: 'appointment_service',
                    title: __('Статус оплаты'),
                    width: '100px',
                    formatter: (value) => {
                        return this.paymentStatus(value);
                    },
                },
                {
                    name: 'by_policy',
                    title: __('Полис'),
                    sortField: 'by_policy',
                    width: '70px',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filterField: 'by_policy',
                    filter: 'yes_no',
                },
                {
                    name: 'warranter',
                    title: __('Гарант'),
                    width: '150px',
                },
            ],
            initialSortOrder: [
                {field: 'date_pass', direction: 'desc'},
            ],
            scopes: [
                'appointment',
                'patient',
                'patient_card_number',
                'analysis',
                'appointment_service',
                'attachments',
            ],
        };
    },
    mounted() {
        this.$eventHub.$on('resultListRefresh', () => {
            this.refresh()
        });
    },
    beforeDestroy() {
        this.$eventHub.$off('resultListRefresh');
    },
    methods: {
        viewFile(analysis) {
            this.viewedAnalysis = analysis
            this.view(analysis.attachments_data[1] ? analysis.attachments_data[1].url
                : analysis.attachments_data[0].url)
        },
        afterPrint() {
            if (this.viewedAnalysis) {
                this.viewedAnalysis.printed()
            }
        },
        afterDownload() {
            if (this.viewedAnalysis) {
                this.viewedAnalysis.downloaded()
            }
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
                if(selected.indexOf(item.id) !== -1) {
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
        paymentStatus(service) {
            if (service) {
                let cost = Number(service.cost);
                let payed = Number(service.payed);

                if (cost === payed && payed !== 0) {
                    return this.$handbook.getOption('analysis_payment_status', CONTANTS.ANALYSIS_RESULT.PAYMENT_STATUSES.PAYED);
                } else if (payed === 0) {
                    return this.$handbook.getOption('analysis_payment_status', CONTANTS.ANALYSIS_RESULT.PAYMENT_STATUSES.IN_DEBT);
                }
                return this.$handbook.getOption('analysis_payment_status', CONTANTS.ANALYSIS_RESULT.PAYMENT_STATUSES.PARTLY);
            }
            return this.$handbook.getOption('analysis_payment_status', CONTANTS.ANALYSIS_RESULT.PAYMENT_STATUSES.IN_DEBT);
        },
        showEmailLog(id) {
            this.$modalComponent(EmailLog, {
                id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Жизненный цикл e-mail'),
                width: '900px',
                customClass: 'no-footer',
            });
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
        refresh() {
            return this.$refs.table.refresh();
        }
    },
}
</script>
