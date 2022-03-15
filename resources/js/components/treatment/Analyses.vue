<template>
    <page
        :title="__('Анализы')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey filter">
                <analyses-filter
                    ref="filter"
                    :initial-state="filters"
                    permissions="analyses"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <analyses-list
                v-if="displayTable"
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$canCreate('analyses')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                     <form-button 
                        v-if="$canUpdate('analyses')"
                        :disabled="activeItem === null || !$canManage('analyses.update', activeItem.clinic_ids)"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button 
                        v-if="$canAccess('analysis-prices')"
                        :disabled="activeItem === null"
                        :text="__('Тарифы')"
                        icon="dollar-alt"
                        @click="showPrices" />
                    <form-button 
                        v-if="$canDelete('analyses')"
                        :disabled="activeItem === null || !$canManage('analyses.delete', activeItem.clinic_ids)"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                    <form-button 
                        v-if="$can('action-logs.access')"
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog" />
                </div>
            </analyses-list>
        </section>
    </page>
</template>

<script>
import AnalysesList from './analyses/List.vue';
import CreateAnalysis from './analyses/Create.vue';
import EditAnalysis from './analyses/Edit.vue';
import AnalysesFilter from './analyses/Filter.vue';
import ManageMixin from '@/mixins/manage';
import AnalysisLog from '@/components/action-log/Analysis.vue';

export default {
    name:'Analyses',
    mixins: [ManageMixin],
    components: {
        AnalysesList,
        AnalysesFilter,
    },
    data(){
        return {
            displayFilter: true,
            displayTable: false
        }
    },
    methods: {
        showPrices() {
            this.$router.push({name: 'analyses-prices', params: {id: this.activeItem.id}});
        },
        getDefaultFilters() {
            let today = this.$moment().format('YYYY-MM-DD');
            return {
                disabled: 0,
                has_price: {
                    from: today,
                    to: today,
                },
            };
        },
        getModalOptions() {
            return {
                createForm: CreateAnalysis,
                editForm: EditAnalysis,
                createHeader: __('Добавить анализ'),
                editHeader: __('Изменить анализ'),
                width: '700px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот анализ?'),
                deleted: __('Анализ был успешно удален'),
                created: __('Анализ был успешно добавлен'),
                updated: __('Анализ был успешно обновлен'),
            };
        },
        showLog() {
            this.$modalComponent(AnalysisLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения анализа'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        clearFiltersAndHideTable() {
            this.displayTable = false;
            this.clearFilters();
        },
        changeFiltersAndShowTable(filters) {
            this.changeFilters(filters);
            this.displayTable = true;
        },
    },
};
</script>
