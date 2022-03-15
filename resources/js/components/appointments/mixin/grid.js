import CONSTANTS from '@/constants';

export default {
    data() {
        return {
            params: [],
            timeRange: [],
        };
    },
    computed: {
        showSchedule() {
            return this.params.length != 0;
        },
    },
    methods: {
        getData(initialCollapse = false) {
            this.$nextTick(() => {
                this.$refs.grid.getSchedules().then(() => {
                    if (initialCollapse && this.$refs.buttons) {
                        this.$refs.buttons.toggleCollapse();
                    }
                });
            });
        },
        addDaySheets({params, showAdjacent}) {
            if (_.isEmpty(params)) {
                return;
            }
            
            let initialCollapse = !this.showSchedule;
            this.updateParams(params, showAdjacent);
            this.getData(initialCollapse);
        },
        updateParams(params, showAdjacent = false) {
            this.params = [...this.uniqueParams(params, showAdjacent), ...this.params];
        },
        updateParam({oldVal, newVal}) {
            let index = this.params.findIndex((param) => {
                return param.date == oldVal.date &&
                       param.clinic_id == oldVal.clinic_id && 
                       param.day_sheet_owner_id == oldVal.doctor.id && 
                       param.day_sheet_owner_type == oldVal.day_sheet_owner_type && 
                       param.workspace_id == oldVal.workspace_id;
            });

            if (index !== -1) {
                this.params.splice(index, 1, newVal);
            }
        },
        uniqueParams(params, showAdjacent = false) {
            this.deleteAdjacentAttribute();
            let newParams = [];

            params.forEach((param) => {
                let index = _.findIndex(this.params, param);

                if (showAdjacent) {
                    param.adjacent = showAdjacent;
                }

                if (index !== -1) {
                    this.params.splice(index, 1);
                }
                newParams.push(param);
            });
            return newParams;
        },
        deleteAdjacentAttribute() {
            this.params.forEach((param) => delete param.adjacent); 
        },
        createTimeList({start, end}) {
            if (this.timeRange.length > 0) {
                this.timeRange = [];
            }

            let dayStart = this.$moment().set('hour', start.substring(0,2)).set('minute', start.substring(3,5));
            let dayEnd = this.$moment().set('hour', end.substring(0,2)).set('minute', end.substring(3,5));

            do {
                this.timeRange.push(dayStart.format("HH:mm"));
                dayStart.add(CONSTANTS.SCHEDULE_TIME_STEP, 'minutes');
            } while (dayStart < dayEnd)
        },
        removeParam(data) {
            let paramList = [...this.params];
            this.params = _.reject(paramList, data);
        },
        removeAllParams() {
            this.params = [];
        },
        updateParamsDate(date) {
            let paramList = [...this.params];
            this.params = paramList.map((param) => {
                param.date = date;
                return param;
            });
            this.getData();
        },
    },
}