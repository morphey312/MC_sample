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
                :page-size-options="pageSizeOptions"
                :visible-fields="visibleFields"
                :page-size="pageSize"
                :show-page-size-selector="showPageSizeSelector"
                @changed="fieldsChanged"
                @pageSizeChanged="pageSizeChanged" />
        </div>
    </div>
</template>

<script>
import Pagination from '@/components/general/Pagination.vue';

export default {
    extends: Pagination,
    props: {
        pageSizeOptions: {
            type: Array,
            default: () => [
                25,
                50,
                75,
                100,
                200,
                500,
            ],
        },
    },
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
