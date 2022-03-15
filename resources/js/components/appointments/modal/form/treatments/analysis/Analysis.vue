<template>
    <div
        v-loading="loading"
        class="form-tab">
        <div class="add-assignments-btn">
            <svg-icon
                v-if="hasContent"
                name="calendar"
                class="icon-small icon-blue mr-10">
                <a
                    href="#"
                    @click.prevent="getPassDateModal">
                    {{ __('Поставить дату сдачи') }}
                </a>
            </svg-icon>
            <svg-icon
                v-if="patientHasAssignedAnalyses"
                name="catalogue"
                class="icon-small icon-blue mr-10">
                <a
                    href="#"
                    @click.prevent="checkExistsAnalysis">
                    {{ __('Есть назначенные анализы') }}
                </a>
            </svg-icon>
            <svg-icon
                v-if="model.analyses_results.length > 0 && !model.isNew() && $can('appointments.transfer-services')"
                name="arrow-right-alt"
                class="icon-small icon-blue mr-10">
                <a
                    href="#"
                    @click.prevent="transferToOtherAppointment">
                    {{ __('Перенести в другую запись') }}
                </a>
            </svg-icon>
            <svg-icon
                name="plus-alt"
                class="icon-small icon-blue"
                :disabled="analysisAddDisabled">
                <a
                    href="#"
                    :disabled="analysisAddDisabled"
                    @click.prevent="canSearchNewAnalysis">
                    {{ __('Добавить / Редактировать анализы') }}
                </a>
            </svg-icon>
        </div>
        <el-row>
            <template v-if="hasContent">
                <table class="vuetable ui blue unstackable celled table fixed text-left analysis-table-wrapper">
                    <div style="height:150px; overflow:auto;">
                        <table class="vuetable table bb-0">
                            <thead>
                                <th class="sticky-header">
                                    {{ __('Дата сдачи') }}
                                </th>
                                <th class="sticky-header">
                                    {{ __('Код лаборатории') }}
                                </th>
                                <th class="sticky-header">
                                    {{ __('Название лаборатории') }}
                                </th>
                                <th class="sticky-header">
                                    {{ __('Код клиники') }}
                                </th>
                                <th class="sticky-header">
                                    {{ __('Название анализов') }}
                                </th>
                                <th class="sticky-header">
                                    {{ __('Кол-во дней на анализ') }}
                                </th>
                                <th class="sticky-header">
                                    {{ __('Кол-во') }}
                                </th>
                                <th class="sticky-header text-right">
                                    {{ __('Стоимость, грн') }}
                                </th>
                                <th class="sticky-header">
                                    {{ __('Скидка') }}
                                </th>
                                <template v-if="showExtraFields">
                                    <th class="sticky-header">
                                        {{ __('Гарантия полиса') }}
                                    </th>
                                    <th class="sticky-header">
                                        {{ __('Франшиза, %') }}
                                    </th>
                                    <th class="sticky-header">
                                        {{ __('Гарант') }}
                                    </th>
                                </template>
                                <th class="sticky-header"></th>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in model.analyses_results"
                                    :key="row.analysis_id">
                                    <td
                                        width="12%"
                                        class="price-grid">
                                        <inline-datepicker
                                            v-model="row.date_pass"
                                            :disabled="!canDeleteDatePass(row)"
                                            :max-date="$moment(model.date).format('YYYY-MM-DD')"
                                        />
                                    </td>
                                    <td
                                        class="no-ellipsis"
                                        width="10%">
                                        {{ row.analysis.laboratory_code }}
                                    </td>
                                    <td class="no-ellipsis"
                                        width="10%">
                                        {{ row.analysis.laboratory_name }}
                                    </td>
                                    <td
                                        class="no-ellipsis"
                                        width="10%">
                                        {{ row.analysis.clinic.code }}
                                    </td>
                                    <td
                                        class="no-ellipsis text-select"
                                        width="25%">
                                        {{ row.analysis.name }}
                                    </td>
                                    <td
                                        class="no-ellipsis text-right"
                                        width="10%">
                                        {{ row.analysis.clinic.duration_days }}
                                    </td>
                                    <td
                                        class="no-ellipsis"
                                        width="10%">
                                        <el-input-number
                                            v-model="row.quantity"
                                            controls-position="right"
                                            :step="1"
                                            :disabled="(row.cost != 0 && row.cost == row.payed)"
                                            :min="1"
                                            class="text-right input-tiny"
                                            @change="calcModelPrice(row)" />
                                    </td>
                                    <td
                                        class="no-ellipsis text-right"
                                        width="10%">
                                        {{ row.cost }}
                                    </td>
                                    <td
                                        class="no-ellipsis"
                                        width="10%">
                                        <el-input-number
                                            v-model="row.discount"
                                            controls-position="right"
                                            :step="0.01"
                                            :min="0"
                                            :max="maxDiscount(row)"
                                            :disabled="row.by_policy == true"
                                            class="text-right input-tiny"
                                            @change="calcModelPrice(row)" />
                                    </td>
                                    <template v-if="showExtraFields">
                                        <td class="sticky-header">
                                            <el-checkbox
                                                v-model="row.by_policy"
                                                @change="setAnalysisPrice(row)" />
                                        </td>
                                        <td class="sticky-header">
                                            <el-input-number
                                                v-model="row.franchise"
                                                controls-position="right"
                                                :max="100"
                                                :min="0"
                                                :step="0.01"
                                                class="text-right input-tiny" />
                                        </td>
                                        <td class="sticky-header">
                                            <el-input
                                                v-model="row.warranter"
                                                property="warranter"
                                                css-class="table-row" />
                                        </td>
                                    </template>
                                    <td
                                    class="no-ellipsis text-center"
                                    width="3%"
                                >
                                    <svg-icon
                                        :disabled="!canDelete(row)"
                                        name="delete"
                                        class="icon-small icon-blue"
                                        @click="removeAnalysis(row)"
                                    ></svg-icon>
                                </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </table>
                <div class="text-right analysis-total">
                    <span class="input-label">
                        {{ __('Итого кол-во:') }} {{ containerSize }}&nbsp;/&nbsp;
                    </span>
                    <span class="input-label">
                        {{ __('Оплачено:') }} {{ analysisPayed }}&nbsp;/&nbsp;
                    </span>
                    <span class="input-label">
                        {{ __('Итоговая сумма по всем анализам:') }} {{ totalCost }}
                    </span>
                </div>
            </template>
            <empty-section v-else>
                <a
                    href="#"
                    @click.prevent="searchAnalysis">
                    {{ __('Добавить анализы к записи пациента') }}
                </a>
            </empty-section>
        </el-row>
    </div>
</template>

<script>
import SearchAnalyses from './Search.vue';
import AssignedList from './Assigned.vue';
import EmptySection from './Empty.vue';
import PassDateForm from '@/components/doctor/appointment/doctor-service/analysis/SaveForm.vue';
import PriceCalculationMixin from '@/mixins/appointment/analysis/price-calculation';
import AnalysisResult from '@/models/analysis/result';
import CreateResultMixin from '@/mixins/appointment/analysis/create-result';
import transferAnalysis from '../transfer-service/transferServices.vue';
import CONSTANTS from '@/constants';
import {getServicePrice, getInsurancePrice} from '@/services/appointment/service-price';
import AnalysisRepository from '@/repositories/analysis';

export default {
    components: {
        EmptySection,
    },
    mixins: [
        PriceCalculationMixin,
        CreateResultMixin,
    ],
    props:{
        specialization: {
            type: Object,
            default: () => ({})
        },
        appointmentData: {
            type: Object,
            default: () => ({})
        },
        model: Object,
        enquiry: Object,
        insurancePolicy: Object,
        showExtraFields: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            assigned_analyses: [],
            enquiry_analyses: [],
            loading: false,
            analysisSpecialization: null,
        }
    },
    computed: {
        discountCard() {
            return this.appointmentData.discountCard;
        },
        analysisPayed() {
            return _.sumOf(this.model.analyses_results, 'payed').toFixed(2);
        },
        containerSize() {
            return _.sumOf(this.model.analyses_results, 'quantity');
        },
        hasContent() {
            return this.model.analyses_results.length !== 0;
        },
        analysisAddDisabled() {
            if(this.$can('appointments.add-analysis-when-paid')){
                return false;
            }

            let payed = this.analysisPayed;
            let cost = this.totalCost;

            return cost > 0 && payed >= cost;
        },
        totalCost() {
            return _.sumOf(this.model.analyses_results, 'cost').toFixed(2);
        },
        availableAssignments() {
            return this.assigned_analyses.filter(item => {
                return item.quantity > 0
                    && item.card_specialization_id == this.model.card_specialization_id;
            });
        },
        patientHasAssignedAnalyses() {
            return this.model.patient && this.availableAssignments.length !== 0;
        },
        clinicNotRoundCost() {
            return this.appointmentData.clinic.not_round_cost;
        },
    },
    watch: {
        ['model.patient.id']() {
            this.getAssignedAnalyses();
        },
        totalCost() {
            this.$eventHub.$emit('total-changed');
        },
        ['model.specialization_id']() {
            if (this.enquiryHasAnalyses()) {
                if (this.model.specialization_id === this.enquiry.specialization_id) {
                    return this.castEnquiryAnalyses();
                } else {
                    this.enquiry.analysis_list.forEach((analysis) => {
                        this.model.unsetAnalysis(analysis);
                    });
                }
            }
        },
        insurancePolicy(val) {
            if (this.model.analysis_containers.length !== 0 || this.assigned_analyses.length !== 0) {
                this.$nextTick(() => {
                    this.updateInsurancePrices().then(() => {
                        this.model.analyses_results.forEach((item) => {
                            if (!val) {
                                item.by_policy = false;
                            }
                            this.setAnalysisPrice(item);
                        });
                    });
                });
            }
        },
    },
    mounted() {
        this.$eventHub.$on('discountSelected', this.recalcAnalysisDiscount);
         this.$eventHub.$on('removeAnalysis', (row) => {
            this.removeAnalysis(row);
            this.model.save();
        });
    
        if (this.enquiryHasAnalyses()) {
            this.castEnquiryAnalyses();
        }

        this.getAssignedAnalyses();
        this.castAnalysisModels();

        if (this.insurancePolicy && this.assigned_analyses.length !== 0) {
            this.updateAssignedAnalysesPrice()
        }
    },
    beforeDestroy() {
        this.$eventHub.$off('discountSelected', this.recalcAnalysisDiscount);
        this.$eventHub.$off('removeAnalysis', this.searchAnalysis)
    },
    methods: {
        updateAssignedAnalysesPrice() {
            this.updateInsurancePrices(true);
        },
        maxDiscount(row) {
            return row.payed > 0 ? parseFloat((100 - (100 * row.payed / (row.analysis.price * row.quantity))).toFixed(2)) : 100
        },
        recalcAnalysisDiscount() {
            this.model.analyses_results.forEach((analysis) => {
                if (!analysis.by_policy) {
                    this.setAnalysisDiscount(analysis);
                    this.calcModelPrice(analysis)
                }
            });

            this.$discountData.reload.analysis = false;
            this.$discountData.firstCalcDiscountCard.analysis = false;
        },
        enquiryHasAnalyses() {
            return this.enquiry
                && this.enquiry.analysis_list
                && this.enquiry.analysis_list.length != 0;
        },
        castEnquiryAnalyses() {
            if (this.specialization && this.specialization.service_group === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.ANALYSIS) {
                this.enquiry.analysis_list.forEach((item) => {
                    let result = new AnalysisResult({
                        analysis_id: item.service_id,
                        cost: item.payed_amount,
                        quantity: 1,
                        discount: item.discount,
                        analysis: {...item.service},
                        payed: item.payed_amount,
                    });
                    let priceData = result.getAnalysesCost(item.service, this.appointmentData);
                    if (priceData && priceData.id) {
                        result.price_id = priceData.id;
                        result.analysis.price = priceData.price;
                    }
                    this.model.setAnalysis(result);
                });
            }
        },
        castAnalysisModels() {
            this.model.analyses_results.forEach((analysis) => {
                analysis.price = analysis.analysis.price;
                this.calcModelPrice(analysis);
            });
        },
        getAssignedAnalyses() {
            if (_.isEmpty(this.model.patient) || _.isEmpty(this.model.patient.assigned_analyses)) {
                this.assigned_analyses = [];
                return;
            }

            let edgeDate = this.$moment(this.model.date + ' 23:59:59');
            let results = this.model.patient.assigned_analyses.filter((item) => {
                // Check if assignment was made not later than appointment date
                if (edgeDate.isBefore(item.assignment_date)) {
                    return false;
                }
                // Is analysis was assigned in the same clinic it is available regardless
                if (item.clinic_id == this.model.clinic_id) {
                    return true;
                }
                // Is analysis was assigned in another clinic it is available only if has price
                if (item.clinic_group.indexOf(this.model.clinic_id) !== -1) {
                    return item.active_prices.some((price) => price.clinics.indexOf(this.model.clinic_id) !== -1);
                }
                return false;
            });

            this.assigned_analyses = results.map((row) => {
                row.available_quantity = row.quantity;
                return row;
            });

            this.setActiveCostsForAnalyses();
        },
        verifyByPolicy() {
            return this.availableAssignments.some((s) => s.by_policy);
        },
        beforeAddAssignedAnalysis() {
            this.$discountData.reload.analysis = false;
            this.$discountData.firstCalcDiscountCard.analysis = false;
            this.$discountData.refreshDiscountType = 0;
        },
        afterAddAssignedAnalysis(refreshDiscountType) {
            this.$discountData.refreshDiscountType = refreshDiscountType;
        },
        checkExistsAnalysis() {
            if (this.model.analyses_results.length > 0) {
                this.$confirm(__('В записи есть добавленные анализы. Вы действительно хотите добавить назначенные анализы в запись?'), () => {this.showAssignments()});
            } else {
                this.showAssignments()
            }
        },
        showAssignments() {
            this.$modalComponent(AssignedList, {
                analyses: this.availableAssignments,
                appointment: this.model,
                withPolicy: this.verifyByPolicy(),
                insurancePolicy: this.insurancePolicy,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    let refreshDiscountType = this.$discountData.refreshDiscountType;
                    this.beforeAddAssignedAnalysis();
                    this.addToSelected(list, refreshDiscountType)
                    this.$emit('assigned-add', list);
                },
            }, {
                header: __('Выбрать назначенные анализы'),
                width: '1300px',
                customClass: 'padding-0',
            });
        },
        canSearchNewAnalysis() {
            if (this.analysisAddDisabled) {
                this.$error(__('Вы не можете добавлять анализы к уже полностью оплаченным'));
                return false;
            }
            if (this.model.service_containers) {
                let existAssignedAnalysis = this.model.service_containers.filter((analysis) => {
                    return _.isFilled(analysis.card_assignment_id);
                });
                if (existAssignedAnalysis.length > 0) {
                    this.$confirm(__('В записи есть назначенные анализы. Вы действительно хотите добавить анализы в запись?'), () => {this.searchAnalysis()});
                } else {
                    this.searchAnalysis()
                }
            } else {
                this.searchAnalysis()
            }
        },
        searchAnalysis() {
            this.$modalComponent(SearchAnalyses, {
                appointmentData: this.appointmentData,
                analyses: this.model.analyses_results,
                model: this.model,
                insurancePolicy: this.insurancePolicy,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.addToSelected(list);
                },
            }, {
                header: __('Выбрать анализы'),
                width: '1100px',
                customClass: 'padding-0',
            });
        },
        addToSelected(list, refreshDiscountType = false) {
            list.forEach((item) => {
                this.model.setAnalysis(item);
                this.setAnalysisDiscount(item);
                this.calcModelPrice(item);
                if(item.by_policy) {
                    this.setAnalysisPrice(item)
                }
                if (refreshDiscountType) {
                    this.afterAddAssignedAnalysis(refreshDiscountType);
                }
            });
        },
        getCardAssignmentId(analyses) {
            let assignment = analyses.find(analysis => {
                return _.isFilled(analysis.card_assignment_id);
            });
            return assignment ? assignment.card_assignment_id : null;
        },
        setAssignmentQuantity(item) {
            let index = this.getAssignedItemIndex(item);
            if (index !== -1) {
                let available_quantity = this.assigned_analyses[index].available_quantity;
                if (item.quantity <= available_quantity) {
                    let quantity = available_quantity - item.quantity;
                    if (quantity > 0) {
                        this.assigned_analyses[index].quantity = quantity;
                    } else {
                        this.assigned_analyses.splice(index, 1);
                    }
                } else {
                    this.assigned_analyses[index].quantity = 0;
                }
            }
        },
        getAssignedItemIndex(item, field = 'id') {
            return this.assigned_analyses.findIndex((row) => row[field] == item[field]);
        },
        removeAnalysis(row) {
            this.restoreAssignment(row);
            this.model.unsetAnalysis(row);
        },
        restoreAssignment(row) {
            let index = this.getAssignedItemIndex(row);
            if (index !== -1) {
                this.assigned_analyses[index].quantity = this.assigned_analyses[index].available_quantity;
            } else {
                index = this.model.analyses_results.findIndex((item) => item['id'] == row['id']);
                this.assigned_analyses.push(this.model.analyses_results[index]);
            }
        },
        setPassDate(datePass) {
            this.model.analyses_results.forEach((item) => {
                item.date_pass = datePass;
            });
        },
        getPassDateModal() {
            this.$modalComponent(PassDateForm, {
                maxDate: this.model.date,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                save: (dialog, datePass) => {
                    dialog.close();
                    if (_.isFilled(datePass)) {
                        this.setPassDate(datePass);
                    }
                },
            }, {
                header: __('Поставить дату сдачи всем анализам'),
                width: '450px',
            });
        },
        canDelete(row) {
            if (row.payed > 0 || _.isFilled(row.date_ready) || _.isFilled(row.date_pass) || row.has_payments) {
                return false;
            }
            return true;
        },
        canDeleteDatePass(row) {
            if (row.payed > 0 || _.isFilled(row.date_ready) || row.has_payments) {
                return false;
            }
            return true;
        },
        setAnalysisPrice(row) {
            this.$nextTick(() => {
                let priceData;
                if (row.by_policy) {
                    priceData = getInsurancePrice(row, this.appointmentData, CONSTANTS.PRICE.SET_TYPE.INSURANCE, this.insurancePolicy.insurance_company_id);
                    row.discount = 0;
                } else {
                    priceData = getServicePrice(row, this.appointmentData, CONSTANTS.PRICE.SET_TYPE.BASE);
                    let discount = this.calcModelDiscount(row, 'analysis');
                    if (discount > row.discount) {
                        row.discount = discount;
                    }
                }
                if (priceData && priceData.id) {
                    row.price_id = priceData.id;
                    row.analysis.price = priceData.price;
                    row.selfCost = priceData.selfCost;
                }
                this.setContainerByPolicy(row)
                this.calcModelPrice(row);
            });
        },
        setContainerByPolicy(row) {
            let container = this.model.analysis_containers.find((container) => {
                return container.items.findIndex((item) => {
                    return item.analysis_id === row.analysis_id;
                }) !== -1;
            });
            container.by_policy = row.by_policy
        },
        updateInsurancePrices(onlyAssigned = false) {
            if (!this.insurancePolicy) {
                return Promise.resolve();
            }
            let insurerId = this.insurancePolicy.insurance_company_id;
            let repo = new AnalysisRepository();
            let params = {
                withInsurer: insurerId,
            };
            let filters = {
                hasPrice: this.appointmentData.hasPrice,
                disabled: false,
                id: [...!onlyAssigned ? this.model.analyses_results.map(item => item.analysis_id) : [], ...this.assigned_analyses.map(item => item.analysis_id)]
            };
            this.loading = true;
            return repo.fetchListForAppointment(filters, params).then((response) => {
                let items = this.model.analyses_results;
                let assignedItems = this.assigned_analyses
                response.forEach((analysis) => {
                    let item = _.find(items, (i) => i.analysis_id === analysis.analysis_id);
                    if (item && !onlyAssigned) {
                        item.prices = analysis.prices;
                    } else {
                        item = _.find(assignedItems, (i) => i.analysis_id === analysis.analysis_id)

                        if (item) {
                            item.prices = analysis.prices;
                        }
                    }
                });
                this.loading = false;
            });
        },
        setActiveCostsForAnalyses() {
            this.assigned_analyses.forEach((analysis, key) => {
                analysis.active_prices.forEach((activePrice) => {
                    if (_.includes(activePrice.clinics, this.model.clinic_id)) {
                        this.assigned_analyses[key].cost = activePrice.cost;
                        this.assigned_analyses[key].analysis.price = activePrice.cost;
                        this.assigned_analyses[key].price.cost = activePrice.cost;
                        this.assigned_analyses[key].price_id = activePrice.price_id;
                    }
                })
            });
        },
        transferToOtherAppointment() {
            this.$modalComponent(transferAnalysis, {
                serviceType: CONSTANTS.APPOINTMENT_SERVICE.SERVICE_TYPE.ANALYSIS,
                showExtraFields: this.showExtraFields,
                appointment: this.model,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                done: (dialog, datePass) => {
                    dialog.close();
                    this.$eventHub.$emit('updateAppointment');
                },
            }, {
                header: __('Выбирите запись'),
                width: '1200px',
            });
        }
    },
}
</script>
