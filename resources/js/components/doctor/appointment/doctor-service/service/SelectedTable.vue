<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto"
        @header-filter-updated="updateList" >
        <template
            slot="discount"
            slot-scope="props" >
            <el-input-number
                v-model="props.rowData.discount"
                controls-position="right"
                :step="0.01"
                :max="100"
                :min="0"
                :disabled="readonly || props.rowData.by_policy == true || props.rowData.payed >= props.rowData.cost"
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
            <template v-if="!props.rowData.payed || props.rowData.payed == 0">
                <span class="" @click="toggleSelection(props.rowData, props.rowIndex)">
                    <svg-icon name="delete" class="icon-small icon-blue" />
                </span>
            </template>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import TableFilterMixin from '@/mixins/appointment/analysis/static-table-filter';
import AppointmentServiceMixin from '@/components/doctor/appointment/mixins/appointment-service-table';

export default {
    mixins: [
        TableFilterMixin,
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
                    name: 'specialization.name',
                    title: __('Специализация'),
                    width: "130px",
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн'),
                    width: "100px",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'discount',
                    title: __('Скидка'),
                    width: "100px",
                    dataClass: 'no-ellipsis',
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
