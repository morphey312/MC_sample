<template>
    <div v-loading="loading">
        <form-row name="select-all">
            <el-checkbox
                v-model="checkAll"
                @change="handleCheckAllChange">
                <b>{{ __('Выбрать все') }}</b>
            </el-checkbox>
        </form-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <div v-for="(item, index) in itemsFirstHalf" :key="index">
                    <form-row name="option">
                        <el-checkbox
                            v-model="item.selected"
                            :label="item.name">
                            {{ item.label }}
                        </el-checkbox>
                    </form-row>
                </div>
            </el-col>
            <el-col :span="12">
                <div v-for="(item, index) in itemsSecondHalf" :key="index">
                    <form-row name="option">
                        <el-checkbox
                            v-model="item.selected"
                            :label="item.name">
                            {{ item.label }}
                        </el-checkbox>
                    </form-row>
                </div>
            </el-col>
        </el-row>
        <div class="form-footer text-right">
            <el-button
                type="primary"
                class="float-left"
                @click="printMoz">
                {{ __('Печать Форма 028/о') }}
            </el-button>
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="confirm"
                :disabled="!hasSelection">
                {{ __('Печать') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import PrintAdvisory from './Print.vue';
import PrintOfficialBlank from '@/components/doctor/appointment/patient-document/Blank.vue';
import OfficialBlankHeaderAddon from '@/components/doctor/appointment/patient-document/HeaderAddon.vue';
import PatientDocumentRepository from '@/repositories/patient-document';
import PrintedDocument from '@/models/patient/card/printed-document';
import printer from '@/services/print';
import LogPrintMixin from '@/components/doctor/appointment/patient-document/mixins/log-print';
import {pageToPdf} from '@/services/pdf-blank';
import fileLoader from '@/services/file-loader';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        LogPrintMixin,
    ],
    props: {
        structure: Object,
        activeCard: Object,
        appointment: Object,
        appointmentDoctor: Object,
        clinicRequisites: Object,
        outpatientRecord: Object,
        diaryRecords: Array,
        diagnostics: Array,
        analyses: Array,
        consultations: Array,
        medicines: Array,
        physiotherapies: Array,
        procedures: Array,
        nextVisit: Object,
        conditionRecords: Array
    },
    data() {
        return {
            checkAll: false,
            items: [
                {name: 'complains', label: __('Жалобы'), selected: true},
                ...this.getTemplateSections(),
                {name: 'diagnosis_mkb', label: __('Диагноз МКБ'), selected: true},
                {name: 'diagnosis', label: __('Диагноз'), selected: true},
                {name: 'diagnostics', label: __('Диагностическое обследование'), selected: true},
                {name: 'analyses_results', label: __('Забор анализов во время приема'), selected: true},
                {name: 'analyses', label: __('Назначенные анализы'), selected: true},
                {name: 'consultations', label: __('Направление на консультацию'), selected: true},
                {name: 'physiotherapies', label: __('Назначенная физиотерапия'), selected: true},
                {name: 'procedures', label: __('Назначенные процедуры'), selected: true},
                {name: 'medicines', label: __('Назначенные медикаменты'), selected: true},
                {name: 'comment', label: __('Комментарий врача'), selected: true},
                {name: 'next-visit', label: __('Дата следующего  визита'), selected: true},
            ],
            loading: false,
            file: null,
        };
    },
    computed: {
        hasSelection() {
            return this.items.some(i => i.selected);
        },
        itemsFirstHalf() {
            return this.items.slice(0, Math.round(this.items.length / 2));
        },
        itemsSecondHalf() {
            return this.items.slice(Math.round(this.items.length / 2));
        },
    },
    methods: {
        handleCheckAllChange(val) {
            this.items.forEach((item) => {
                item.selected = val;
            });
        },
        getTemplateSections() {
            let result = [];
            if (this.structure) {
                this.structure.getSections().forEach((section, index) => {
                    result.push({
                        name: `section.${index}`,
                        label: section.label || section.hint,
                        selected: false,
                    });
                });
            }
            return result;
        },
        getSelection() {
            return this.items.filter(i => i.selected).map(i => i.name);
        },
        cancel() {
            this.$emit('close');
        },
        confirm() {
            this.$confirm(__('Вы утверждаете выдачу распечатанного документа ?'), () => {
                let items = this.getSelection();
                printer.printComponent(PrintAdvisory, {
                    activeCard: this.activeCard,
                    appointment: this.appointment,
                    appointmentDoctor: this.appointmentDoctor,
                    structure: this.structure,
                    pick: items,
                    record: this.outpatientRecord,
                    diaryRecords: this.diaryRecords,
                    diagnostics: this.diagnostics,
                    analyses: this.analyses,
                    consultations: this.consultations,
                    medicines: this.medicines,
                    physiotherapies: this.physiotherapies,
                    procedures: this.procedures,
                    nextVisit: this.nextVisit,
                    conditionRecords: this.conditionRecords
                }, this.appointment.clinic_id).then((html) => {
                    this.logAdvisoryPrint(html)
                });
                this.$emit('close');
            });
        },
        printMoz() {
            this.getMozBlank().then((blank) => {
                this.$modalComponent(PrintOfficialBlank, {
                    appointment: this.appointment,
                    file: blank,
                    clinicRequisites: this.clinicRequisites,
                }, {
                    cancel: (dialog) => {
                        this.cancel();
                    },
                    printed: (dialog, patientDocument) => {
                        this.logPrint(patientDocument);
                    },
                }, {
                    width: '1100px',
                    headerAddon: {
                        component: OfficialBlankHeaderAddon,
                            eventListeners: {
                            print: (dialog) => {
                                dialog.getTopComponent().print();
                            },
                        },
                    }
                });
            }).catch(() => {
                this.$error(__('Добавьте бланк МОЗ в клинику'));
            });
        },
        getMozBlank() {
            if (this.file !== null) {
                return Promise.resolve(this.file);
            }
            let doc = new PatientDocumentRepository();
            this.loading = true;
            return doc.fetch(this.getDocumentFilters()).then(response => {
                this.loading = false;
                if (response && response.rows && response.rows.length != 0) {
                    this.file = response.rows[0];
                    return this.file;
                }
                throw new Error('No blank');
            });
        },
        getDocumentFilters() {
            return {
                clinic: this.appointment.clinic_id,
                is_official_form: true,
                is_conclusion: true,
            };
        },
        logAdvisoryPrint(html) {
            this.loading = true;
            pageToPdf(html.document)
                .then((blob) => {
                    let date = this.$moment().format('YYYY-MM-DD');
                    let name = `${__('Консультационное заключение')} - ${date}.pdf`;
                    let request = fileLoader.upload(blob, name);
                    return request.promise().then((response) => {
                        let doc = new PrintedDocument({
                            card_specialization_id: this.activeCard.id,
                            appointment_id: this.appointment.id || null,
                            document_name: CONSTANTS.PRINT_LOG.ADVISORY,
                            file_id: response.data.id,
                        });
                        return doc.save();
                    }).catch(() => {
                        this.$error(__('Не удалось сохранить копию консультативного заключения'));
                    });
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        removeProtocolAndDomain(string){
            if(_.isString(string)){
                return new URL(string).pathname;
            }

            return null;
        },
    },
}
</script>
