<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :flex-height="true"
        :enable-loader="permissions === null"
        @header-filter-updated="syncFilters">
        <template
            slot="value"
            slot-scope="props">
            <expandable-cell 
                :data="props.rowData"
                @toggle="groupToggled"/>
        </template>
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import PermissionRepository from '@/repositories/permission';
import CollectionFilter from '@/services/collection-filter';
import ExpandableCell from '@/components/general/table/ExpandableCell.vue';
import ExpandableMixin from '@/mixins/table/expandable';

export default {
    mixins: [
        ExpandableMixin,
    ],
    components: {
        ExpandableCell,
    },
    props: {
        role: Object,
    },
    data() {
        return {
            permissions: null,
            repository: new ProxyRepository(() => {
                if (this.permissions === null) {
                    let repository = new PermissionRepository();
                    return repository.fetch({role: this.role.id}, [], null, 1, 1000).then((result) => {
                        this.permissions = result.rows;
                        return Promise.resolve({
                            rows: this.filterResults(this.permissions),
                        });
                    });
                }
                return Promise.resolve({
                    rows: this.filterResults(this.permissions),
                });
            }),
            fields: [
                {
                    name: 'value',
                    title: __('Название'),
                    width: '100%',
                    filter: true,
                },
            ],
            filters: {
            },
        };
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        filterResults(results) {
            let filter = new CollectionFilter();
            filter.whereContains(this.filters);
            return this.makeGrops(filter.filter(results), this.expandedGroups);
        },
        groupToggled(groupId) {
            this.toggleGroup(groupId, this.expandedGroups);
            this.$refs.table.refresh();
        },
    },
}
</script>