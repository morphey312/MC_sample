export default {
    props: {
        clinicLabelMap: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            showChart: false,
        }
    },
    mounted() {
        this.$watch('loading', (val) => {
            this.showChart = !val;
        });
    },
    methods: {
        getLabelOptions() {
            return {
                show: true,
                position: 'top',
                align: 'center',
                verticalAlign: 'middle',
                formatter: '{c}',
                fontSize: 12,
                color: '#000',
                rich: {
                    name: {
                        a: {
                            color: '#000',
                        },
                    },
                }
            };
        },
        getTooltip() {
            return {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross',
                    crossStyle: {
                        color: '#999'
                    }
                }
            };
        },
        getDataZoom() {
            return [
                {
                    type: 'slider',
                    show: true,
                    start: 0,
                    end: 100,
                    backgroundColor: '#C0E2FF',
                    top: 0,
                },
                {
                    type: 'inside',
                    start: 0,
                    end: 100,
                },
            ];
        },
        getFormattedDate(date, format) {
            return this.$formatter.dateFormat(date, format);
        },
        getAxisLabel(index, label = '') {
            let name = this.clinicLabelMap[label] || label;
            return this.isOdd(index) ? this.newLineLabel(name) : name;
        },
        isOdd(index) {
            return index % 2 > 0;
        },
        newLineLabel(label) {
            return '\n' + label;
        },
        getChartDataKeys(periodList, altPeriodList = [], key = 'itemName') {
            return _.uniq([...periodList, ...altPeriodList].map(c => c[key])).sort();
        },
    }
}