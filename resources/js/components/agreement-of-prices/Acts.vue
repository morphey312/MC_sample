<template>
    <section class="p-0 shrinkable-tabs">
        <div class="content-wrapper">
            <section class="grey">
                <act-filter
                    ref="filter"
                    :initial-state="filters"
                    permissions="services"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
            <section class="grey-cap p-20 shrinkable">
                <act-list
                    v-if="displayTable"
                    ref="table"
                    :filters="filters"
                    @selection-changed="setActiveItem"
                    @loaded="refreshed"
                    @header-filter-updated="syncFilters">
                    <div class="buttons" slot="buttons">
                        <form-button
                            :text="__('Экспорт в Excel')"
                            icon="download"
                            :disabled="activeItem === null || !$canManage('price-agreement-acts.export')"
                            @click="exportAct()" />
                        <form-button
                            v-if="$canUpdate('price-agreement-acts')"
                            :disabled="activeItem === null || !canManage()"
                            type="primary"
                            :text="__('Редактировать')"
                            @click="edit" />
                        <form-button
                            v-if="$can('price-agreement-acts.approve')"
                            :disabled="!checkActStatus()"
                            type="primary"
                            :text="__('Согласовать')"
                            @click="approve" />
                        <form-button
                            v-if="$canDelete('price-agreement-acts')"
                            :disabled="!checkActStatus()"
                            :text="__('Не согласовать')"
                            @click="remove" />
                    </div>
                </act-list>
            </section>
        </div>
    </section>
</template>

<script>
import ActFilter from './ActFilter.vue';
import ActList from './ActList.vue';
import FormEdit from './FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import CONSTANTS from '@/constants';
import setStartDatePrices from './setStartDatePrices.vue';
import ExportXLSXMixin from './mixins/export-xlsx';
import * as analysisGenerator from './generators/analysis';
import * as servicesGenerator from './generators/services';
import AnalysesMixin from './mixins/analyses';
import ServiceMixin from './mixins/service';
import Price from '@/models/price-agreement-act';

export default {
    components: {
        ActFilter,
        ActList
    },
    mixins: [
        ManageMixin,
        AnalysesMixin,
        ServiceMixin,
        ExportXLSXMixin,
    ],
    data(){
        return {
            model: null,
            displayTable: false,
            fileGenerator: null,
            exportFields: [],
        }
    },
    methods: {
        exportAct() {
            this.model = new Price({id: this.activeItem.id})
            this.fileGenerator = this.activeItem.type === CONSTANTS.PRICE_AGREEMENT_ACT.TYPE.ANALYSIS ? analysisGenerator : servicesGenerator;
            let exportFields = this.activeItem.type === CONSTANTS.PRICE_AGREEMENT_ACT.TYPE.ANALYSIS ? this.analysesExportFields : this.serviceExportFields;
            this.exportExcel(__('Записи на прием'), exportFields);
        },
        checkActStatus() {
            return (this.activeItem && this.activeItem.status === CONSTANTS.PRICE_AGREEMENT_ACT.STATUSES.IN_WORK);
        },
        canManage() {
            if (this.$can('price-agreement-acts.update')) {
                return true
            } else if (this.$can('price-agreement-acts.update-clinic')) {
                return _.intersection(this.activeItem.clinic_ids, this.$store.state.user.clinics).length > 0
            } else {
                return false
            }
        },
        getModalOptions() {
            return {
                editForm: FormEdit,
                editHeader: __('Изменить акт согласования прайсов'),
                width: '1300px',
            };
        },
        approve() {
            this.$modalComponent(setStartDatePrices, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                save: (dialog, date) => {
                    dialog.close();
                    this.approveTheActWithDate(date);
                }
            }, {
                header: __('Задать дату действия тарифов'),
                width: '400px',
            });
        },
        approveTheActWithDate(date) {
            this.activeItem.approve(date).then(() => {
                this.refresh()
                this.$info(__('Акт согласован успешно'));
            }).catch((e) => {
                this.$warning(__('Что-то пошло не так'));
            });
        },
        remove() {
            this.$confirm(__('Вы дейстельно хотите удалить акт согласования цен?'), () => {
                this.activeItem.changeStatus(CONSTANTS.PRICE_AGREEMENT_ACT.STATUSES.NOT_AGREED).then(() => {
                    this.refresh()
                    this.$info(__('Статус акта изменен'));
                }).catch((e) => {
                    this.$warning(__('Что-то пошло не так'));
                });
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
    }
}
</script>
