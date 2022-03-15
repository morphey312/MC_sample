<template>
    <page
        :title="__('Анализы из MC+Lab')"
        v-loading="loading"
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
                    :laboratories="laboratories"
                    permissions="analyses"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <analyses-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button
                        :text="__('Связать')"
                        :disabled="activeItem === null || activeItem.analysis != null"
                        @click="connect"/>
                    <form-button
                        :text="__('Добавить')"
                        :disabled="activeItem === null || activeItem.analysis != null"
                        icon="plus"
                        @click="create"/>
                 </div>
            </analyses-list>
        </section>
    </page>
</template>

<script>
import AnalysesList from './analyses/candidates/List.vue';
import EditAnalysis from './analyses/candidates/Edit.vue';
import AnalysesFilter from './analyses/candidates/Filter.vue';
import ManageMixin from '@/mixins/manage';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import Analyses from  '@/models/analysis';

export default {
    name:'AnalysesCandidate',
    mixins: [ManageMixin],
    components: {
        AnalysesList,
        AnalysesFilter,
    },
    data(){
        return {
            loading: false,
            displayFilter: true,
            laboratories: [],
        }
    },
    mounted() {
       this.getLaboratories();
    },
    methods: {
        getDefaultFilters() {
            return {
                has_analysis: false,
            };
        },
        getLaboratories() {
            let repo = new LaboratoryRepository({filters: {hasExternal: true}});
            repo.fetchList().then(response => {
                this.laboratories = response;
            });
        },
        create() {
            this.$confirm(__('Вы уверены, что хотите создать новый анализ?'), () => {
                let analysis = new Analyses({
                    name: this.activeItem.name,
                    code: this.activeItem.code,
                    lab_analysis_id: this.activeItem.id,
                    clinics: this.clinics,
                    laboratory_id: this.laboratories[0].id,
                });
                analysis.save().then((response) => {
                    this.$info(__('Анализ успешно создан'));
                    this.refresh();
                }).catch((e) => {
                    this.$error(__('Не удалось создать анализ'));
                });
            });
        },
        connect() {
             this.$modalComponent(EditAnalysis, {
                model: this.activeItem,
                laboratories: this.laboratories,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                connect: (dialog, analysis) => {
                    analysis.save().then(() => {
                        this.$info(__('Анализ успешно связан'));
                        dialog.close();
                        this.refresh();

                    }).catch((e) => {
                        this.$error(__('Не удалось создать анализ'));
                    });
                },
            }, {
                header: __('Связать анализ'),
                width: '900px',
                customClass: 'no-footer padding-0',
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
