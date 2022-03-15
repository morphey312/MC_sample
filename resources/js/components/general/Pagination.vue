<template>
    <div 
        class="pagination-wrapper"
        :class="{'pagination-right': slotsMissing || !enablePagination}">
        <slot />
        <div class="pagination-info-and-pagination" v-show="showPageSizeSelector">
            <div class="pagination-info">
                <span>{{ first }}</span> - <span>{{ last }}</span> {{ __('из') }} {{ total }}
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
import { VuetablePagination } from 'vuetable-2';
import TableSettings from './TableSettings.vue';

export default {
    components: {
        VuetablePagination,
        TableSettings,
    },
    props: {
        paginationData: {
            type: Object,
        },
        pageSizeOptions: {
            type: Array,
            default: () => [
                25,
                50,
                75,
                100,
            ],
        },
        visibleFields: {
            type: Array,
        },
        fields: {
            type: Array,
        },
        showTableSettings: {
            type: Boolean,
            default: true,
        },
        enablePagination: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            hasData: false,
            first: 0,
            last: 0,
            total: 0,
            pageSize: 0,
            currentPage: 0,
            last_page: 0,
        };
    },
    computed: {
        showPageSizeSelector() {
            return this.hasData && this.total > 0;
        },
        slotsMissing() {
            return _.isEmpty(this.$slots);
        },
    },
    methods: {
        fieldsChanged(fields) {
            this.$emit('fieldsChanged', fields);
        },
        pageChanged(page) {
            this.$emit('pageChanged', page);
        },
        pageSizeChanged(val) {
            this.pageSize = val;
            this.$emit('pageSizeChanged', val);
        },
        setPaginationData(data) {
            this.$refs.pagination.setPaginationData(data);
            this.hasData = true;
            this.first = data.from;
            this.last = data.to;
            this.total = data.total;
            this.pageSize = data.per_page;
            this.currentPage = data.current_page;
            this.last_page = data.last_page;
        },
    },
};
</script>