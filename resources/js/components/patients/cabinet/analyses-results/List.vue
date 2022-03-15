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
            slot="logbtn">
            <svg-icon
                name="info-alt"
                class="icon-tiny icon-grey"
                :title="__('Операции')"
                @click.stop="showLog(props.rowData.id)" />
        </template>
        <template
            slot-scope="props"
            slot="record_date">
            {{ props.rowData.appointment ?
            $formatter.dateFormat(props.rowData.appointment.date) :
            $formatter.dateFormat(props.rowData.created) }}
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
        <template slot="footer-top">
            <div
                slot="buttons"
                class="buttons">
                <el-button
                    v-if="$can('patient-cabinet.outclinic-analysis-add')"
                    @click="addAnalysesResult">
                    {{ __('Добавить результат') }}
                </el-button>
                <el-button
                    v-if="$can([
                        'patient-cabinet.analysis-date-ready-set',
                        'patient-cabinet.analysis-date-email-sent-set'
                    ])"
                    :disabled="activeItem === null"
                    type="primary"
                    @click="edit">
                    {{ __('Поставить дату готовности/отправки') }}
                </el-button>
                <el-button
                    v-if="$can('analysis-results.submit-result')"
                    @click="sendResults">
                    {{ __('Отправить пациенту') }}
                </el-button>
                <el-button
                    v-if="$can('patient-cabinet.outclinic-analysis-delete')"
                    :disabled="activeItem === null"
                    @click="deleteAnalysesResult">
                    {{ __('Удалить') }}
                </el-button>
            </div>
        </template>
    </manage-table>
</template>
<script>
import Result from '@/models/analysis/result';
import ResultRepository from '@/repositories/analysis/result';
import ClinicRepository from '@/repositories/clinic';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import AddAnalysisResult from "@/components/patients/cabinet/analyses-results/OutclinicAnalysisForm";
import FormResultEdit from "../../analysis-results/FormResultEdit";
import FileActionMixin from '@/mixins/file-action';
import SendResultMixin from '@/components/patients/analysis-results/mixins/send-result';
import AnalysisResultLog from '@/components/action-log/AnalysisResult.vue';
import EmailLog from "@/components/patients/analysis-results/email-log/EmailLog.vue";

export default {
    mixins: [
        FileActionMixin,
        SendResultMixin,
    ],
    props: {
        filters: Object,
        patient:Object
    },
    data() {
        return {
            rows: [],
            viewedAnalysis: null,
            repository: new ResultRepository(),
            fields: [
                ...(this.$can([
                    'patient-cabinet.analysis-date-ready-set',
                    'patient-cabinet.analysis-date-email-sent-set',
                    'patient-cabinet.outclinic-analysis-delete',
                    'analysis-results.submit-result'
                ]) ? [{
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
                    name: 'record_date',
                    title: __('Дата'),
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'appointment_date',
                    filter: DateHeaderFilter,
                },

                {
                    name: 'analysis_name',
                    sortField: 'analysis_name',
                    title: __('Название'),
                    width: '250px',
                    dataClass: 'no-ellipsis',
                    filterField: 'analysis_name',
                    filter: true,
                },
                {
                    name: 'appointment.card_number',
                    title: __('№ карты'),
                    width: '100px',
                    filterField: 'patient_card_number',
                    filter: true,
                },
                {
                    name: 'assigner.name',
                    title: __('Врач назначивший'),
                    width: '250px',
                    filterField: 'assigner_fullname',
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
                    name: 'analysis.laboratory_name',
                    sortField: 'laboratory_name',
                    title: __('Лаборатория'),
                    width: '100px',
                    dataClass: 'no-dash',
                    filterField: 'laboratory',
                    filter: new LaboratoryRepository(),
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
                    name: 'date_pass',
                    title: __('Дата сдачи'),
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'date_pass',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'date_expected_ready',
                    title: __('Дата предп. готовности'),
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'date_expected_ready',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'date_ready',
                    title: __('Дата готовности'),
                    width: '100px',
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
                    filterField: 'patient_email',
                    filter: true,
                },
                {
                    name: 'patient.mailing_analysis',
                    title: __('Отправлять на Email'),
                    width: '150px',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filterField: 'patient_mailing_analysis',
                    filter: true,
                },
                {
                    name: 'date_sent_email',
                    title: __('Дата отправки на email'),
                    width: '100px',
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
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    filterField: 'price_cost',
                    filter: true,
                    scopes: ['price'],
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
                    name: 'analysis.laboratory_code',
                    sortField: 'laboratory_code',
                    title: __('Код лаборатории'),
                    width: '100px',
                    filterField: 'laboratory_code',
                    filter: true,
                },
                {
                    name: 'analysis.clinic.code',
                    sortField: 'clinic_code',
                    title: __('Код клиники'),
                    width: '100px',
                    filterField: 'clinic_code',
                    filter: true,
                },
                {
                    name: 'is_outclinic',
                    title: __('Вне записи'),
                    filter: 'yes_no',
                    filterField: 'outclinic',
                    dataClass: 'no-ellipsis no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value,
                            '<span class="check-yes" />');
                    },
                    width: '90px',
                }
            ],
            activeItem: null,
            selectedTo: [],
            initialSortOrder: [
                {field: 'date_pass', direction: 'desc'},
            ],
            scopes: [
                'appointment',
                'patient',
                'analysis',
                'appointment_service',
                'attachments',
            ],
        };
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
        edit() {
            let rows = this.getSelectedRows();

            if (rows.length == 0) {
                return this.$error(__('Вы не можете редактировать выбранные анализы'));
            }

            this.$modalComponent(FormResultEdit, {
                    rows,
                    canSetDateReady: this.$can('patient-cabinet.analysis-date-ready-set'),
                    canSetDateEmailSent: this.$can('patient-cabinet.analysis-date-email-sent-set'),
                    canUploadResult: false
                },
                {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    saved: (dialog, list) => {
                        dialog.close();
                        this.$info(__('Анализы пациентов были успешно обновлены'));
                        this.refresh();
                        this.selectedTo = list;
                    },
                },
                {
                    header: __('Изменить дату сдачи выбранным анализам'),
                    width: '1150px',
                });
        },
        selectionChanged(selection) {
            this.setActiveItem(selection);
            this.rows = selection;
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
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
        setActiveItem(selection) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
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
        addAnalysesResult() {
            this.$modalComponent(AddAnalysisResult, {
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, record) => {
                    dialog.close();
                    this.$emit('analysis-added', record);
                    this.refresh();
                },
            },
            {
                header: __('Прикрепить результат анализа к пациенту: {name}', {name: this.patient.full_name}),
                width: '450px',
                customClass: 'padding-0',
            });
        },
        deleteAnalysesResult(){
            let selectedAnalyses = this.getSelectedRows();

            if(!this.checkIfSelectedAnalysesIsOutclinic(selectedAnalyses)){
                return this.$error(__('Среди выбранных анализов есть анализы из клиники'));
            }

            this.$confirm(__('Вы уверены, что хотите удалить документ (документы)'), () => {
                selectedAnalyses.forEach((analyses) => {
                    analyses.delete().then(() => {
                        this.$info(__('Анализ успешно удалён'));
                        this.refresh();
                    });
                });
            })
        },
        checkIfSelectedAnalysesIsOutclinic(analyses){
            return analyses.every( el => el.is_outclinic !== false )
        },
        getManageTable() {
            return this.$refs.table;
        },
        refresh() {
            this.getManageTable().refresh();
        },
        refreshed() {
            if (this.selectedTo.length !== 0) {
                this.getManageTable().updateSelection((item) => {
                    return this.selectedTo.indexOf(item.id) !== -1;
                });
                this.selectedTo = [];
            }
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
