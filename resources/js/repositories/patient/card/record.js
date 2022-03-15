import BaseRepository from '@/repositories/base-repository';
import OutpatientRecord from '@/models/patient/card/outpatient-record';
import DiaryRecord from '@/models/patient/card/diary-record';
import ProtocolRecord from '@/models/patient/card/protocol-record';
import OutclinicProtocolRecord from '@/models/patient/card/outclinic-protocol-record';
import CardAssignment from '@/models/patient/card/assignment';
import Appointment from '@/models/appointment';
import TreatmentAssignment from '@/models/patient/card/treatment-assignment';
import ConsultationRecord from '@/models/patient/card/consultation-record';
import PatientDocument from '@/models/patient/card/document';
import CONSTANT from '@/constants';
import NextVisit from "@/models/patient/card/next-visit";
import PrintedDocument from "@/models/patient/card/printed-document";
import ServiceRecord from "@/models/patient/card/service-record";
import ConditionRecord from "@/models/patient/card/condition-record";
import ManipulationRecord from "@/models/patient/card/manipulation-record";

class CardRecordRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/cards/specializations/records';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        switch (row.type) {
            case CONSTANT.CARD_RECORD.TYPE.OUTPATIENT_RECORD:
                return new OutpatientRecord(row);

            case CONSTANT.CARD_RECORD.TYPE.DIARY_RECORD:
                return new DiaryRecord(row);

            case CONSTANT.CARD_RECORD.TYPE.SERVICE_RECORD:
                return new ServiceRecord(row);

            case CONSTANT.CARD_RECORD.TYPE.CONDITION_RECORD:
                return new ConditionRecord(row);

            case CONSTANT.CARD_RECORD.TYPE.PROTOCOL_RECORD:
                return new ProtocolRecord(row);

            case CONSTANT.CARD_RECORD.TYPE.OUTCLINIC_PROTOCOL_RECORD:
                return new OutclinicProtocolRecord(row);

            case CONSTANT.CARD_RECORD.TYPE.CARD_ASSIGNMENT:
                return new CardAssignment(row);

            case CONSTANT.CARD_RECORD.TYPE.TREATMENT_ASSIGNMENT:
                return new TreatmentAssignment(row);

            case CONSTANT.CARD_RECORD.TYPE.CONSULTATION_RECORD:
                return new ConsultationRecord(row);

            case CONSTANT.CARD_RECORD.TYPE.PATIENT_DOCUMENT:
                return new PatientDocument(row);

            case CONSTANT.CARD_RECORD.TYPE.NEXT_VISIT:
                return new NextVisit(row);

            case CONSTANT.CARD_RECORD.TYPE.PRINTED_DOCUMENT:
                return new PrintedDocument(row);
            case CONSTANT.CARD_RECORD.TYPE.MANIPULATION_RECORD:
                return new ManipulationRecord(row)
            default:
                return row;
        }
    }

    /**
     * Get appointment records
     *
     * @param {number} specializationCard
     * @param {number} appointment
     * @param {object} filters
     * @param {bool} withPrevious
     * @param {number} template
     *
     * @returns {Promise}
     */
    getAppointmentRecords(specializationCard, appointment, filters = null, withPrevious = true, template = null, onlyEmployee = true) {
        let path = `${specializationCard}/appointment/${appointment}`;
        let params = {
            template,
            withPrevious: withPrevious ? 1 : 0,
            onlyEmployee,
            ...this.getFilters(filters),
        };

        return axios.get(this.buildUrl(path, params))
            .then((response) => {
                return {
                    records: response.data.records.map((row) => this.transformRow(row)),
                    outpatientData: new OutpatientRecord(response.data.outpatient_data),
                    previousVisit: response.data.previous_visit
                        ? {
                            appointment: new Appointment(response.data.previous_visit.appointment),
                            records: response.data.previous_visit.records.map((row) => this.transformRow(row)),
                        }
                        : null,
                };
            });
    }

    /**
     * Get appointment records
     *
     * @param {number} specializationCard
     * @param {object} filters
     *
     * @returns {Promise}
     */
    getCardRecords(specializationCard, filters = null, page = 1, limit = 50) {
        return this.fetchInternal(this.buildUrl(specializationCard, {
            ...this.getFilters(filters),
            page,
            limit,
        }));
    }
}

export default CardRecordRepository;
