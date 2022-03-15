<!-- /** @scan-translations-off */ -->
<template>
     <div>
        <div style="position: absolute; top: -55555px;">
            <div class="moz-blank" id="moz-blank" style="width: 185mm;">
                 <div class="blank-body">
                    <div class="text-center"><h1>{{ __('Карта епідеміологічного спостереження') }}</h1></div>
                    <div class="blank-content">
                        <div class="row">
                            <b>{{ __('Історія хвороби N') }}:</b>&nbsp;{{ patient.card_number }}
                        </div>
                        <div class="row">
                            <b>{{ __('Палата') }}:</b>&nbsp;1
                        </div>
                        <div class="row">
                            <b>{{ __('Відділення') }}:</b>&nbsp; {{ __('Хірургії') }}
                        </div>
                        <div class="row">
                            <div><b>{{ __('Дати: госпіталізації') }}:</b>&nbsp;{{ hospitalisationStart }}</div>
                            <div><b>{{ __('виписки') }} </b>&nbsp;{{ dischargeDate }}</div>
                        </div>
                        <div class="row">
                            <div><b>{{ __('ХІРУРГІЧНІ ВТРУЧАННЯ') }}:</b></div>
                        </div>
                        <div class="row">
                            <div><b>{{ __('ДАТА') }}:</b>{{ formatDate(surgery.date) }}</div>
                        </div>
                        <div class="row">
                            <b>{{ __('Вид операції') }}:</b>&nbsp; {{ surgeryServiceName }}
                        </div>
                        <div class="row">
                            <div><b>{{ __('Тривалість операції: початок') }}:</b>&nbsp; {{ $formatter.datetimeFormat(surgery.start) }}</div>
                            <div><b>{{ __('закінчення') }}:</b>&nbsp; {{ $formatter.datetimeFormat(surgery.end) }}</div>
                        </div>
                        <div
                            v-if="outpatientRecord"
                            class="outpatient-record-details sections-wrapper" style="margin:0; margin-left:-20px;">
                            <section>
                                <div class="card-record-section">
                                    <template v-if="structure">
                                        <fields-section
                                            v-for="(section, index) in structure.getSections()"
                                            :key="index"
                                            :label="section.label"
                                            :fields="section.children"
                                            :readonly="true" />
                                    </template>
                                </div>
                            </section>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
        <section 
            v-loading="loading"
            ref="docContainer"
            id="blank-container"
            class="light-grey pdf-container">
        </section>
     </div>
</template>
<script>
import DocumentMixin from './mixin/document';
import CardRecordRepository from '@/repositories/patient/card/record';
import OutpatientRecord from '@/models/patient/card/outpatient-record';
import FieldsSection from '@/components/patients/cabinet/outpatient-cards/print/Section.vue';
import {Structure} from '@/services/card/template';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        DocumentMixin,
    ],
    components: {
        FieldsSection,
    },
    data() {
        return {
            outpatientRecord: null,
            documentTitle: __('Карта_епідеміологічного_спостереження') + '_№' + this.patient.card_number,
            includeSections: [
                CONSTANTS.CARD_RECORD.SECTIONS.EPIDEMIOLOGICAL_MAP,
            ],
        };
    },
    beforeMount() {
        this.getAppointmentRecords();
    },
    methods: {
        getAppointmentRecords() {
            let surgeryService = this.getSurgeryService();
            if (!surgeryService) {
                this.$error(__('Проверьте наличие назначенной операции в курсе лечения'));
                return;
            }

            let filters = {
                or: [
                    {course_records: this.course.id},
                    {card_assignment: surgeryService.card_assignment_id},
                ]
            };
            let record = new CardRecordRepository();
            record.fetch(filters, [{field: 'id', direction: 'desc'}]).then(courseRecords => {
                if (courseRecords && courseRecords.rows && courseRecords.rows.length !== 0) {
                    let outpatientRecords = courseRecords.rows.filter(row => row instanceof OutpatientRecord);
                    //Return if specialization template is missing
                    if (outpatientRecords.length === 0) {
                        this.$error(__('Шаблон амбулаторной карты отсутствует'));
                        return this.$emit('cancel');
                    }

                    let cardRecord = courseRecords.rows[0];

                    record.getAppointmentRecords(cardRecord.card_specialization_id, cardRecord.appointment_id, null, true, cardRecord.template_id)
                        .then(appointmentRecords => {
                            let template = appointmentRecords.outpatientData.template;
                            this.outpatientRecord = new OutpatientRecord();
                            appointmentRecords.records.forEach((record) => {
                                if (record instanceof OutpatientRecord) {
                                    if (this.outpatientRecord === null) {
                                        this.outpatientRecord = record;
                                    } else {
                                        this.outpatientRecord.mergeWith(record);
                                    }
                                }
                            });
                            this.outpatientRecord.setPrevious(appointmentRecords.outpatientData);

                            if (template) {
                                this.loadOutpatientCardAddons().then(templateAddons => {
                                    let availableAddons = this.includeAddons(templateAddons);
                                    let structure = new Structure(availableAddons[0].structure, availableAddons[0].field_keys);
                                    this.outpatientRecord.mapStructure(structure);
                                    this.structure = structure;
                                    this.records = appointmentRecords.records;
                                    this.loading = false;
                                    this.$nextTick(() => this.makePDF());
                                });
                            } else {
                                this.records = appointmentRecords.records;
                                this.loading= false;
                                this.$nextTick(() => this.makePDF());
                            }
                        });
                }
            });
        },
    },
}
</script>