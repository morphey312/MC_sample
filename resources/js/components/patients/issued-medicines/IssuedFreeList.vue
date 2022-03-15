<template>
    <manage-table
        ref="table"
        v-loading="loading"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :scopes="scopes"
        :filters="filters"
        :flex-height="true"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
        <template
            slot="to_issue"
            slot-scope="props">
            {{ getToIssue(props.rowData) }}
        </template>
        <template slot="footer-top">
            <div class="buttons">
                <el-button
                    v-if="$can('action-logs.assigned-issued-meds')"
                    :disabled="activeItem === null"
                    @click="showLog(activeItem)">
                    {{ __('Операции') }}
                </el-button>
                <el-button @click="exportExcel(__('Медикаменты'))">
                    {{ __('Экспорт в Excel') }}
                </el-button>
            </div>
        </template>
    </manage-table>
</template>

<script>
import IssueListMixin from '@/components/patients/cabinet/issued-medicines/mixins/issue-list';
import ListMixin from './mixin/list';

export default {
    mixins: [
        IssueListMixin,
        ListMixin,
    ],
    methods: {
        getFilters(filters){
            return {
                ...filters,
                is_free: true,
            }
        },
    }
}
</script>
