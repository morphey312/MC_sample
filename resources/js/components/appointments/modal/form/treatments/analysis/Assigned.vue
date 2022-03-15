<template>
    <section v-loading="loading">
        <el-checkbox v-model="filters.isShowAnalyzesWithoutTariff">
            {{ __('Показывать анализы без тарифа') }}
        </el-checkbox>
        <manage-table
            v-if="!loading"
            ref="table"
            :fields="fields"
            :filters="filters"
            :repository="repository"
            table-height="auto"
            :enable-pagination="false"
            :row-class="addRowClass"
            @selection-changed="checked"
        >
            <template slot="analysis-name" slot-scope="props">
                <analysis-title
                    :model="props.rowData"
                    :show-message="missingClinicPrice(props.rowData)" />
            </template>
            <template slot="cost" slot-scope="props">
                {{ $formatter.numberFormat(props.rowData.cost) }}
                <el-popover
                    v-if="Number(props.rowData.cost) !== Number(props.rowData.price)"
                    placement="bottom"
                    min-width="340px"
                    trigger="click">
                    <b>{{ __('Цена на дату записи отличается: {cost} грн.', {cost: $formatter.numberFormat(props.rowData.price)}) }}</b>
                    <template slot="reference">
                        <svg-icon name="info-alt" class="icon-tiny" />
                    </template>
                </el-popover>
            </template>
        </manage-table>
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="confirm"
                :disabled="disableSelect">
                {{ __('Выбрать') }}
            </el-button>
        </div>
    </section>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import AnalysisTitle from './AnalysisTitle.vue';
import AnalysisRepository from '@/repositories/analysis';
import CONSTANTS from '@/constants';

export default {
    components: {
        AnalysisTitle,
    },
    props: {
        analyses: Array,
        withPolicy: {
            type: Boolean,
            default: false,
        },
        insurancePolicy: {
            type: Object,
            default: null,
        },
        appointment: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            loading: true,
            rows: [],
            repository: new ProxyRepository(({filters}) => {
                let analyses = this.analyses;
                if (!filters.isShowAnalyzesWithoutTariff) {
                    analyses = analyses.filter((analysis) => {
                        return !this.missingClinicPrice(analysis)
                    });
                }
                return Promise.resolve({
                    rows: analyses,
                });
            }),
            fields: [
                {
                    name: '__checkbox',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    width: '34px',
                },
                {
                    name: 'analysis.laboratory_code',
                    title: __('Код лаборатории'),
                    width: '90px',
                },
                {
                    name: 'analysis.clinic.code',
                    title: __('Код клиники'),
                    width: '80px',
                },
                {
                    name: 'analysis-name',
                    title: __('Название анализов'),
                },
                {
                    name: 'clinic_name',
                    title: __('Клиника назначения'),
                    width: '110px',
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн'),
                    width: '80px',
                },
                {
                    name: 'analysis.clinic.duration_days',
                    title: __('Кол-во дней на анализ'),
                    width: '70px',
                },
                {
                    name: 'assigner_name',
                    title: __('Врач'),
                    width: '140px',
                },
                {
                    name: 'date_expected_pass',
                    title: __('Реком. Дата сдачи'),
                    width: '120px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                ...(this.withPolicy ? [
                {
                    name: 'by_policy',
                    title: __('Полис'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'franchise',
                    title: __('Фр-за, %'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                ] : [])
            ],
            filters: {
                isShowAnalyzesWithoutTariff: false
            }
        };
    },
    computed: {
        disableSelect() {
            return this.rows.length === 0;
        },
    },
    watch: {
        ['filters.isShowAnalyzesWithoutTariff'](val) {
            this.repository.setFilters({isShowAnalyzesWithoutTariff: val});
        },
    },
    mounted() {
        this.actualizePrices()
            .catch(() => {
                this.$error(__('Не удалось получить данные по тарифам'));
            }).finally(() => {
                this.loading = false;
            });
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        checked(rows = []) {
            this.rows = rows;
            let table = this.getTable();
            let tableData = table.getData();

            tableData.forEach(row => {
                if (this.missingClinicPrice(row)) {
                    table.unselectRow(row);
                    let index = this.rows.findIndex(id => id === row.id);
                    if (index != -1) {
                        this.rows.splice(index, 1);
                    }
                }
            });
        },
        getTable() {
            return this.$refs.table;
        },
        confirm() {
            let date = this.$moment().format('YYYY-MM-DD');
            let indexList = this.$refs.table.getSelectedRows();
            let list = this.$refs.table.getData().filter((item) => {
                return indexList.indexOf(item.id) !== -1;
            });

            if (this.insurancePolicy == null) {
                let policyAssignments = list.filter(item => item.by_policy);
                if (policyAssignments.length != 0) {
                    return this.$error(__('Анализ назначен по страховой, добавьте полис в запись'));
                }
            }

            if (list.filter(item => item.date_expected_pass && item.date_expected_pass !== date).length > 0) {
                this.$confirm(__('Внимание! Рекомендуемая дата сдачи выбранного анализа - не сегодня'), () => {
                    this.$emit('selected', list);
                });
            } else {
                this.$emit('selected', list);
            }
        },
        missingClinicPrice(row) {
            let price = row.active_prices.find(item => {
                let tariffClosingDate = item.date_to ? this.$moment(item.date_to.date, 'YYYY-MM-DD') : null;
                let appointmentDate = this.$moment(this.appointment.date, 'YYYY-MM-DD');
                if (tariffClosingDate !== null && appointmentDate.isAfter(tariffClosingDate)) {
                    return false;
                }
                return item.clinics.indexOf(this.appointment.clinic_id) !== -1;
            });
            return _.isVoid(price);
        },
        addRowClass(row, index) {
            return this.missingClinicPrice(row, index) ? 'checkbox-events-none' : '';
        },
        actualizePrices() {
            let insurerId = this.insurancePolicy 
                ? this.insurancePolicy.insurance_company_id 
                : null;
            let repo = new AnalysisRepository();
            let filters = {
                hasPrice: {
                    from: this.appointment.date,
                    to: this.appointment.date,
                    clinic: this.appointment.clinic_id,
                    set: [CONSTANTS.PRICE.SET_TYPE.BASE],
                },
                disabled: false,
                id: this.analyses.map((analysis) => analysis.analysis_id),
            };
            return repo.fetchListForAppointment(filters, insurerId ? {withInsurer: insurerId} : {})
                .then((response) => {
                    this.analyses.forEach((analysis) => {
                        let hasPrice = _.find(response, (row) => row.analysis_id === analysis.analysis_id);
                        if (hasPrice) {
                            analysis.active_prices = hasPrice ? hasPrice.prices : [];
                            let price = _.find(hasPrice.prices, (price) => {
                                return price.clinics.indexOf(this.appointment.clinic_id) !== -1
                                    && ((insurerId === null || !analysis.by_policy)
                                        ? price.set_type === CONSTANTS.PRICE.SET_TYPE.BASE
                                        : (price.set_type === CONSTANTS.PRICE.SET_TYPE.INSURANCE
                                            && price.set_owner.owner_id === insurerId));
                            });
                            if (price) {
                                // ? analysis.cost = price.cost; 
                                analysis.price = price.cost;
                                analysis.price_id = price.id;
                                analysis.analysis.price = price.cost;
                            }
                        } else {
                            analysis.active_prices = [];
                            // ? analysis.cost = '';
                        }
                    });
                });
        },
    }
}
</script>
