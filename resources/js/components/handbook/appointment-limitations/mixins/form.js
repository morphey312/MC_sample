export default {
    data() {
        return {
            activeTab: 'info',
            error: '',
            totalPercents: 0,
            maxPercent: 100,
        }
    },
    computed: {
        invalidPercents() {
            return this.totalPercents > this.maxPercent;
        },
        blankPercents() {
            return this.totalPercents === 0;
        },
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        saveModel() {
            if (this.model.doctors.length === 0) {
                return this.$error(__('Выберите ограничение для врачей'));
            }
            this.setTotalPercents();

            if (this.totalPercents > 100) {
                return this.overPercentError();
            }

            if (this.model.isNew() && this.totalPercents == 0) {
                return this.zeroPercentError();
            }
            return this.save();
        },
        save() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$info(__('Данные ограничения успешно сохранены'));
                this.completed();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        isInvalidPercent() {
            return this.getTotalPercents() > 100;
        },
        checkPercent() {
            this.setTotalPercents();
            this.error = this.invalidPercents ? this.percentError() : '';
        },
        setTotalPercents() {
            if(this.model.doctors.length === 0) {
                return false;
            }
            
            this.totalPercents = 0;
            return this.model.doctors.forEach((doctor) => {
                this.totalPercents += Number(doctor.limitation_percent);
            });
        },
        overPercentError() {
            return this.$error(this.percentError());
        },
        zeroPercentError() {
            return this.$error(__('Задайте процент записи по врачам'));
        },
        percentError() {
            return __('Суммарный максимальный процент записи по всем врачам ограничения более 100');
        },
    }
}