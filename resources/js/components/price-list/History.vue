<template>
    <div class="flex-content">
        <prices-list
            :filters="filters"
            ref="table"
            @header-filter-updated="syncFilters">
        </prices-list>
    </div>
</template>

<script>
import PricesList from '@/components/treatment/prices/List.vue';
import CONSTANT from '@/constants';

export default {
    components: {
        PricesList,
    },
    props: {
        serviceId: [Number, String],
        serviceType: String,
        setType: String,
        clinics: Array,
    },
    data() {
        let filters = {
            clinic: this.clinics,
            set_type: this.setType,
        };

        if (this.serviceType == CONSTANT.PRICE.SERVICE_TYPE.ANALYSIS) {
            filters.analysis = this.serviceId;
        } else {
            filters.service = this.serviceId;
        }

        return {filters};
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
}
</script>
