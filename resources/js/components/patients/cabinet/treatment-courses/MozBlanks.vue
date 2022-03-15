<template>
    <div v-if="!loading">
        <manage-table
            ref="table"
            :fields="fields"
            :repository="repository"
            :enable-pagination="false"
            table-height="200px">
             <template
                slot="name"
                slot-scope="props" >
                <template v-if="props.rowData.attachments_data.length !== 0">
                    <a
                        href="#"
                        @click.prevent="show(props.rowData)">
                        {{ props.rowData.name }}
                    </a>
                </template>
                <template v-else>
                    {{ props.rowData.name }}
                </template>
             </template>
            <template
                slot="action"
                slot-scope="props" >
                <div class="has-icon">
                    <a href="#" @click.prevent="prepareDocument(props.rowData)">{{ __('Сформировать') }}</a>
                </div>
            </template>
        </manage-table>
    </div>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import StationarPatientCard from './course-templates/StationarPatientCard.vue';
import Anesthesiology from './course-templates/Anesthesiology.vue';
import DischargedCard from './course-templates/DischargedCard.vue';
import StationarCardExtraction from './course-templates/StationarCardExtraction.vue';
import DoctorAssignmentList from './course-templates/DoctorAssignmentList.vue';
import OperationProtocol from './course-templates/OperationProtocol.vue';
import EpidemiologicalMap from './course-templates/EpidemiologicalMap.vue';
import Clinic from '@/models/clinic';
import CabinetMixin from '@/components/patients/cabinet/mixins/cabinet';
import HeaderAddon from './course-templates/HeaderAddon.vue';
import TreatmentCourseDocument from '@/models/treatment-course/document';
import FileActionMixin from '@/mixins/file-action';
import FileViewer from '@/components/general/FileViewer.vue';
import CONSTANTS from '@/constants';
import DigitalSignMixin from '@/mixins/digital-sign';
import DocumentSignature from '@/models/treatment-course/document-signature';
import Signatures from './Signatures.vue';

export default {
    mixins: [
        CabinetMixin,
        FileActionMixin,
        DigitalSignMixin,
    ],
    props: {
        course: Object,
        patient: Object,
        diagnosisIcd: {
            type: Array,
            default: () => [],
        },
        diagnosis: {
            type: String,
            default: '',
        },
        treatmentActivities: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                let blanks = this.$handbook.getOptions('stationar_moz_blank');

                return Promise.resolve({
                    rows: blanks.map(doc => {
                        let courseDoc = this.course.documents.find(item => item.type === doc.id);
                        return new TreatmentCourseDocument({
                            type: doc.id,
                            name: doc.value,
                            treatment_course_id: this.course.id,
                            ...(courseDoc ? courseDoc.attributes : {}),
                        });
                    }),
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название МОЗ формы'),
                    width: '40%',
                },
                {
                    name: 'last_sign_date',
                    title: __('Дата подписания'),
                    width: '15%',
                    formatter: (val) => {
                        return this.$formatter.datetimeFormat(val);
                    }
                },
                {
                    name: 'signed',
                    title: __('Подпись'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'last_signer_name',
                    title: __('Подписал врач'),
                    width: '20%',
                },
                {
                    name: 'action',
                    title: '',
                    width: '15%',
                },
            ],
            loading: true,
            clinic: null,
        };
    },
    mounted() {
        this.getClinic();
        this.loadSignalRecord();
    },
    methods: {
        getClinic() {
            let treatmentClinic = this.course.appointments.find(appointment => {
                return appointment.services.findIndex(service => {
                    return service.specialization.service_group === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY;
                }) !== -1;
            });

            if (!treatmentClinic) {
                this.loading= false;
                return null;
            }
            let clinic = new Clinic({id: treatmentClinic.clinic_id});
            clinic.fetch().then(() => {
                this.loading= false;
                this.clinic = clinic;
            });
        },
        prepareDocument(row) {
            let template = this.getTemplate(row);
            if (!template) {
                return this.$error(__('Бланк отсутствует'));
            }

            this.$modalComponent(template, {
                model: row,
                course: this.course,
                patient: this.patient,
                clinic: this.clinic,
                diagnosisIcd: this.diagnosisIcd,
                diagnosis: this.diagnosis,
                signalRecord: this.signalRecord,
                treatmentActivities: this.treatmentActivities,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                    this.$refs.table.refresh();
                },
                saved: (dialog, model) => {
                    dialog.getTopHeaderAddon().allowActions();
                    let docIndex = this.course.documents.findIndex(doc => doc.type === model.type);
                    if (docIndex !== -1) {
                        this.course.documents.splice(docIndex, 1, model);
                    } else {
                        this.course.documents.push(model);
                    }
                    this.$refs.table.refresh();
                },
            }, {
                header: row.name ? __(row.name) : '',
                width: '1100px',
                headerAddon: {
                    component: HeaderAddon,
                    props: {
                        document: row,
                    },
                    eventListeners: {
                        download: (dialog) => {
                            dialog.getTopComponent().download();
                        },
                        print: (dialog) => {
                            dialog.getTopComponent().print();
                        },
                        sign: (dialog) => {
                            dialog.getTopComponent().getFileBlob()
                                .then((file) => {
                                    this.signFile(file, row);
                                });
                        },
                        showSign: (dialog, document) => {
                            this.showSignatures(document);
                        },
                    }
                },
            });
        },
        getTemplate(row) {
            let key = row.type;
            switch (key) {
                case CONSTANTS.STATIONAR_MOZ_BLANK.STATIONAR_CARD:
                    return StationarPatientCard;
                case CONSTANTS.STATIONAR_MOZ_BLANK.INSPECTION_AND_ANESTHESIA:
                    return Anesthesiology;
                case CONSTANTS.STATIONAR_MOZ_BLANK.DISCHARGED_CARD:
                    return DischargedCard;
                case CONSTANTS.STATIONAR_MOZ_BLANK.STATIONAR_CARD_EXTRACTION:
                    return StationarCardExtraction;
                case CONSTANTS.STATIONAR_MOZ_BLANK.DOCTOR_ASSIGNMENT_LIST:
                    return DoctorAssignmentList;
                case CONSTANTS.STATIONAR_MOZ_BLANK.DISCHARGE_DIARY:
                    return OperationProtocol;
                case CONSTANTS.STATIONAR_MOZ_BLANK.EPIDEMIOLOGICAL_MAP:
                    return EpidemiologicalMap;
                default:
                    return null;
            }
        },
        show(model) {
            let url = model.attachments_data[0].url;
            let title = model.name;
            let header = title + " №" + this.patient.card_number;
            this.$modalComponent(FileViewer, {url}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                print: (dialog, data) => {
                    if (this.printHandle){
                        this.printHandle(data);
                    }
                    dialog.$emit('print', data);
                },
            }, {
                header,
                width: '1100px',
                headerAddon: {
                    component: HeaderAddon,
                    props: {
                        initialAllowed: true,
                        document: model,
                    },
                    eventListeners: {
                        download: (dialog) => {
                            dialog.getTopComponent().download(header);
                        },
                        print: (dialog) => {
                            dialog.getTopComponent().print();
                        },
                        sign: (dialog) => {
                            dialog.getTopComponent().getFileBlob()
                                .then((file) => {
                                    this.signFile(file, model);
                                });
                        },
                        showSign: (dialog, document) => {
                            this.showSignatures(document);
                        },
                    },
                }
            });
        },
        signFile(file, model) {
            this.signData(file, (signed) => {
                let bytes = atob(signed);
                let byteNumbers = new Array(bytes.length);
                for (let i = 0; i < bytes.length; i++) {
                    byteNumbers[i] = bytes.charCodeAt(i);
                }
                let byteArray = new Uint8Array(byteNumbers);
                let blob = new Blob([byteArray], {type: file.type});
                let filename = model.attachments_data[0].name + '.p7s';
                let request = this.fileLoader.upload(blob, filename);
                request.promise().then((response) => {
                    let fileId = response.data.id;
                    let signature = new DocumentSignature({
                        file_id: fileId,
                        document_id: model.id,
                    });
                    return signature.save().then(() => {
                        model.signatures.push(signature);
                        this.$info(__('Подписаный документ успешно сохранен'));
                    });
                }).catch((error) => {
                    console.log(error);
                    this.$error(__('Не удалось сохранить подписаный документ'));
                });
            });
        },
        showSignatures(document) {
            this.$modalComponent(Signatures, {document}, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Список подписантов'),
                width: '800px',
                customClass: 'no-footer',
            });
        }
    },
}
</script>
