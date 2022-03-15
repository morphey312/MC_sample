<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        :filters="filters">
        <template
            slot="workspace_name"
            slot-scope="props">
            <div class="has-icon">
                <span class="ellipsis">
                    <a href="#" @click.prevent="selected(props.rowData)">
                        {{ props.rowData.name }}    
                    </a>
                </span>
                <svg-icon
                    v-if="$can('action-logs.access')"
                    :title="__('Операции')"
                    name="info-alt" 
                    class="icon-tiny icon-grey"
                    @click="showLog(props.rowData.id)" />
            </div>
        </template>
    </manage-table>
</template>

<script>
import WorkspaceRepository from '@/repositories/workspace';
import CONSTANTS from '@/constants';
import DaysheetLog from '@/components/action-log/DaySheet.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new WorkspaceRepository(),
            fields: [
                {
                    name: 'workspace_name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '30%',
                },
                {
                    name: 'workspace_clinics',
                    title: __('Клиника'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value, 'clinic_name');
                    }
                },
                
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: '40%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    }
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
        };
    },
    methods: {
        selected(workspace) {
            this.$router.push({
                name: 'day-sheet-schedule',
                params: {
                    id: workspace.id,
                    owner_type: CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE,
                }
            });
        },
        showLog(id) {
            this.$modalComponent(DaysheetLog, {
                id,
                category: CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения табеля кабинета'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    }
}
</script>
