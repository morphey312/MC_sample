export default {
    methods: {
        getClinics() {
            return this.$refs.filter.clinics.fetchList();
        },
        getClinicName(clinicId) {
            return this.getClinics().then((list) => {
                let clinic = list.find(item => item.id == clinicId);
                return clinic ? clinic.value : '';
            })
        },
        getFileName(clinicName) {
            let dateSubstr = this.$formatter.dateFormat(this.filters.date_start, 'DD-MM-YYYY');
            if (this.filters.date_start != this.filters.date_end) {
                dateSubstr += '_' + this.$formatter.dateFormat(this.filters.date_end, 'DD-MM-YYYY')
            }
            return __('{clinic}_инкам__{date}.xlsx', {clinic: clinicName, date: dateSubstr});
        },
        subtractDate(date, period = 'months') {
            return this.$moment(date).subtract(1, period);
        },
    }
}