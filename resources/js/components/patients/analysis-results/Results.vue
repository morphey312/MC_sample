<template>
    <result-list
        ref="table"
        :filters="filters"
        v-loading="loading"
        @loaded="refreshed"
        @selection-changed="setActiveItem"
        @header-filter-updated="syncFilters">
        <div class="buttons" slot="buttons">
            <el-button 
                v-if="$can('analysis-results.submit-result')"
                @click="sendResults">
                {{ __('Отправить пациенту') }}
            </el-button>
            <form-button 
                :text="__('Экспорт в Excel')"
                icon="download"
                @click="exportExcel(__('Результаты анализов'))" />
            <el-button 
                v-if="$can('analysis-results.submit-result')"
                :disabled="selectedTo.length < 2"
                @click="attachResult">
                {{ __('Прикрепить результат') }}
            </el-button>
        </div>
    </result-list>
</template>
<script>
import ResultList from './ResultList.vue';
import FormResultEdit from './FormResultEdit.vue';
import ResultRepository from '@/repositories/analysis/result';
import * as resultGenerator from './generators/results';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import SendResultMixin from './mixins/send-result';

export default {
    mixins: [
        ExportXLSXMixin,
        SendResultMixin,
    ],
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
            reportRepository: new ResultRepository(),
            loading: false,
            fileGenerator: resultGenerator,
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

            this.$modalComponent(FormResultEdit, {rows}, 
            {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, list) => {
                    dialog.close();
                    this.$info(__('Анализы пациентов были успешно обновлены'));
                    this.refresh();
                    this.selectedTo = list;
                },
            },
            {
                header: __('Изменить дату сдачи выбранным анализам'),
                width: '1150px',
            });
        },
        setActiveItem(selection) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
            this.selectedTo = selection;
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