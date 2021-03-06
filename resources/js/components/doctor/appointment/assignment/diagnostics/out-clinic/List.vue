<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        table-height="auto"
        @header-filter-updated="updateList">
        <template
            slot="add-selection"
            slot-scope="props" >
            <div class="pr-20">
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Выбрать диагностику') }}</a>
            </div>
        </template>
        <template
            slot="remove"
            slot-scope="props" >
            <span
                @click="remove(props.rowData, props.rowIndex)">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import OutclinicDiagnostic from "@/models/employee/outclinic-diagnostic";

export default {
    props: {
        rows: {
            type: Array,
            default: () => []
        },
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, page, limit}) => {
               return this.getRows(sort);
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Диагностика вне клиники'),
                    filter: true,
                    filterField: 'name',
                    sortField: 'name',
                },
                {
                    name: 'add-selection',
                    title: '',
                    width: "270px",
                    dataClass: 'text-right',
                },
                ...(this.readonly ? [] : [{
                    name: 'remove',
                    title: '',
                    width: "30px",
                    dataClass: 'no-ellipsis no-dash',
                }]),
            ],
            filter: {
               name: null,
            },
            filteredResults: this.rows,
            initialSortOrder: [
                {field: 'name', direction: 'desc'},
            ],
        }
    },
    watch: {
        ['rows']() {
            this.filteredResults = this.rows;
            this.refresh();
        },
    },
    methods: {
        getRows(sort) {
            return Promise.resolve({
                rows: this.sortResults(sort),
            });
        },
        refresh() {
          this.$refs.table.refresh();
        },
        updateList(updates) {
            this.syncFilters(updates);
            this.filteredResults = this.filterResults(this.rows,  _.onlyFilled(this.filter));
            this.refresh();
        },
        syncFilters(updates) {
            this.filter = {...this.filter, ...updates};
        },
        sortResults(sort) {
            if (!sort[0]) {
                return this.filteredResults;
            }
            return _.orderBy(this.filteredResults, sort[0].field, sort[0].direction);
        },
        filterResults(results, filters) {
            if (!_.isEmpty(filters) && results.length !== 0) {
                Object.keys(filters).forEach((key) => {
                    results = results.filter((item) => {
                        let field = item[key];
                        return field.toLowerCase().includes(filters[key].toLowerCase());
                    });
                });
            }
            return results;
        },
        toggleSelection(row) {
            this.$emit('selection-changed', {...row});
        },
        remove(row, index) {
            let model = new OutclinicDiagnostic({...row, is_deleted: true});
            model.save().then(() => {
                this.$emit('specialization-deleted');
            });
        },
    }
}
</script>
