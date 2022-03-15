import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    props: {
        model: Object,
        invalidPercents: Boolean,
        blankPercents: Boolean,
    },
    watch: {
        ['model.clinic_id'] () {
            this.getDoctors();
        },
        ['model.specialization_id'] () {
            this.getDoctors();
        },
    },
    methods: {
        mapDoctors(list) {
            return list.map(row => this.makeDoctorRow(row));
        },
        makeDoctorRow(doctor) {
            return {
                doctor_id: doctor.id,
                full_name: doctor.value,
                limitation_percent: 0,
                is_hard_limit: false,
            };
        },
        getDoctorsList(filters) {
            if (_.isVoid(filters.employee_clinic.clinic) || _.isVoid(filters.employee_clinic.specialization)) {
                return Promise.resolve([]);
            }

            let employee = new EmployeeRepository();
            return employee.fetchList(filters).then((response) => {
                return Promise.resolve(response);
            });
        },
        doctorFilters() {
            return _.onlyFilled({
                employee_clinic: {
                    clinic: this.model.clinic_id,
                    specialization: this.model.specialization_id,
                    position_type: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                    status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                },
            });
        },
        cancel() {
            this.$emit('cancel');
        },
        saveModel() {
            this.$emit('save')
        },
        checkPercent() {
            this.$emit('check-percent');
        },
    }
}