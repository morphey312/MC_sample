<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto" >
        <template
            slot="medication_duration"
            slot-scope="props">
            <el-input-number
                v-model="props.rowData.medication_duration"
                controls-position="right"
                :step="1"
                :min="1"
                class="text-right input-tiny" />
        </template>
        <template
            slot="base_cost"
            slot-scope="props">
            <div
                v-if="props.rowData.is_free && showPrices === false"
                @click="showPrices = true"
                class="text-center"
                v-text="__('Бесплатно')">
            </div>
            <div v-show="!props.rowData.is_free || showPrices === true" >
                <el-input-number
                    v-if="props.rowData.medicine_type !== 'outclinic_medicine'"
                    v-model="props.rowData.base_cost"
                    controls-position="right"
                    :min="0"
                    :disabled="props.rowData.is_free || props.rowData.prev_assigned"
                    :step="1"
                    control-size="mini"
                    css-class="input-tiny"
                    @change="costChanged(props.rowData)" />
                <div v-else class="text-center">{{ __('Из аптеки') }}</div>
            </div>
        </template>
        <template
            slot="quantity"
            slot-scope="props">
            <el-input-number
                v-model="props.rowData.quantity"
                controls-position="right"
                :step="0.001"
                :precision="3"
                :min="getMinQuantity(props.rowData)"
                class="text-right input-tiny"
                @change="costChanged(props.rowData)" />
        </template>
        <template slot="by_policy" slot-scope="props">
            <el-checkbox
                v-model="props.rowData.by_policy"
                :disabled="props.rowData.is_free" />
        </template>
        <template slot="franchise" slot-scope="props">
            <el-input-number
                v-model="props.rowData.franchise"
                :disabled="props.rowData.is_free"
                controls-position="right"
                :max="100"
                :min="0"
                :step="0.01"
                class="text-right input-tiny" />
        </template>
        <template slot="warranter" slot-scope="props">
            <el-input
                v-model="props.rowData.warranter"
                :disabled="props.rowData.is_free" />
        </template>
        <template slot="is_apteka24" slot-scope="props">
            <svg-icon
                v-show="props.rowData.is_apteka24"
                name="apteka24"
                class="icon-small icon-blue"
            ></svg-icon>
        </template>
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
            slot="remove"
            slot-scope="props">
            <span
                v-if="props.rowData.prev_assigned && props.rowData.issued == 0"
                @click="deletePrevAssigned(props.rowData)">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
            <span
                v-else-if="(props.rowData.prev_assigned === undefined) && props.rowData.issued_quantity == 0"
                @click="toggleSelection(props.rowData, props.rowIndex)">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import TableFilter from '@/mixins/appointment/analysis/static-table-filter';

export default {
    mixins: [
        TableFilter
    ],
    props: {
        readonly: Boolean,
        paidMedicine: Boolean,
        rows: {
            type: Array,
            default: () => []
        },
        prevAssignedList: {
            type: Array,
            default: () => []
        },
        isDoctor: {
            type: Boolean,
            default: false,
        },
        insurancePolicy: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.getRows()
                })
            }),
            fields: [
                {
                    name: 'is_apteka24',
                    title: '',
                    width: '30px',
                    dataClass: 'text-center',
                },
                {
                    name: 'last_issue',
                    title: __('Дата выдачи'),
                    width: "90px",
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        if (val && val.length != 0) {
                            let lastIssued = _.sortBy(val, 'date', 'desc')[0];
                            return this.$formatter.dateFormat(lastIssued.date);
                        }
                        return '';
                    },
                },
                {
                    name: 'name',
                    width: "150px",
                    title: __('Название медикамента'),
                },
                {
                    name: 'medication_duration',
                    title: __('Длительность приема, дней'),
                    width: "100px",
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'base_cost',
                    title: __('Стоимость за ед., грн.'),
                    width: "110px",
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis text-right',
                },
                {
                    name: 'quantity',
                    title: __('Количество, шт'),
                    width: "90px",
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'cost',
                    title: __('Итого стоимость, грн.'),
                    width: "110px",
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis text-right',
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
                    width: '60px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'warranter',
                    title: __('Гарант'),
                    width: '70px',
                },
                ] : []),
                {
                    name: 'comment',
                    title: __('Комментарий'),
                    width: "262px",
                },
                ...(this.readonly || !this.isDoctor ? [] : [{
                    name: 'remove',
                    title: '',
                    width: "30px",
                    dataClass: 'no-ellipsis no-dash',
                }]),
            ],
            showPrices: false,
        }
    },
    watch: {
        rows() {
            this.refresh();
        },
        prevAssignedList() {
            this.refresh();
        },
    },
    methods: {
        getRows() {
            return [
                ...this.rows,
                ...this.prevAssignedList
            ];
        },
        costChanged(row) {
            this.$emit('cost-changed', row);
        },
        toggleSelection(row, index) {
            this.$emit('selection-changed', {row, index});
        },
        getMinQuantity(row) {
            let inUse = 0;
            if (!row.prev_assigned) {
                inUse = Number(row.issued_quantity) + Number(row.on_payment_quantity);
            } else {
                inUse = Number(row.issued);
            }
            return (inUse == 0) ? 0.001 : inUse;
        },
        deletePrevAssigned(row) {
            this.$confirm(__('Вы уверены, что хотите удалить медикамент из предыдущего назначения?'), () => {
                this.$emit('delete-prev', row);
            });
        },
    }
}
</script>
