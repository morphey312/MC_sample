import LaboratoryRepository from '@/repositories/analysis/laboratory';
import ClinicRepository from '@/repositories/clinic';

export default {
    data() {
        return {
            pickerOptionsTo: {
                disabledDate: this.checkDateTo,
                firstDayOfWeek: 1,
            },
            laboratories: new LaboratoryRepository(),
            primaryClinicId: this.$store.state.user.primaryClinicId ?
                [this.$store.state.user.primaryClinicId] : this.$store.state.user.clinics,
        };
    },
    methods: {
        checkDateTo(date) {
            if (this.filter.price_date_to && this.$moment(date).isBefore(this.filter.price_date_from)) {
                return true;
            }
            return false;
        },
        initFilter(fromState = {}) {
            let clinic = [];

            if (this.$isAccessLimited('service-prices')) {
                clinic = this.primaryClinicId;
            }

            let today = this.$moment().format('YYYY-MM-DD');
            this.filter = {
                clinic: clinic,
                name: null,
                laboratory: null,
                laboratory_code: null,
                clinic_code: null,
                disabled: null,
                price_exists: 3,
                price_date_from: today,
                price_date_to: today,
                ...this.convertPriceParams(fromState),
            };
        }
    },
    watch: {
        ['filter.price_date_from'](val) {
            this.filter.price_date_to = val;
        },
        ['filter.clinic'](val) {
            if (val.length) {
                this.laboratories.setFilters({
                    clinic_id : val
                })
            }
        },
    },
};
