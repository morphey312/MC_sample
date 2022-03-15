import PriceSetRepository from '@/repositories/price/set';

export default {
    data() {
        return {
            loading: true,
            activeTab: null,
            tabs: [],
            availableClinics: [],
            headerClinicList: null,
            displayTable: false
        }
    },
    computed: {
        activePriceSet() {
            let tab = this.tabs.find(t => t.value === this.activeTab);
            return tab ? tab.id : null;
        },
    },
    mounted() {
        this.initInsurers();
    },
    methods: {
        switchTab() {
            this.headerClinicList = null;
            this.availableClinics = null;
            this.syncFilters({
                set_id: this.activePriceSet,
                clinic: [],
            });
        },
        initInsurers() {
            this.loading = true;
            let priceSet = new PriceSetRepository();
            priceSet.fetchList(this.getPriceSetFilters()).then(response => {
                this.tabs = response.map(s => {
                    return {
                        id: s.id,
                        value: s.owner.short_name,
                        owner_id: s.owner_id,
                    }
                });
                this.setDefaultTab();
                this.loading = false;
            });
        },
        setDefaultTab() {
            let initialInsurer = this.tabs[0];
            if (initialInsurer) {
                this.activeTab = initialInsurer.value;
                this.filters.set_id = initialInsurer.id;
                this.filters.has_price.set_id = initialInsurer.id;
            }
        },
        getPriceSetFilters() {
            return _.onlyFilled({
                insurer_has_price: true,
            });
        },
        changeFilters(filters) {
            this.headerClinicList = this.availableClinics;
            this.setActiveTab(filters);
            this.filters = {...filters};
            this.displayTable = true;
        },
        clearFilters() {
            this.filters = {set_id: this.activePriceSet};
            this.displayTable = false;
        },
        syncFilters(updates) {
            this.$refs.filter.sync(updates);
        },
        setActiveTab(filters) {
            let tab = this.tabs.find(t => t.id === filters.set_id);
            if (tab) {
                this.activeTab = tab.value;
            }
        },
        changeAvailableclinics(list) {
            this.availableClinics = list;
            if (this.headerClinicList === null) {
                this.headerClinicList = list;
            }
        },
    },
    watch: {
        ['filters.set_id']() {
            // this.headerClinicList = null;
        }
    },
}
