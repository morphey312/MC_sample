export default {
    props: {
        minDate: {
            type: [String, Date],
        },
        maxDate: {
            type: [String, Date],
        },
    },
    data() {
        return {
            pickerOptions: {
                disabledDate: this.checkDisabledDate,
                firstDayOfWeek: 1,
            },
        };
    },
    methods: {
        checkDisabledDate (date) {
            if (this.minDate && this.$moment(date).isBefore(this.minDate)) {
                return true;
            }
            if (this.maxDate && this.$moment(date).isAfter(this.maxDate)) {
                return true;
            }
            return false;
        },
    },
};