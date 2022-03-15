<!-- /** @scan-translations-off */ -->
<template>
    <div>
        <div style="position: absolute; top: -55555px;">
            <div class="moz-blank" id="moz-blank" style="width: 185mm;">
                <blank-header 
                    :clinic="clinic"
                    :blank-data="blankData" />
                <div class="blank-body">
                    <div class="text-center">
                        <h1>{{ __('ПЕРЕДОПЕРАЦІЙНИЙ ОГЛЯД АНЕСТЕЗІОЛОГОМ ТА ПРОТОКОЛ ЗАГАЛЬНОГО ЗНЕБОЛЕННЯ') }}</h1>
                        <div><h1>{{ __('від') }} {{ formatDateTime(surgery.start) }}</h1></div>
                    </div>
                    <div class="blank-content">
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
import BlankHeader from './BlankHeader.vue';
import CardRecordRepository from '@/repositories/patient/card/record';
import OutpatientRecord from '@/models/patient/card/outpatient-record';
import FieldsSection from '@/components/patients/cabinet/outpatient-cards/print/Section.vue';
import {Structure} from '@/services/card/template';
import DocumentMixin from './mixin/document';
import CONSTANTS from '@/constants';

const BlankData = {
    blankNumber: '003-3/о',
    date: '14.02.2012',
    number: 110
};

export default {
    mixins: [
        DocumentMixin,
    ],
    components: {
        BlankHeader,
        FieldsSection,
    },
    data() {
        return {
            blankData: BlankData,
            outpatientRecord: null,
            includeSections: [
                CONSTANTS.CARD_RECORD.SECTIONS.ANESTHESIOLOGY,
                CONSTANTS.CARD_RECORD.SECTIONS.ANESTHESIOLOGY_PROTOCOL,
            ],
            documentTitle: __('ПЕРЕДОПЕРАЦІЙНИЙ_ОГЛЯД_АНЕСТЕЗІОЛОГОМ_ТА_ПРОТОКОЛ_ЗАГАЛЬНОГО_ЗНЕБОЛЕННЯ') + '_№' + this.patient.card_number,
        };
    },
    beforeMount() {
        this.getAnestiologyRecords();
    },
    methods: {
        getAnestiologyRecords() {
            let cardSpecialization = this.patient.cards[0].specializations.find(spec => {
                return spec.specialization_id === this.course.card_specialization_id;
            });

            if(!cardSpecialization) {
                return;
            }

            let record = new CardRecordRepository();

            let filters = {
                card_specialization: cardSpecialization.id,
                patient: this.patient.id,
            };

            record.fetch(filters, [{field: 'id', direction: 'desc'}]).then(courseRecords => {
                if (courseRecords && courseRecords.rows && courseRecords.rows.length !== 0) {
                    let cardAssignment = courseRecords.rows[0];

                    record.getAppointmentRecords(cardAssignment.card_specialization_id, cardAssignment.appointment_id, null)
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
                                    let subtemplate = availableAddons.splice(0, 1)[0];
                                    if (subtemplate) {
                                        template.structure = subtemplate.structure;
                                        template.field_keys = subtemplate.field_keys;
                                    }
                                    let structure = new Structure(template.structure, template.field_keys, availableAddons);
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