<template>
    <result-list
        ref="table"
        :filters="filters"
        @loaded="refreshed"
        @selection-changed="setActiveItem"
        @header-filter-updated="syncFilters">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$canUpdate('analysis-results')"
                :disabled="activeItem === null"
                type="primary"
                @click="edit">
                {{ __('Изменить статус') }}
            </el-button>
        </div>
    </result-list>
</template>
<script>
import ResultList from './AssignedList.vue';
import FormAssignedEdit from './FormAssignedEdit.vue';

export default {
    components: {
        ResultList,
    },
    props: {
        filters: Object,
    },
    data() {
        return {
            activeItem: null,
            selectedTo: [],
        }
    },
    methods: {
        edit() {
            let rows = this.$refs.table.getSelectedRows().filter((row) => {
                return this.$canManage('analysis-results.update', [row.clinic_id]);
            });

            if (rows.length == 0) {
                return this.$error(__('Вы не можете редактировать выбранные анализы'));
            }

            this.$modalComponent(FormAssignedEdit, {rows},
            {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, list) => {
                    dialog.close();
                    this.$info(__('Анализы были успешно обновлены'));
                    this.refresh();
                    this.selectedTo = list;
                },
            },
            {
                header: __('Изменить статус выбранным анализам'),
                width: '1150px',
            });
        },
        setActiveItem(selection) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
        },
        syncFilters(updates) {
            this.$emit('syncFilters', updates);
        },
        getManageTable() {
            return this.$refs.table.$refs.table;
        },
        refresh() {
            this.getManageTable().refresh();
        },
        refreshed() {
            if (this.selectedTo.length !== 0) {
                this.getManageTable().updateSelection((item) => {
                    return this.selectedTo.indexOf(item.id) !== -1;
                });
                this.selectedTo = [];
            }
        },
    }
}
</script>
