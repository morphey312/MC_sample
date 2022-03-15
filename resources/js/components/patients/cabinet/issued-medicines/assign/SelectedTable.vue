<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto" >
        <template
            slot="card_specilization"
            slot-scope="props">
            <form-select
                :entity="props.rowData" 
                :options="patientCards"
                property="card_specialization_id"
                label=""
                cssClass="m-0" />
        </template>
        <template
            slot="assigner"
            slot-scope="props">
            <form-select
                :entity="props.rowData" 
                :options="doctors"
                property="assigner_id"
                label=""
                cssClass="m-0" />
        </template>
        <template
            slot="quantity"
            slot-scope="props">
            <el-input-number
                v-model="props.rowData.quantity"
                controls-position="right"
                :step="0.001"
                :min="0"
                :precision="3"
                class="text-right input-tiny"
                @change="costChanged(props.rowData)" />
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
            <span class="" @click="toggleSelection(props.rowIndex)">
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
        rows: {
            type: Array,
            default: () => []
        },
        patientCards: {
            type: Array,
            default: () => []
        },
        doctors: {
            type: Array,
            default: () => []
        },
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
                    title: __('Название медикамента'),
                },
                {
                    name: 'card_specilization',
                    title: __('Карта'),
                    width: "110px",
                },
                {
                    name: 'assigner',
                    title: __('Назначил врач'),
                    width: "150px",
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн.'),
                    width: "110px",
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis text-right',
                },
                {
                    name: 'quantity',
                    title: __('Кол-во, шт'),
                    width: "80px",
                    titleClass: 'text-right',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                    width: "200px",
                },
                {
                    name: 'remove',
                    title: '',
                    width: "30px",
                    dataClass: 'no-ellipsis no-dash',
                },
            ],
            filteredResults: this.rows,
        }
    },
    methods: {
        costChanged(row) {
            this.$emit('cost-changed', row);
        },
        toggleSelection(index) {
            this.$emit('selection-changed', index);
        },
    }
}   
</script>