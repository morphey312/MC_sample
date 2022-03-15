import RecordTemplateRepository from '@/repositories/patient/card/record-template';
import PdfView from '@/services/pdf-view';
import fileLoader from '@/services/file-loader';
import html2canvas from 'html2canvas';
import {pdfFactory} from '@/services/pdf-blank';
import CONSTANTS from '@/constants';

export default {
    props: {
        model: Object,
        course: Object,
        patient: Object,
        clinic: {
            type: Object,
            default: () => ({}),
        },
        diagnosisIcd: {
            type: Array,
            default: () => [],
        },
        diagnosis: {
            type: String,
            default: '',
        },
        signalRecord: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: true,
            structure: null,
            pdfDocument: null,
            fileBlob: null,
        };
    },
    computed: {
        stationarAppointments() {
            return this.course.appointments.filter(appointment => {
                return appointment.doctor.is_hospital_room === true || appointment.doctor.is_operational === true;
            });
        },
        dischargeDate() {
            if (this.stationarAppointments.length === 0) {
                return '';
            }
            let lastAppointment = _.orderBy(this.stationarAppointments, ['end'], ['desc'])[0];
            return lastAppointment ? this.formatDateTime(lastAppointment.end) : '';
        },
        hospitalisationStart() {
            if (this.stationarAppointments.length === 0) {
                return '';
            }
            let firstAppointment = _.orderBy(this.stationarAppointments, ['date', 'start'], ['asc', 'asc'])[0];
            return firstAppointment ? this.formatDateTime(firstAppointment.start) : '';
        },
        daysHospitaled() {
            if (this.stationarAppointments.length === 0) {
                return 0;
            }
            let appointments = _.orderBy(this.stationarAppointments, ['date'], ['asc']);
            let daysCount = this.$moment(appointments[appointments.length - 1].date).diff(appointments[0].date, 'days');
            return (daysCount > 0) ? daysCount : 1;
        },
        services() {
            let result = [];
            this.course.appointments.forEach((appointment) => {
                appointment.services.forEach((service) => result.push(service));
            });
            return result;
        },
        surgery() {
            return this.course.appointments.find(appointment => {
                return appointment.services.findIndex(service => {
                    return service.is_base && service.specialization.service_group === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY;
                }) !== -1;
            });
        },
        surgeryServices() {
            return this.surgery.services.filter(service => {
                return service.specialization.service_group === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY;
            });
        },
        anesthesiaServices() {
            return this.services.filter(service => {
                return service.service_mark === CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_ANESTHESIA;
            });
        },
        surgeryServiceName() {
            return this.$formatter.listFormat(this.getSurgeryService(), 'name');
        },
        gynecologyObservation() {
            if (this.signalRecord.onco_observation_gyn) {
                return __('Онкоскрининг ГИН') + (this.signalRecord.onco_observation_gyn_date
                    ? (': ' + this.formatDate(this.signalRecord.onco_observation_gyn_date))
                    : ''
                );
            }
            return false;
        },
        proctologyObservation() {
            if (this.signalRecord.onco_observation_pro) {
                return __('Онкоскрининг ПРО') + (this.signalRecord.onco_observation_pro_date
                    ? (': ' + this.formatDate(this.signalRecord.onco_observation_pro_date))
                    : ''
                );
            }
            return false;
        },
        urologyObservation() {
            if (this.signalRecord.onco_observation_uro) {
                return __('Онкоскрининг УРО') + (this.signalRecord.onco_observation_uro_date
                    ? (': ' + this.formatDate(this.signalRecord.onco_observation_uro_date))
                    : ''
                );
            }
            return false;
        },
        hasCancerObservation() {
            return this.gynecologyObservation || this.proctologyObservation || this.urologyObservation;
        },
    },
    methods: {
        formatDateTime(date, format = 'DD.MM.YYYY HH:mm') {
            return this.formatDate(date, format);
        },
        formatDate(date, format = 'DD.MM.YYYY') {
            return this.$formatter.dateFormat(date, format);
        },
        getSurgeryService() {
            return this.services.filter(service => {
                return service.is_base === true &&
                    service.specialization.service_group === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY &&
                    _.isFilled(service.card_assignment_id);
            });
        },
        loadOutpatientCardAddons() {
            let repository = new RecordTemplateRepository();
            return repository.fetch({specialization_addons: this.course.card_specialization_id}).then(templates => {
                if (templates && templates.rows) {
                    return Promise.resolve(templates.rows);
                }
                return Promise.resolve([]);
            });
        },
        excludeAddons(templateAddons) {
            return templateAddons.map(addon => {
                addon.structure = addon.structure.filter(el => !el.sectionKey || this.excludeSections.indexOf(el.sectionKey) === -1);
                return addon;
            }).filter(addon => addon.structure.length !== 0);
        },
        includeAddons(templateAddons) {
            return templateAddons.map(addon => {
                addon.structure = addon.structure.filter(el => this.includeSections.indexOf(el.sectionKey) !== -1);
                return addon;
            }).filter(addon => addon.structure.length !== 0);
        },
        print() {
            if (this.pdfDocument instanceof PdfView) {
                this.pdfDocument.print(this.$refs.docContainer, this.documentTitle);
            }
        },
        download() {
            let link = document.createElement('a');
            let data = window.URL.createObjectURL(this.fileBlob);
            link.href = data;
            link.download = this.documentTitle;
            link.click();
            window.URL.revokeObjectURL(data);
            link.remove();
        },
        getFileBlob() {
            return Promise.resolve(this.fileBlob);
        },
        makePDF() {
            let el = document.getElementById('moz-blank');
            html2canvas(el).then((canvas) => {
                let paddingH = 30;
                let paddingV = 30;
                pdfFactory((pdf) => {
                    pdf.addImage(canvas, 'PNG', paddingH, paddingV);
                    return Promise.resolve();
                }, {
                    'format': [
                        0.75 * (canvas.width + 2 * paddingH),
                        0.75 * (canvas.height + 2 * paddingV),
                    ]
                }).then((blob) => {
                    let name = this.documentTitle + '.pdf';
                    let request = fileLoader.upload(blob, name);
                    this.fileBlob = blob;
                    request.promise().then((response) => {
                        this.model.attachments = [response.data.id];
                        this.model.attachment_data = response.data;
                        this.model.save().then(() => {
                            this.$emit('saved', this.model);
                            let url = window.URL.createObjectURL(blob);
                            this.pdfDocument = new PdfView({blob: url}, this.$refs.docContainer);
                        });
                    }).catch((error) => {
                        console.log(error);
                        this.$error(__('Не удалось сохранить копию документа'));
                    });
                });
            });
        },
    },
}
