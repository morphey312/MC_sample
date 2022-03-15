<template>
    <model-form :model="model">
        <table class="vuetable ui blue unstackable price-grid celled table fixed text-left analysis-table-wrapper">
            <div style="height:150px; overflow:auto;">
                <table class="vuetable table bb-0">
                    <thead>
                    <template v-if="isAnalysis">
                        <th class="sticky-header">
                            {{ __('Название анализа') }}
                        </th>
                        <th class="sticky-header">
                            {{ __('Лаборатория') }}
                        </th>
                        <th class="sticky-header">
                            {{ __('Код лаб.') }}
                        </th>
                        <th class="sticky-header">
                            {{ __('Код клиники') }}
                        </th>
                    </template>
                    <template v-else>
                        <th class="sticky-header">
                            {{ __('Название услуги') }}
                        </th>
                        <th class="sticky-header">
                            {{ __('Специализация') }}
                        </th>
                    </template>
                    <th class="sticky-header">
                        {{ __('Клиники') }}
                    </th>
                    <th class="sticky-header">
                        {{ __('Стоимость') }}
                    </th>
                    <th class="sticky-header">
                        {{ __('Стоимость рекомендованная') }}
                    </th>
                    <th class="sticky-header">
                        {{ __('Дата начала тарифа') }}
                    </th>
                    <th class="sticky-header">
                        {{ __('Дата закрытия тарифа') }}
                    </th>
                    <th class="sticky-header"></th>
                    </thead>
                    <tbody>
                        <tr
                            :class="rowClass(price)"
                            v-for="(price, index) in model.act_prices"
                            :key="index">
                            <template v-if="isAnalysis">
                                <td class="no-ellipsis"
                                    width="30%">
                                    {{ price.service_name }}
                                </td>
                                <td class="no-ellipsis"
                                    width="5%">
                                    {{ price.laboratory_name }}
                                </td>
                                <td class="no-ellipsis"
                                    width="5%">
                                    {{ price.laboratory_code }}
                                </td>
                                <td class="no-ellipsis"
                                    width="10%">
                                    {{ $formatter.listFormat(price.clinics_code, 'code') }}
                                </td>
                            </template>
                            <template v-else>
                                <td class="no-ellipsis"
                                    width="30%">
                                    {{ price.name }}
                                </td>
                                <td class="no-ellipsis"
                                    width="10%">
                                    {{ price.specialization_name }}
                                </td>
                            </template>
                            <td width="12%"
                                class="no-ellipsis">
                                <form-select
                                    :options="clinics"
                                    :entity="price"
                                    property="clinics_for_show"
                                    control-size="mini"
                                    :disabled="true"
                                    :multiple="true"/>
                            </td>
                            <td class="no-ellipsis text-right"
                                width="5%">
                                <span class="inline-input disabled">
                                    {{ $formatter.numberFormat(price.cost) }}
                                </span>
                            </td>
                            <td class="no-ellipsis text-right"
                                width="5%">
                                <inline-input
                                    v-model="price.recommended_cost"
                                    :formatter="$formatter.numberFormat"
                                    :disabled="isClosed(price)"
                                    :required="true" />
                            </td>
                            <td class="no-ellipsis"
                                width="10%">
                                {{ $formatter.dateFormat(price.price_date_from) }}
                            </td>
                            <td class="no-ellipsis"
                                width="10%">
                                {{ $formatter.dateFormat(price.price_date_to) }}
                            </td>
                            <td class="no-ellipsis"
                                width="3%">
                                <svg-icon
                                    :disabled="!canDelete(price)"
                                    name="delete"
                                    class="icon-small icon-blue"
                                    @click="remove(index)" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </table>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import CONSTANTS from '@/constants';
import PriceAgreementAct from '@/models/price-agreement-act';
import Price from '@/models/price-agreement-act/price';
import CONSTANT from "@/constants";
import ClinicRepository from "@/repositories/clinic";

export default {
    props: {
        model: {
            type: Object,
            required: true
        },
        isAnalysis: {
            type: Boolean,
            required: true
        }
    },
    data() {
        return {
            clinics: [],
        }
    },
    mounted() {
        this.getClinics();
    },
    methods: {
        getClinics() {
            let clinicsRepository = new ClinicRepository();
            clinicsRepository.fetchList().then((response) => {
                this.clinics = response;
            });
        },
        canDelete(price) {
            if (this.model.isNew()) {
                return true
            } else {
                if (this.$can('price-agreement-acts.update')) {
                    return true
                } else if (this.$can('price-agreement-acts.update-clinic')) {
                    return _.intersection(price.clinics, this.$store.state.user.clinics).length > 0
                } else {
                    return false
                }
            }
        },
        remove(index) {
            this.model.act_prices.splice(index, 1)
        },
        rowClass(price) {
            if (this.isClinicAdd(price)) {
                return ['new-clinic', 'success'];
            }
            if (this.isNewPrice(price)) {
                return ['new-price', 'success'];
            }
            if (this.isClosed(price)) {
                return ['changed', 'closed', 'success'];
            }
            if (this.recommendedCostChanged(price)) {
                return ['changed', 'warning'];
            }
            return  '';
        },
        recommendedCostChanged(price) {
            return price.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CHANGE_COST
        },
        isClinicAdd(price) {
            return price.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.ADD_CLINIC;
        },
        isNewPrice(price) {
            return price.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.NEW_PRICE;
        },
        isClosed(price) {
            return price.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CLOSE_PRICE;
        },
    }
}
</script>

