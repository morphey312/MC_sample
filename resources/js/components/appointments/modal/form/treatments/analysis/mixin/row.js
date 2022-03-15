export default {
	props: {
        rows: {
            type: Array,
            default: () => []
        },
    },
    methods: {
        toggleSelection(row, index) {
            this.$emit('selection-changed', {row, index});
        },
    }
}