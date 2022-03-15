<template>
    <div 
        v-show="hasData && total > 0"
        class="pagination-wrapper">
        <slot />
        <div class="pagination-info-and-pagination">
            <div class="pagination-info">
                <span>{{ amount }}</span> {{ __('записей') }} {{ __('из') }} {{ total }}
            </div>
            <vuetable-pagination
                ref="pagination"
                @vuetable-pagination:change-page="pageChanged" />
            <table-settings 
                v-if="showTableSettings"
                class="ml-10"
                :fields="fields"
                :visible-fields="visibleFields"
                @changed="fieldsChanged" />
        </div>
    </div>
</template>

<script>
import Pagination from '@/components/general/Pagination.vue';

export default {
    extends: Pagination,
    computed: {
        amount() {
            return this.vuetable && this.vuetable.tableData ? this.vuetable.tableData.length : 0;
        },
        vuetable() {
            return this.$parent.$refs.vuetable;
        },
    }
};
</script>
