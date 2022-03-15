import BaseReportRepository from '../base-report-repository';
import CONSTANTS from '@/constants';

class DoctorSpecializationReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/finance/doctor-specialization');
    }

    fetch(filters = null) {
        return this.fetchAppointments(this.getAppointmentsFilter(filters)).then((appointments) => {
            return this.fetchPayments(filters).then((payments) => {
                return {appointments, payments};
            });
        });
    }

    /**
     * Fetch payments data
     *
     * @param {object} filters
     *
     * @returns {Promise}
     */
    fetchPayments(filters = null) {
        return this.fetchInternal(this.buildUrl(null, {
            ...this.getFilters(filters), 
        }), false);
    }

    /**
     * Fetch appointments data
     *
     * @param {object} filters
     *
     * @returns {Promise}
     */
    fetchAppointments(filters = null) {
        return this.fetchInternal(this.buildUrl('appointments', {
            ...this.getFilters(filters), 
        }), false);
    }

    /**
     * Get appointment filters
     * 
     * @param {object} filters 
     */
    getAppointmentsFilter(filters) {
        return _.onlyFilled({
            card_specialization: filters.specialization,
            date_start: filters.date_start,
            date_end: filters.date_end,
            date_end: filters.date_end,
            doctor_has_specialization: filters.specialization,
            is_deleted: filters.is_deleted,
            different_specializations: false,
            skip_system_status: [
                CONSTANTS.APPOINTMENT.STATUSES.DID_NOT_COME,
                CONSTANTS.APPOINTMENT.STATUSES.DELETED,
                CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP,
                CONSTANTS.APPOINTMENT.STATUSES.PAYED,
                CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_RECEPTION,
            ],
        });
    }
}

export default DoctorSpecializationReportRepository;