export default {
    props: {
        tableData: {
            type: [Object, Array],
            default: () => ({}),
        },
    },
    methods: {
        getHeight() {
            return window.innerHeight * 70 / 100;
        },
        addPercent(row, column, cellValue, index) {
            return (index == 0) ?  cellValue : (cellValue + '%');
        },
    },
}