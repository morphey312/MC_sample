export default {
    data() {
        return {
            activeTab: 'encounter',
            tabConditions: false,
            tabDiagnostics: false,
            tabProcedures: false,
        };
    },
    watch: {
        activeTab(val) {
            switch(val) {
                case 'conditions':
                    this.tabConditions = true;
                    break;
                case 'diagnostics':
                    this.tabDiagnostics = true;
                    break;
                case 'procedures':
                    this.tabProcedures = true;
                    break;
            }
        }
    }
}
