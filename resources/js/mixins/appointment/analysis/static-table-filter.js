export default {
    data() {
        return {
            filter: {
                laboratory_code: null,
                laboratory_name: null,
                name: null,
                clinic_code: null,
            },
        }
    },
    methods: {
        filterResults(results, filters) {
            if (!_.isEmpty(filters) && results.length !== 0) {
                Object.keys(filters).forEach((key) => {
                    results = results.filter((item) => {
                        let field = (key === 'clinic_code') ? item.analysis.clinic.code : item.analysis[key];
                        return field.toLowerCase().includes(filters[key].toLowerCase());
                    });
                });
            }
            return results;
        },
        refresh() {
            this.$refs.table.refresh();
        },
        syncFilters(updates) {
            this.filter = {...this.filter, ...updates};
        },
    },
}