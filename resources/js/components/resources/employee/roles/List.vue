<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot="name"
            slot-scope="props" >
            <div class="has-icon">
                <span class="ellipsis">
                    {{ props.rowData.name }}
                </span>
                <svg-icon 
                    name="info-alt" 
                    class="icon-tiny icon-grey"
                    @click.stop="showDetails(props.rowData)" />
            </div>
        </template>
        <template slot="spacer">
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import RoleRepository from '@/repositories/role';
import RoleDetails from './Details.vue';
import EditGroupLink from './EditGroupLink.vue';

export default 
{
    data() {
        return {
            repository: new RoleRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '30%',
                    filter: true,
                },
                {
                    name: 'spacer',
                    title: '',
                    width: '70%',
                    dataClass: 'no-dash',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {
            },
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        showDetails(role) {
            this.$modalComponent(RoleDetails, {
                role,
            }, {}, {
                header: role.name,
                width: '700px',
                customClass: 'no-footer',
                headerAddon: {
                    component: EditGroupLink,
                    eventListeners: {
                        click: (dialog) => {
                            dialog.close();
                            this.$emit('edit', role);
                        }
                    }
                }
            });
        },
    },
}
</script>