<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto"
        @header-filter-updated="updateList" >
        <template
            slot="comment"
            slot-scope="props">
            <el-input
                type="textarea"
                autosize
                :rows="1"
                class="table-textarea"
                :placeholder="__('Добавить комментарий')"
                v-model="props.rowData.comment"/>
        </template>
        <template
            slot="discount"
            slot-scope="props">
            <form-input-number
                :entity="props.rowData"
                property="discount"
                control-size="mini"
                css-class="table-row"
                :max="100"
                :min="0"
                :step="0.01"
                :disabled="props.rowData.by_policy"
                @changed="setServicePrice(props.rowData)"
            />
        </template>
        <template slot="by_policy" slot-scope="props">
            <el-checkbox
                v-model="props.rowData.by_policy"
                @change="setServicePrice(props.rowData)" />
        </template>
        <template slot="franchise" slot-scope="props">
            <el-input-number
                v-model="props.rowData.franchise"
                controls-position="right"
                :max="100"
                :min="0"
                :step="0.01"
                class="text-right input-tiny" />
        </template>
        <template slot="warranter" slot-scope="props">
            <el-input v-model="props.rowData.warranter" />
        </template>
        <template
            slot="remove"
            slot-scope="props" >
            <span
                v-if="props.rowData.appointment_service_count == 0"
                @click="toggleSelection(props.rowData, props.rowIndex)">
                    <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import TableFilter from '@/mixins/appointment/analysis/static-table-filter';
import AppointmentServiceMixin from '@/components/doctor/appointment/mixins/appointment-service-table';
import {getInsurancePrice, getServicePrice} from "@/services/appointment/service-price";
import CONSTANTS from "@/constants";

export default {
    mixins: [
        TableFilter,
        AppointmentServiceMixin,
    ],
    props: {
        rows: {
            type: Array,
            default: () => []
        },
        readonly: Boolean,
        model: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.filteredResults
                })
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Выбранные услуги'),
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн'),
                    width: "80px",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'discount',
                    title: __('Скидка'),
                    width: "60px",
                },
                {
                    name: 'by_policy',
                    title: __('Полис'),
                    width: '45px',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'comment',
                    title: __('Дополнительные примечания'),
                    width: "360px",
                },
                ...(this.insurancePolicy ? [
                    {
                        name: 'franchise',
                        title: __('Фр-за, %'),
                        width: '48px',
                        titleClass: 'text-right',
                        dataClass: 'text-right',
                    },
                    {
                        name: 'warranter',
                        title: __('Гарант'),
                        width: '70px',
                    },
                ] : []),
                ...(this.readonly ? [] : [{
                    name: 'remove',
                    title: '',
                    width: "30px",
                    dataClass: 'no-ellipsis no-dash',
                }]),
            ],
            filteredResults: this.rows,
        }
    },
    methods:{
        setServicePrice(service) {
            this.$nextTick(() => {
                let priceData;
                if (service.by_policy) {
                    priceData = getInsurancePrice(service, this.filters, CONSTANTS.PRICE.SET_TYPE.INSURANCE, this.insurancePolicy.insurance_company_id);
                    service.discount = 0;
                } else {
                    priceData = getServicePrice(service, this.filters, CONSTANTS.PRICE.SET_TYPE.BASE);
                    let discount = this.calcModelDiscount(service, 'service');

                }
                if (priceData && priceData.id) {
                    service.price_id = priceData.id;
                    service.price = priceData.price;
                    service.selfCost = priceData.selfCost;
                }
                this.costChanged(service);
            });
        }
    }
}
</script>
