<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto"
        @header-filter-updated="updateList" >
        <template
            slot="quantity"
            slot-scope="props" >
            <el-input-number
                v-model="props.rowData.quantity"
                controls-position="right"
                :step="1"
                :min="props.rowData.appointment_service_count"
                :disabled="readonly"
                class="text-right input-tiny"
                @change="costChanged(props.rowData)" />
        </template>
        <template
            slot="discount"
            slot-scope="props" >
            <el-input-number
                v-model="props.rowData.discount"
                controls-position="right"
                :step="0.01"
                :max="100"
                :min="0"
                :disabled="readonly || props.rowData.by_policy == true"
                class="text-right input-tiny"
                @change="costChanged(props.rowData)" />
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
        model: Object,
        readonly: Boolean,
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
                    title: __('Название услуги'),
                    filter: true,
                    filterField: 'name',
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн'),
                    width: "80px",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'self_cost',
                    title: __('Себестоимость, грн'),
                    width: "130px",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'quantity',
                    title: __('Кол-во'),
                    width: "100px",
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'discount',
                    title: __('Скидка'),
                    width: "100px",
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'is_free',
                    title: __('Платный'),
                    width: "65px",
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.boolToString(!val, '<span class="check-yes" />');
                    },
                },
                ...(this.insurancePolicy ? [{
                    name: 'by_policy',
                    title: __('Полис'),
                    width: '48px',
                },
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
}
</script>
