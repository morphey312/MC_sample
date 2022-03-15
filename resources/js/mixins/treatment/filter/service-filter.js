import SpecializationRepository from '@/repositories/specialization';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import CONSTANTS from '@/constants';

export default {
    data() {
        return {
            pickerOptionsTo: {
                disabledDate: this.checkDateTo,
                firstDayOfWeek: 1,
            },
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited(this.permissions),
            }),
            destinations: new PaymentDestinationRepository({
                limitClinics: this.$isAccessLimited(this.permissions),
            }),
            primaryClinicId: this.$store.state.user.primaryClinicId ?
                [this.$store.state.user.primaryClinicId] : this.$store.state.user.clinics,
        };
    },
    methods: {
        initFilter(fromState = {}) {
            let clinic = [];

            if (this.$isAccessLimited('service-prices')) {
                clinic = this.primaryClinicId;
            }

            let today = this.$moment().format('YYYY-MM-DD');
            this.filter = {
                clinic: clinic,
                name: null,
                name_i18n: null,
                specialization: null,
                payment_destination: null,
                base: null,
                disabled: null,
                price_exists: CONSTANTS.PRICE.EXISTENS.HAS_PRICE_WITH_PERIOD,
                price_date_from: today,
                price_date_to: today,
                ...this.convertPriceParams(fromState),
            };
        },
        checkDateTo(date) {
            if (this.filter.price_date_to && this.$moment(date).isBefore(this.filter.price_date_from)) {
                return true;
            }
            return false;
        },
        changed(filters = {}) {
            if (this.$isAccessLimited('payments') && (!filters.clinic || filters.clinic.length === 0)) {
                filters.clinic = this.primaryClinicId;
            }
            this.$emit('changed', this.makeFilters(filters));
        },
    },
    watch: {
        ['filter.clinic'](val) {
            this.specializations.setFilters(_.onlyFilled({active_clinic: val}));
            this.destinations.setFilters(_.onlyFilled({
                clinic: val,
                disabled: 0
            }));
        },
        ['filter.price_date_from'](val) {
            this.filter.price_date_to = val;
        },
    },
};
