<template>
    <section class="p-0 shrinkable-tabs">
        <div class="content-wrapper">
            <section class="grey">
                <prices-filter
                    ref="filter"
                    :initial-state="filters" />
            </section>
           
        </div>
    </section>
</template>

<script>
import PricesFilter from './changed-prices/Filter.vue';
import CONSTANTS from "@/constants";

export default {
    components: {
        PricesFilter,
    },
    data() {
        return {
           filters: {
                set_type: CONSTANTS.PRICE.SET_TYPE.BASE,
            },
        }
    },
};
  
</script>
